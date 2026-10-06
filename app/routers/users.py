from fastapi import APIRouter, Depends
from sqlmodel import Session

from app.controllers.user import UserController
from app.dependencies.auth import require_roles
from app.models.user import User
from app.services.logger import ActivityLogger
from app.validators.user import ProfileCreate, ProfileUpdate, UserCreate, UserUpdate
from config.database import get_db

router = APIRouter(prefix="/users", tags=["Users"])


@router.get("")
def index(
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN")),
):
    return UserController.index(db)


@router.get("/{user_id}")
def show(
    user_id: int,
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN", "RH")),
):
    return UserController.show(db, user_id)


@router.post("", status_code=201)
def create(
    payload: UserCreate,
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("ADMIN")),
):
    created = UserController.create(db, payload)
    ActivityLogger.write(
        db,
        action="CREATE",
        description=f"User created: {created['email']}",
        user_id=user.id,
    )
    return created


@router.put("/{user_id}")
def update(
    user_id: int,
    payload: UserUpdate,
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("ADMIN")),
):
    updated = UserController.update(db, user_id, payload)
    ActivityLogger.write(
        db,
        action="UPDATE",
        description=f"User updated: {user_id}",
        user_id=user.id,
    )
    return updated


@router.delete("/{user_id}")
def delete(
    user_id: int,
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("ADMIN")),
):
    deleted = UserController.delete(db, user_id)
    ActivityLogger.write(
        db,
        action="DELETE",
        description=f"User deleted: {user_id}",
        user_id=user.id,
    )
    return deleted


@router.post("/profiles", status_code=201, tags=["RH Profiles"])
def create_profile(
    payload: ProfileCreate,
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("RH", "ADMIN")),
):
    created = UserController.create_profile(db, payload)
    ActivityLogger.write(
        db,
        action="CREATE",
        description=f"Profile created by RH: {created['email']}",
        user_id=user.id,
    )
    return created


@router.put("/profiles/{user_id}", tags=["RH Profiles"])
def update_profile(
    user_id: int,
    payload: ProfileUpdate,
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("RH", "ADMIN")),
):
    return UserController.update_profile(db, user_id, payload)


@router.delete("/profiles/{user_id}", tags=["RH Profiles"])
def delete_profile(
    user_id: int,
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("RH", "ADMIN")),
):
    return UserController.delete(db, user_id)
