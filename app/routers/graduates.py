from fastapi import APIRouter, Depends, File, UploadFile
from sqlmodel import Session

from app.controllers.graduate import GraduateController
from app.dependencies.auth import require_roles
from app.models.user import User
from app.validators.graduate import GraduateSearchRequest
from config.database import get_db

router = APIRouter(tags=["Graduates / Palmares"])


@router.post("/rh/search")
def search_graduate(
    payload: GraduateSearchRequest,
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("RH")),
):
    return GraduateController.search(db, user, payload)


@router.get("/rh/stats")
def rh_stats(
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("RH")),
):
    return GraduateController.rh_stats(db, user)


@router.post("/universities/palmares/import")
async def import_palmares(
    file: UploadFile = File(...),
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("UNIVERSITY")),
):
    return await GraduateController.import_palmares(db, user, file)


@router.get("/universities/stats")
def university_stats(
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("UNIVERSITY")),
):
    return GraduateController.university_stats(db, user)
