from sqlalchemy.orm import Session
from app.models.role import Role

class RoleController():
    @staticmethod
    def index(db: Session):
        return db.query(Role).all()
    
    @staticmethod
    def create(db: Session, role: Role):
        role = Role(name=role.name)
        db.add(role)
        db.commit()
        db.refresh(role)
        return role
    @staticmethod
    def delete(db: Session, id: int):
        role = db.query(Role).filter(Role.id == id).first()
        if not role:
            return None
        db.delete(role)
        db.commit()
        return role
    
    @staticmethod
    def update(db: Session, id: int, role: Role):
        db_role = db.query(Role).filter(Role.id == id).first()
        if not db_role:
            return None
        db_role.name = role.name
        db.commit()
        db.refresh(db_role)
        return db_role