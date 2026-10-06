from fastapi import APIRouter, Depends
from sqlmodel import Session

from app.controllers.notification import NotificationController
from app.dependencies.auth import get_current_user, require_roles
from app.models.user import User
from app.validators.notification import NotificationCreate
from config.database import get_db

router = APIRouter(prefix="/notifications", tags=["Notifications"])


@router.get("")
def index(
    db: Session = Depends(get_db),
    user: User = Depends(get_current_user),
):
    return NotificationController.index(db, user)


@router.post("", status_code=201)
def create(
    payload: NotificationCreate,
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN")),
):
    return NotificationController.create(db, payload)


@router.post("/{notification_id}/read")
def mark_read(
    notification_id: int,
    db: Session = Depends(get_db),
    user: User = Depends(get_current_user),
):
    return NotificationController.mark_read(db, user, notification_id)
