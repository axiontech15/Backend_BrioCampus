from fastapi import HTTPException
from sqlmodel import Session, select

from app.models.role import Role
from app.models.university import University
from app.models.user import User
from app.services.cache import CacheService
from app.validators.university import UniversityCreate, UniversityOut, UniversityUpdate
from config.security import hash_password


class UniversityController:
    CACHE_NS = "universities"

    @staticmethod
    def _serialize(university: University, contact_email: str | None = None) -> dict:
        data = UniversityOut.model_validate(university).model_dump(mode="json")
        if contact_email is not None:
            data["contact_email"] = contact_email
        else:
            data.setdefault("contact_email", None)
        return data

    @classmethod
    def _contact_email(cls, db: Session, university_id: int) -> str | None:
        user = db.exec(
            select(User).where(User.university_id == university_id).order_by(User.id)
        ).first()
        return user.email if user else None

    @classmethod
    def index(cls, db: Session) -> list[dict]:
        rows = db.exec(select(University).order_by(University.id)).all()
        return [cls._serialize(u, cls._contact_email(db, u.id)) for u in rows]

    @classmethod
    def show(cls, db: Session, university_id: int) -> dict:
        university = db.exec(
            select(University).where(University.id == university_id)
        ).first()
        if not university:
            raise HTTPException(status_code=404, detail="University not found")
        return cls._serialize(university, cls._contact_email(db, university.id))

    @classmethod
    def create(cls, db: Session, payload: UniversityCreate) -> dict:
        name = payload.name.strip()
        email = str(payload.email).lower()

        if db.exec(select(University).where(University.name == name)).first():
            raise HTTPException(status_code=409, detail="University already exists")
        if db.exec(select(User).where(User.email == email)).first():
            raise HTTPException(status_code=409, detail="Email already registered")

        role = db.exec(
            select(Role).where(Role.name.in_(["UNIVERSITY", "university"]))
        ).first()
        if not role:
            role = Role(name="UNIVERSITY")
            db.add(role)
            db.flush()

        university = University(
            name=name,
            logo=payload.logo,
            address=payload.address,
            status=payload.status.upper(),
        )
        db.add(university)
        db.flush()

        user = User(
            role_id=role.id,
            university_id=university.id,
            first_name=payload.first_name.strip(),
            last_name=payload.last_name.strip(),
            email=email,
            password=hash_password(payload.password),
            phone=payload.phone,
            status="ACTIVE",
        )
        db.add(user)
        db.commit()
        db.refresh(university)
        CacheService.invalidate_namespace(cls.CACHE_NS)
        CacheService.invalidate_namespace("users")
        return cls._serialize(university, email)

    @classmethod
    def update(cls, db: Session, university_id: int, payload: UniversityUpdate) -> dict:
        university = db.exec(
            select(University).where(University.id == university_id)
        ).first()
        if not university:
            raise HTTPException(status_code=404, detail="University not found")
        data = payload.model_dump(exclude_unset=True)
        if "name" in data and data["name"]:
            name = data["name"].strip()
            exists = db.exec(
                select(University).where(
                    University.name == name, University.id != university_id
                )
            ).first()
            if exists:
                raise HTTPException(status_code=409, detail="University name already used")
            university.name = name
            data.pop("name")
        if "status" in data and data["status"]:
            university.status = data.pop("status").upper()
        for key, value in data.items():
            setattr(university, key, value)
        db.commit()
        db.refresh(university)
        CacheService.invalidate_namespace(cls.CACHE_NS)
        return cls._serialize(university, cls._contact_email(db, university.id))

    @classmethod
    def delete(cls, db: Session, university_id: int) -> dict:
        university = db.exec(
            select(University).where(University.id == university_id)
        ).first()
        if not university:
            raise HTTPException(status_code=404, detail="University not found")
        data = cls._serialize(university, cls._contact_email(db, university.id))
        linked_users = db.exec(select(User).where(User.university_id == university_id)).all()
        for user in linked_users:
            user.university_id = None
        db.delete(university)
        db.commit()
        CacheService.invalidate_namespace(cls.CACHE_NS)
        CacheService.invalidate_namespace("users")
        return data
