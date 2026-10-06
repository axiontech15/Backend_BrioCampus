from fastapi import APIRouter, Depends, Query
from sqlmodel import Session, select

from app.controllers.log import LogController
from app.dependencies.auth import get_current_user, require_roles
from app.models.log import Log
from app.models.user import User
from app.validators.log import LogOut
from config.database import get_db

router = APIRouter(prefix="/logs", tags=["Logs"])


@router.get("")
def index(
    archived: bool | None = Query(default=None),
    limit: int = Query(default=100, ge=1, le=500),
    offset: int = Query(default=0, ge=0),
    db: Session = Depends(get_db),
    user: User = Depends(get_current_user),
):
    role = user.role.name.upper() if user.role else ""
    if role != "ADMIN":
        rows = db.exec(
            select(Log)
            .where(Log.user_id == user.id)
            .order_by(Log.created_at.desc())
            .offset(offset)
            .limit(limit)
        ).all()
        return [LogOut.model_validate(r).model_dump(mode="json") for r in rows]
    return LogController.index(db, archived=archived, limit=limit, offset=offset)


@router.post("/archive")
def archive(
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN")),
):
    return LogController.archive_old(db)


@router.delete("/purge")
def purge(
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN")),
):
    return LogController.purge_archived(db)


@router.get("/{log_id}")
def show(
    log_id: int,
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN")),
):
    return LogController.show(db, log_id)
