from datetime import UTC, datetime, timedelta

from fastapi import HTTPException
from sqlalchemy import delete, update
from sqlmodel import Session, select

from app.models.log import Log
from app.validators.log import LogOut
from config.settings import get_settings


class LogController:
    @staticmethod
    def _serialize(entry: Log) -> dict:
        return LogOut.model_validate(entry).model_dump(mode="json")

    @classmethod
    def index(
        cls,
        db: Session,
        *,
        archived: bool | None = None,
        limit: int = 100,
        offset: int = 0,
    ) -> list[dict]:
        stmt = select(Log).order_by(Log.created_at.desc())
        if archived is not None:
            stmt = stmt.where(Log.is_archived == archived)
        rows = db.exec(
            stmt.offset(max(offset, 0)).limit(min(max(limit, 1), 500))
        ).all()
        return [cls._serialize(r) for r in rows]

    @classmethod
    def show(cls, db: Session, log_id: int) -> dict:
        entry = db.exec(select(Log).where(Log.id == log_id)).first()
        if not entry:
            raise HTTPException(status_code=404, detail="Log not found")
        return cls._serialize(entry)

    @classmethod
    def archive_old(cls, db: Session) -> dict:
        settings = get_settings()
        threshold = datetime.now(UTC) - timedelta(days=settings.LOG_ARCHIVE_AFTER_DAYS)
        now = datetime.now(UTC)
        result = db.exec(
            update(Log)
            .where(Log.is_archived == False, Log.created_at < threshold)  # noqa: E712
            .values(is_archived=True, archived_at=now)
        )
        db.commit()
        return {
            "archived": result.rowcount or 0,
            "archive_after_days": settings.LOG_ARCHIVE_AFTER_DAYS,
        }

    @classmethod
    def purge_archived(cls, db: Session) -> dict:
        settings = get_settings()
        threshold = datetime.now(UTC) - timedelta(days=settings.LOG_RETENTION_DAYS)
        result = db.exec(
            delete(Log).where(
                Log.is_archived == True,  # noqa: E712
                Log.created_at < threshold,
            )
        )
        db.commit()
        return {
            "deleted": result.rowcount or 0,
            "retention_days": settings.LOG_RETENTION_DAYS,
        }
