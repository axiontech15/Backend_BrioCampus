from collections.abc import Callable

from fastapi import Depends, HTTPException, Request, status
from fastapi.security import HTTPAuthorizationCredentials, HTTPBearer
from sqlmodel import Session, select

from app.models.user import User
from config.database import get_db
from config.security import decode_token

bearer_scheme = HTTPBearer(auto_error=False)


def get_client_meta(request: Request) -> dict[str, str | None]:
    forwarded = request.headers.get("x-forwarded-for")
    ip = forwarded.split(",")[0].strip() if forwarded else (
        request.client.host if request.client else None
    )
    return {
        "ip_address": ip,
        "user_agent": request.headers.get("user-agent"),
    }


def get_current_user(
    credentials: HTTPAuthorizationCredentials | None = Depends(bearer_scheme),
    db: Session = Depends(get_db),
) -> User:
    if credentials is None or credentials.scheme.lower() != "bearer":
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Authentication required",
            headers={"WWW-Authenticate": "Bearer"},
        )
    try:
        payload = decode_token(credentials.credentials)
    except ValueError as exc:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail=str(exc),
            headers={"WWW-Authenticate": "Bearer"},
        ) from exc

    if payload.get("type") != "access":
        raise HTTPException(status_code=401, detail="Invalid token type")

    user_id = payload.get("sub")
    if not user_id:
        raise HTTPException(status_code=401, detail="Invalid token subject")

    user = db.exec(select(User).where(User.id == int(user_id))).first()
    if not user or user.status != "ACTIVE":
        raise HTTPException(status_code=401, detail="User inactive or not found")
    return user


def require_roles(*allowed: str) -> Callable:
    allowed_upper = {name.upper() for name in allowed}

    def dependency(user: User = Depends(get_current_user)) -> User:
        role_name = user.role.name.upper() if user.role else ""
        if role_name not in allowed_upper:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Insufficient permissions",
            )
        return user

    return dependency
