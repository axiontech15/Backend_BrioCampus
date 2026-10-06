from sqlmodel import Session

from app.models.log import Log


class ActivityLogger:
    @staticmethod
    def write(
        db: Session,
        *,
        action: str,
        description: str | None = None,
        user_id: int | None = None,
        ip_address: str | None = None,
        user_agent: str | None = None,
        commit: bool = True,
    ) -> Log:
        entry = Log(
            user_id=user_id,
            action=action,
            description=description,
            ip_address=ip_address,
            user_agent=(user_agent or "")[:500] or None,
        )
        db.add(entry)
        if commit:
            db.commit()
            db.refresh(entry)
        return entry
