from fastapi import APIRouter, Depends
from sqlmodel import Session

from app.controllers.category import CategoryController
from app.dependencies.auth import get_current_user, require_roles
from app.models.user import User
from app.validators.category import CategoryCreate, CategoryUpdate
from config.database import get_db

router = APIRouter(prefix="/categories", tags=["Categories"])


@router.get("")
def index(
    db: Session = Depends(get_db),
    _: User = Depends(get_current_user),
):
    return CategoryController.index(db)


@router.get("/{category_id}")
def show(
    category_id: int,
    db: Session = Depends(get_db),
    _: User = Depends(get_current_user),
):
    return CategoryController.show(db, category_id)


@router.post("", status_code=201)
def create(
    payload: CategoryCreate,
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN")),
):
    return CategoryController.create(db, payload)


@router.put("/{category_id}")
def update(
    category_id: int,
    payload: CategoryUpdate,
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN")),
):
    return CategoryController.update(db, category_id, payload)


@router.delete("/{category_id}")
def delete(
    category_id: int,
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN")),
):
    return CategoryController.delete(db, category_id)
