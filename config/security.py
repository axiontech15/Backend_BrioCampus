from datetime import UTC, datetime, timedelta
from typing import Any

import bcrypt
import jwt
from jwt.exceptions import InvalidTokenError

from config.settings import get_settings


def hash_password(password: str) -> str:
    return bcrypt.hashpw(password.encode("utf-8"), bcrypt.gensalt()).decode("utf-8")


def verify_password(plain_password: str, hashed_password: str) -> bool:
    try:
        return bcrypt.checkpw(
            plain_password.encode("utf-8"),
            hashed_password.encode("utf-8"),
        )
    except (ValueError, TypeError):
        return False


def create_access_token(subject: str | int, extra: dict[str, Any] | None = None) -> str:
    settings = get_settings()
    payload: dict[str, Any] = {
        "sub": str(subject),
        "type": "access",
        "exp": datetime.now(UTC) + timedelta(minutes=settings.ACCESS_TOKEN_EXPIRE_MINUTES),
        "iat": datetime.now(UTC),
    }
    if extra:
        payload.update(extra)
    return jwt.encode(payload, settings.JWT_SECRET, algorithm=settings.JWT_ALGORITHM)


def create_refresh_token(subject: str | int) -> str:
    settings = get_settings()
    payload = {
        "sub": str(subject),
        "type": "refresh",
        "exp": datetime.now(UTC) + timedelta(days=settings.REFRESH_TOKEN_EXPIRE_DAYS),
        "iat": datetime.now(UTC),
    }
    return jwt.encode(payload, settings.JWT_SECRET, algorithm=settings.JWT_ALGORITHM)


def decode_token(token: str) -> dict[str, Any]:
    settings = get_settings()
    try:
        return jwt.decode(token, settings.JWT_SECRET, algorithms=[settings.JWT_ALGORITHM])
    except InvalidTokenError as exc:
        raise ValueError("Invalid or expired token") from exc
