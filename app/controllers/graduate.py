import csv
import io
from datetime import UTC, datetime
from pathlib import Path

from fastapi import HTTPException, UploadFile
from sqlalchemy import func
from sqlmodel import Session, select

from app.controllers.ticket import TicketController
from app.models.graduate import Graduate, ProfileSearch
from app.models.palmares import Palmares
from app.models.user import User
from app.services.logger import ActivityLogger
from app.validators.graduate import (
    GraduateOut,
    GraduateSearchRequest,
    GraduateSearchResult,
    ImportResult,
)
from config.settings import get_settings


class GraduateController:
    @staticmethod
    def _serialize(graduate: Graduate) -> dict:
        return GraduateOut.model_validate(graduate).model_dump(mode="json")

    @classmethod
    def search(
        cls,
        db: Session,
        user: User,
        payload: GraduateSearchRequest,
    ) -> GraduateSearchResult:
        if not any([payload.matricule, payload.full_name, payload.faculty, payload.university_id]):
            raise HTTPException(status_code=400, detail="Provide at least one search criterion")

        stmt = select(Graduate)
        if payload.matricule:
            stmt = stmt.where(Graduate.matricule == payload.matricule.strip())
        if payload.full_name:
            stmt = stmt.where(
                func.lower(Graduate.full_name).like(
                    f"%{payload.full_name.strip().lower()}%"
                )
            )
        if payload.faculty:
            stmt = stmt.where(
                func.lower(Graduate.faculty).like(
                    f"%{payload.faculty.strip().lower()}%"
                )
            )
        if payload.university_id:
            stmt = stmt.where(Graduate.university_id == payload.university_id)

        graduate = db.exec(stmt.order_by(Graduate.id.desc())).first()
        remaining = TicketController.balance(db, user)["remaining_tickets"]

        if not graduate:
            return GraduateSearchResult(
                found=False,
                graduate=None,
                ticket_consumed=False,
                remaining_tickets=remaining,
            )

        ticket = TicketController.consume_one(db, user)
        db.add(
            ProfileSearch(
                rh_user_id=user.id,
                graduate_id=graduate.id,
                university_id=graduate.university_id,
                ticket_id=ticket.id,
            )
        )
        ActivityLogger.write(
            db,
            action="SEARCH",
            description=f"RH {user.id} found graduate {graduate.matricule}",
            user_id=user.id,
            commit=False,
        )
        db.commit()
        remaining = TicketController.balance(db, user)["remaining_tickets"]
        return GraduateSearchResult(
            found=True,
            graduate=GraduateOut.model_validate(graduate),
            ticket_consumed=True,
            remaining_tickets=remaining,
        )

    @classmethod
    async def import_palmares(
        cls,
        db: Session,
        user: User,
        file: UploadFile,
    ) -> ImportResult:
        settings = get_settings()
        if not user.university_id:
            raise HTTPException(status_code=400, detail="University account has no university_id")

        content_type = (file.content_type or "").split(";")[0].strip().lower()
        allowed = {m.lower() for m in settings.allowed_upload_mime_list}
        allowed.update({"application/csv", "text/csv", "application/octet-stream"})
        if content_type and content_type not in allowed:
            raise HTTPException(
                status_code=415,
                detail=f"Unsupported MIME type: {content_type}",
            )

        raw = await file.read()
        max_bytes = settings.MAX_UPLOAD_SIZE_MB * 1024 * 1024
        if len(raw) > max_bytes:
            raise HTTPException(status_code=413, detail="File too large")

        upload_dir = Path(settings.UPLOAD_DIR)
        upload_dir.mkdir(parents=True, exist_ok=True)
        safe_name = Path(file.filename or "palmares.csv").name.replace("..", "")
        stamp = datetime.now(UTC).strftime("%Y%m%d%H%M%S")
        dest = upload_dir / f"{user.university_id}_{stamp}_{safe_name}"
        dest.write_bytes(raw)

        palmares = Palmares(
            university_id=user.university_id,
            file_path=str(dest),
            file_name=safe_name,
            uploaded_by=user.id,
            status="PENDING",
        )
        db.add(palmares)
        db.flush()

        result = ImportResult(palmares_id=palmares.id)
        try:
            decoded = raw.decode("utf-8-sig")
        except UnicodeDecodeError:
            raise HTTPException(status_code=400, detail="File must be UTF-8 CSV") from None

        reader = csv.DictReader(io.StringIO(decoded))
        if not reader.fieldnames:
            raise HTTPException(status_code=400, detail="CSV header missing")

        required = {"matricule", "full_name", "faculty", "diploma_date"}
        normalized_headers = {h.strip().lower(): h for h in reader.fieldnames if h}
        missing = required - set(normalized_headers)
        if missing:
            raise HTTPException(
                status_code=400,
                detail=f"Missing CSV columns: {', '.join(sorted(missing))}",
            )

        for idx, row in enumerate(reader, start=2):
            try:
                matricule = (row[normalized_headers["matricule"]] or "").strip()
                full_name = (row[normalized_headers["full_name"]] or "").strip()
                faculty = (row[normalized_headers["faculty"]] or "").strip()
                diploma_raw = (row[normalized_headers["diploma_date"]] or "").strip()
                if not all([matricule, full_name, faculty, diploma_raw]):
                    result.invalid += 1
                    result.errors.append(f"Line {idx}: missing required field")
                    continue
                diploma_date = datetime.strptime(diploma_raw, "%Y-%m-%d").date()
            except (ValueError, KeyError) as exc:
                result.invalid += 1
                result.errors.append(f"Line {idx}: {exc}")
                continue

            existing = db.exec(
                select(Graduate).where(
                    Graduate.university_id == user.university_id,
                    Graduate.matricule == matricule,
                )
            ).first()
            if existing:
                result.duplicates += 1
                result.errors.append(f"Line {idx}: duplicate matricule {matricule}")
                continue

            db.add(
                Graduate(
                    university_id=user.university_id,
                    palmares_id=palmares.id,
                    matricule=matricule,
                    full_name=full_name,
                    faculty=faculty,
                    diploma_date=diploma_date,
                )
            )
            result.imported += 1

        palmares.status = "VERIFIED" if result.imported else "REJECTED"
        if result.imported:
            palmares.verified_at = datetime.now(UTC)
            palmares.verified_by = user.id

        ActivityLogger.write(
            db,
            action="UPLOAD",
            description=(
                f"Palmares import univ={user.university_id} "
                f"imported={result.imported} duplicates={result.duplicates} invalid={result.invalid}"
            ),
            user_id=user.id,
            commit=False,
        )
        db.commit()
        return result

    @classmethod
    def rh_stats(cls, db: Session, user: User) -> dict:
        remaining = TicketController.balance(db, user)["remaining_tickets"]
        consulted = db.exec(
            select(func.count(ProfileSearch.id)).where(
                ProfileSearch.rh_user_id == user.id
            )
        ).one()
        return {
            "remaining_tickets": remaining,
            "profiles_consulted": int(consulted or 0),
        }

    @classmethod
    def university_stats(cls, db: Session, user: User) -> dict:
        if not user.university_id:
            raise HTTPException(status_code=400, detail="University account has no university_id")
        graduates_count = db.exec(
            select(func.count(Graduate.id)).where(
                Graduate.university_id == user.university_id
            )
        ).one()
        rh_consulted = db.exec(
            select(func.count(func.distinct(ProfileSearch.rh_user_id))).where(
                ProfileSearch.university_id == user.university_id
            )
        ).one()
        return {
            "university_id": user.university_id,
            "graduates_count": int(graduates_count or 0),
            "rh_consulted_count": int(rh_consulted or 0),
        }
