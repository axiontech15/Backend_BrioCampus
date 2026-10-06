from fastapi import HTTPException
from sqlmodel import Session, select

from app.models.category import Category
from app.services.cache import CacheService
from app.validators.category import CategoryCreate, CategoryOut, CategoryUpdate


class CategoryController:
    CACHE_NS = "categories"

    @staticmethod
    def _serialize(category: Category) -> dict:
        data = CategoryOut.model_validate(category).model_dump(mode="json")
        data["quantity"] = int(getattr(category, "quantity", None) or 1)
        return data

    @classmethod
    def index(cls, db: Session) -> list[dict]:
        cached = CacheService.get(cls.CACHE_NS, "all")
        if cached is not None:
            return cached
        rows = db.exec(select(Category).order_by(Category.id)).all()
        data = [cls._serialize(c) for c in rows]
        CacheService.set(cls.CACHE_NS, "all", data)
        return data

    @classmethod
    def show(cls, db: Session, category_id: int) -> dict:
        category = db.exec(select(Category).where(Category.id == category_id)).first()
        if not category:
            raise HTTPException(status_code=404, detail="Category not found")
        return cls._serialize(category)

    @classmethod
    def create(cls, db: Session, payload: CategoryCreate) -> dict:
        name = payload.name.strip()
        if db.exec(select(Category).where(Category.name == name)).first():
            raise HTTPException(status_code=409, detail="Category already exists")
        category = Category(
            name=name,
            description=payload.description,
            quantity=payload.quantity,
            status=payload.status.upper(),
        )
        db.add(category)
        db.commit()
        db.refresh(category)
        CacheService.invalidate_namespace(cls.CACHE_NS)
        return cls._serialize(category)

    @classmethod
    def update(cls, db: Session, category_id: int, payload: CategoryUpdate) -> dict:
        category = db.exec(select(Category).where(Category.id == category_id)).first()
        if not category:
            raise HTTPException(status_code=404, detail="Category not found")
        data = payload.model_dump(exclude_unset=True)
        if "name" in data and data["name"]:
            name = data["name"].strip()
            exists = db.exec(
                select(Category).where(Category.name == name, Category.id != category_id)
            ).first()
            if exists:
                raise HTTPException(status_code=409, detail="Category name already used")
            category.name = name
            data.pop("name")
        if "status" in data and data["status"]:
            category.status = data.pop("status").upper()
        for key, value in data.items():
            setattr(category, key, value)
        db.commit()
        db.refresh(category)
        CacheService.invalidate_namespace(cls.CACHE_NS)
        return cls._serialize(category)

    @classmethod
    def delete(cls, db: Session, category_id: int) -> dict:
        category = db.exec(select(Category).where(Category.id == category_id)).first()
        if not category:
            raise HTTPException(status_code=404, detail="Category not found")
        data = cls._serialize(category)
        db.delete(category)
        db.commit()
        CacheService.invalidate_namespace(cls.CACHE_NS)
        return data
