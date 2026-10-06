from fastapi import APIRouter, Depends
from sqlmodel import Session

from app.controllers.role import RoleController
from app.dependencies.auth import require_roles
from app.models.user import User
from app.services.logger import ActivityLogger
from app.validators.role import RoleCreate, RoleUpdate
from config.database import get_db

router = APIRouter(prefix="/roles", tags=["Roles"])


@router.get("")
def index(
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN")),
):
    return RoleController.index(db)


@router.get("/{role_id}")
def show(
    role_id: int,
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN")),
):
    return RoleController.show(db, role_id)


@router.post("", status_code=201)
def create(
    payload: RoleCreate,
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("ADMIN")),
):
    role = RoleController.create(db, payload)
    ActivityLogger.write(
        db,
        action="CREATE",
        description=f"Role created: {role['name']}",
        user_id=user.id,
    )
    return role


@router.put("/{role_id}")
def update(
    role_id: int,
    payload: RoleUpdate,
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("ADMIN")),
):
    role = RoleController.update(db, role_id, payload)
    ActivityLogger.write(
        db,
        action="UPDATE",
        description=f"Role updated: {role_id}",
        user_id=user.id,
    )
    return role


@router.delete("/{role_id}")
def delete(
    role_id: int,
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("ADMIN")),
):
    role = RoleController.delete(db, role_id)
    ActivityLogger.write(
        db,
        action="DELETE",
        description=f"Role deleted: {role_id}",
        user_id=user.id,
    )
    return role
