
from sqlmodel import Session

from app.validators.category import Category


class CategoryController():
    
    @staticmethod
    def index(db: Session):
        return db.query(Category).all()
    
    @staticmethod
    def create(db: Session, category: Category):
        category = Category(name=category.name)
        db.add(category)
        db.commit()
        db.refresh(category)
        return category
    
    @staticmethod
    def delete(db: Session, id: int):
        category = db.query(Category).filter(Category.id == id).first()
        if not category:
            return None
        db.delete(category)
        db.commit()
        return category
    
    @staticmethod
    def update(db: Session, id: int, category: Category):
        db_category = db.query(Category).filter(Category.id == id).first()
        if not db_category:
            return None
        db_category.name = category.name
        db.commit()
        db.refresh(db_category)
        return db_category
    