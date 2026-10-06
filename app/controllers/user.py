from fastapi import HTTPException
from sqlmodel import Session, select

from app.models.role import Role
from app.models.user import User
from app.services.cache import CacheService
from app.validators.user import (
    AccountUpdate,
    ProfileCreate,
    ProfileUpdate,
    UserCreate,
    UserOut,
    UserUpdate,
)
from config.security import hash_password


class UserController:
    CACHE_NS = "users"

    @staticmethod
    def _serialize(user: User) -> dict:
        data = UserOut.model_validate(user).model_dump(mode="json")
        data["role_name"] = user.role.name if user.role else None
        return data

    @classmethod
    def index(cls, db: Session) -> list[dict]:
        cached = CacheService.get(cls.CACHE_NS, "all")
        if cached is not None:
            return cached
        users = db.exec(select(User).order_by(User.id)).all()
        data = [cls._serialize(u) for u in users]
        CacheService.set(cls.CACHE_NS, "all", data)
        return data

    @classmethod
    def show(cls, db: Session, user_id: int) -> dict:
        user = db.exec(select(User).where(User.id == user_id)).first()
        if not user:
            raise HTTPException(status_code=404, detail="User not found")
        return cls._serialize(user)

    @classmethod
    def create(cls, db: Session, payload: UserCreate) -> dict:
        email = str(payload.email).lower()
        if db.exec(select(User).where(User.email == email)).first():
            raise HTTPException(status_code=409, detail="Email already registered")
        if not db.exec(select(Role).where(Role.id == payload.role_id)).first():
            raise HTTPException(status_code=400, detail="Invalid role_id")
        if payload.matricule and db.exec(
            select(User).where(User.matricule == payload.matricule)
        ).first():
            raise HTTPException(status_code=409, detail="Matricule already used")

        user = User(
            role_id=payload.role_id,
            university_id=payload.university_id,
            first_name=payload.first_name.strip(),
            last_name=payload.last_name.strip(),
            email=email,
            password=hash_password(payload.password),
            matricule=payload.matricule,
            phone=payload.phone,
            status=payload.status.upper(),
        )
        db.add(user)
        db.commit()
        db.refresh(user)
        CacheService.invalidate_namespace(cls.CACHE_NS)
        return cls._serialize(user)

    @classmethod
    def update(cls, db: Session, user_id: int, payload: UserUpdate) -> dict:
        user = db.exec(select(User).where(User.id == user_id)).first()
        if not user:
            raise HTTPException(status_code=404, detail="User not found")

        data = payload.model_dump(exclude_unset=True)
        if "email" in data and data["email"]:
            email = str(data["email"]).lower()
            exists = db.exec(
                select(User).where(User.email == email, User.id != user_id)
            ).first()
            if exists:
                raise HTTPException(status_code=409, detail="Email already registered")
            user.email = email
            data.pop("email")
        if "password" in data and data["password"]:
            user.password = hash_password(data.pop("password"))
        if "status" in data and data["status"]:
            user.status = data.pop("status").upper()
        if "role_id" in data and data["role_id"] is not None:
            if not db.exec(select(Role).where(Role.id == data["role_id"])).first():
                raise HTTPException(status_code=400, detail="Invalid role_id")
            user.role_id = data.pop("role_id")
        for key, value in data.items():
            setattr(user, key, value)

        db.commit()
        db.refresh(user)
        CacheService.invalidate_namespace(cls.CACHE_NS)
        return cls._serialize(user)

    @classmethod
    def delete(cls, db: Session, user_id: int) -> dict:
        user = db.exec(select(User).where(User.id == user_id)).first()
        if not user:
            raise HTTPException(status_code=404, detail="User not found")
        data = cls._serialize(user)
        db.delete(user)
        db.commit()
        CacheService.invalidate_namespace(cls.CACHE_NS)
        return data

    @classmethod
    def update_account(cls, db: Session, user: User, payload: AccountUpdate) -> dict:
        data = payload.model_dump(exclude_unset=True)
        if "password" in data and data["password"]:
            user.password = hash_password(data.pop("password"))
        for key, value in data.items():
            setattr(user, key, value)
        db.commit()
        db.refresh(user)
        CacheService.invalidate_namespace(cls.CACHE_NS)
        return cls._serialize(user)

    @classmethod
    def create_profile(cls, db: Session, payload: ProfileCreate, role_name: str = "USER") -> dict:
        role = db.exec(select(Role).where(Role.name == role_name.upper())).first()
        if not role:
            raise HTTPException(status_code=400, detail=f"Role {role_name} missing")
        return cls.create(
            db,
            UserCreate(
                role_id=role.id,
                university_id=payload.university_id,
                first_name=payload.first_name,
                last_name=payload.last_name,
                email=payload.email,
                password=payload.password,
                matricule=payload.matricule,
                phone=payload.phone,
            ),
        )

    @classmethod
    def update_profile(cls, db: Session, user_id: int, payload: ProfileUpdate) -> dict:
        return cls.update(db, user_id, UserUpdate(**payload.model_dump(exclude_unset=True)))
