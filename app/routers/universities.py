from fastapi import APIRouter, Depends, HTTPException
from sqlmodel import Session

from app.controllers.university import UniversityController
from app.dependencies.auth import get_current_user, require_roles
from app.models.user import User
from app.validators.university import UniversityCreate, UniversityUpdate
from config.database import get_db

router = APIRouter(prefix="/universities", tags=["Universities"])


@router.get("")
def index(
    db: Session = Depends(get_db),
    _: User = Depends(get_current_user),
):
    return UniversityController.index(db)


@router.get("/me")
def get_own_profile(
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("UNIVERSITY")),
):
    if not user.university_id:
        raise HTTPException(status_code=400, detail="No university linked to account")
    return UniversityController.show(db, user.university_id)


@router.put("/me")
def update_own_profile(
    payload: UniversityUpdate,
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("UNIVERSITY")),
):
    if not user.university_id:
        raise HTTPException(status_code=400, detail="No university linked to account")
    return UniversityController.update(db, user.university_id, payload)


@router.get("/{university_id}")
def show(
    university_id: int,
    db: Session = Depends(get_db),
    _: User = Depends(get_current_user),
):
    return UniversityController.show(db, university_id)


@router.post("", status_code=201)
def create(
    payload: UniversityCreate,
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN")),
):
    return UniversityController.create(db, payload)


@router.put("/{university_id}")
def update(
    university_id: int,
    payload: UniversityUpdate,
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN")),
):
    return UniversityController.update(db, university_id, payload)


@router.delete("/{university_id}")
def delete(
    university_id: int,
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN")),
):
    return UniversityController.delete(db, university_id)
