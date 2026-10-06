from fastapi import APIRouter, Depends, Request
from sqlmodel import Session

from app.controllers.auth import AuthController
from app.controllers.user import UserController
from app.dependencies.auth import get_current_user
from app.models.user import User
from app.validators.auth import (
    LoginRequest,
    OtpVerifyRequest,
    PasswordResetConfirm,
    PasswordResetRequest,
    RefreshRequest,
    TokenResponse,
)
from app.validators.user import AccountUpdate
from config.database import get_db

router = APIRouter(prefix="/auth", tags=["Auth"])


@router.post("/login", response_model=TokenResponse)
def login(payload: LoginRequest, request: Request, db: Session = Depends(get_db)):
    return AuthController.login(db, payload, request)


@router.post("/refresh", response_model=TokenResponse)
def refresh(payload: RefreshRequest, db: Session = Depends(get_db)):
    return AuthController.refresh(db, payload)


@router.post("/password-reset/request")
def password_reset_request(payload: PasswordResetRequest, db: Session = Depends(get_db)):
    return AuthController.request_password_reset(db, payload)


@router.post("/password-reset/confirm")
def password_reset_confirm(payload: PasswordResetConfirm, db: Session = Depends(get_db)):
    return AuthController.confirm_password_reset(db, payload)


@router.post("/otp/send")
def send_otp(payload: PasswordResetRequest, db: Session = Depends(get_db)):
    return AuthController.send_otp(db, str(payload.email), purpose="verify")


@router.post("/otp/verify")
def verify_otp(payload: OtpVerifyRequest):
    return AuthController.verify_otp(str(payload.email), payload.code, purpose="verify")


@router.get("/me")
def me(user: User = Depends(get_current_user)):
    return UserController._serialize(user)


@router.put("/me")
def update_me(
    payload: AccountUpdate,
    user: User = Depends(get_current_user),
    db: Session = Depends(get_db),
):
    return UserController.update_account(db, user, payload)
