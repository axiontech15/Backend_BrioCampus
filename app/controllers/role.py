from fastapi import HTTPException
from sqlmodel import Session, select

from app.models.role import Role
from app.services.cache import CacheService
from app.validators.role import RoleCreate, RoleOut, RoleUpdate


class RoleController:
    CACHE_NS = "roles"

    @staticmethod
    def _serialize(role: Role) -> dict:
        return RoleOut.model_validate(role).model_dump(mode="json")

    @classmethod
    def index(cls, db: Session) -> list[dict]:
        cached = CacheService.get(cls.CACHE_NS, "all")
        if cached is not None:
            return cached
        roles = db.exec(select(Role).order_by(Role.id)).all()
        data = [cls._serialize(r) for r in roles]
        CacheService.set(cls.CACHE_NS, "all", data)
        return data

    @classmethod
    def show(cls, db: Session, role_id: int) -> dict:
        cached = CacheService.get(cls.CACHE_NS, str(role_id))
        if cached is not None:
            return cached
        role = db.exec(select(Role).where(Role.id == role_id)).first()
        if not role:
            raise HTTPException(status_code=404, detail="Role not found")
        data = cls._serialize(role)
        CacheService.set(cls.CACHE_NS, str(role_id), data)
        return data

    @classmethod
    def create(cls, db: Session, payload: RoleCreate) -> dict:
        name = payload.name.strip().upper()
        if db.exec(select(Role).where(Role.name == name)).first():
            raise HTTPException(status_code=409, detail="Role already exists")
        role = Role(name=name)
        db.add(role)
        db.commit()
        db.refresh(role)
        CacheService.invalidate_namespace(cls.CACHE_NS)
        return cls._serialize(role)

    @classmethod
    def update(cls, db: Session, role_id: int, payload: RoleUpdate) -> dict:
        role = db.exec(select(Role).where(Role.id == role_id)).first()
        if not role:
            raise HTTPException(status_code=404, detail="Role not found")
        name = payload.name.strip().upper()
        exists = db.exec(
            select(Role).where(Role.name == name, Role.id != role_id)
        ).first()
        if exists:
            raise HTTPException(status_code=409, detail="Role name already used")
        role.name = name
        db.commit()
        db.refresh(role)
        CacheService.invalidate_namespace(cls.CACHE_NS)
        return cls._serialize(role)

    @classmethod
    def delete(cls, db: Session, role_id: int) -> dict:
        role = db.exec(select(Role).where(Role.id == role_id)).first()
        if not role:
            raise HTTPException(status_code=404, detail="Role not found")
        data = cls._serialize(role)
        db.delete(role)
        db.commit()
        CacheService.invalidate_namespace(cls.CACHE_NS)
        return data
