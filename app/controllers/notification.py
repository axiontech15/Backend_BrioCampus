from datetime import UTC, datetime

from fastapi import HTTPException
from sqlmodel import Session, select

from app.models.notification import Notification
from app.models.user import User
from app.validators.notification import NotificationCreate, NotificationOut


class NotificationController:
    @staticmethod
    def _serialize(notification: Notification) -> dict:
        return NotificationOut.model_validate(notification).model_dump(mode="json")

    @classmethod
    def index(cls, db: Session, user: User) -> list[dict]:
        rows = db.exec(
            select(Notification)
            .where(Notification.user_id == user.id)
            .order_by(Notification.created_at.desc())
        ).all()
        return [cls._serialize(n) for n in rows]

    @classmethod
    def create(cls, db: Session, payload: NotificationCreate) -> dict:
        notification = Notification(
            user_id=payload.user_id,
            title=payload.title,
            message=payload.message,
            type=payload.type.upper(),
        )
        db.add(notification)
        db.commit()
        db.refresh(notification)
        return cls._serialize(notification)

    @classmethod
    def mark_read(cls, db: Session, user: User, notification_id: int) -> dict:
        notification = db.exec(
            select(Notification).where(
                Notification.id == notification_id,
                Notification.user_id == user.id,
            )
        ).first()
        if not notification:
            raise HTTPException(status_code=404, detail="Notification not found")
        notification.is_read = True
        notification.read_at = datetime.now(UTC)
        db.commit()
        db.refresh(notification)
        return cls._serialize(notification)
