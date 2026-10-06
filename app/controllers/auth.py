from fastapi import HTTPException, Request
from sqlmodel import Session, select

from app.models.user import User
from app.services.logger import ActivityLogger
from app.services.otp import OtpService
from app.validators.auth import (
    LoginRequest,
    PasswordResetConfirm,
    PasswordResetRequest,
    RefreshRequest,
    TokenResponse,
)
from config.security import (
    create_access_token,
    create_refresh_token,
    decode_token,
    hash_password,
    verify_password,
)


class AuthController:
    @staticmethod
    def login(db: Session, payload: LoginRequest, request: Request) -> TokenResponse:
        email = str(payload.email).lower()
        user = db.exec(select(User).where(User.email == email)).first()
        if not user or not verify_password(payload.password, user.password):
            raise HTTPException(status_code=401, detail="Invalid credentials")
        if user.status != "ACTIVE":
            raise HTTPException(status_code=403, detail="Account disabled")

        role_name = user.role.name if user.role else None
        access = create_access_token(
            user.id,
            extra={"role": role_name, "email": user.email},
        )
        refresh = create_refresh_token(user.id)

        ActivityLogger.write(
            db,
            action="LOGIN",
            description=f"User {user.email} logged in",
            user_id=user.id,
            ip_address=request.client.host if request.client else None,
            user_agent=request.headers.get("user-agent"),
        )
        return TokenResponse(access_token=access, refresh_token=refresh)

    @staticmethod
    def refresh(db: Session, payload: RefreshRequest) -> TokenResponse:
        try:
            data = decode_token(payload.refresh_token)
        except ValueError as exc:
            raise HTTPException(status_code=401, detail=str(exc)) from exc
        if data.get("type") != "refresh":
            raise HTTPException(status_code=401, detail="Invalid refresh token")
        user = db.exec(select(User).where(User.id == int(data["sub"]))).first()
        if not user or user.status != "ACTIVE":
            raise HTTPException(status_code=401, detail="User not found")
        role_name = user.role.name if user.role else None
        return TokenResponse(
            access_token=create_access_token(
                user.id, extra={"role": role_name, "email": user.email}
            ),
            refresh_token=create_refresh_token(user.id),
        )

    @staticmethod
    def request_password_reset(db: Session, payload: PasswordResetRequest) -> dict:
        user = db.exec(
            select(User).where(User.email == str(payload.email).lower())
        ).first()
        if user:
            OtpService.issue(user.email, purpose="reset")
        return {"detail": "If the email exists, an OTP has been sent"}

    @staticmethod
    def confirm_password_reset(db: Session, payload: PasswordResetConfirm) -> dict:
        email = str(payload.email).lower()
        if not OtpService.verify(email, payload.code, purpose="reset"):
            raise HTTPException(status_code=400, detail="Invalid or expired OTP")
        user = db.exec(select(User).where(User.email == email)).first()
        if not user:
            raise HTTPException(status_code=404, detail="User not found")
        user.password = hash_password(payload.new_password)
        db.commit()
        ActivityLogger.write(
            db,
            action="UPDATE",
            description=f"Password reset for {email}",
            user_id=user.id,
        )
        return {"detail": "Password updated successfully"}

    @staticmethod
    def send_otp(db: Session, email: str, purpose: str = "verify") -> dict:
        user = db.exec(select(User).where(User.email == email.lower())).first()
        if not user:
            return {"detail": "If the email exists, an OTP has been sent"}
        OtpService.issue(user.email, purpose=purpose)
        return {"detail": "If the email exists, an OTP has been sent"}

    @staticmethod
    def verify_otp(email: str, code: str, purpose: str = "verify") -> dict:
        if not OtpService.verify(email.lower(), code, purpose=purpose, consume=False):
            raise HTTPException(status_code=400, detail="Invalid or expired OTP")
        return {"detail": "OTP valid", "verified": True}
