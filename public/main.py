from fastapi import FastAPI , Depends,  HTTPException
from sqlalchemy.orm import Session
from app.validators.role import Role
from app.controllers.role import RoleController
from config.database import Base, engine , SessionLocal

app = FastAPI()

Base.metadata.create_all(bind=engine)

def database():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()
        
@app.get("/")
def hello():
    return {"api":"hello api"}

@app.get("/api/roles",description="Print all roles in database",status_code=200)
def index(db: Session = Depends(database)):
    return RoleController.index(db)

@app.get("/api/roles/{id}",description="Show role information with id",status_code=200)
def show(id:int):
    return {}

@app.post("/api/roles",description="Create role",status_code=201)
def create(role:Role, db: Session = Depends(database)):
    return RoleController.create(db, role)

@app.put("/api/roles/{id}",description="Update role with id")
def update(id:int, role:Role, db: Session = Depends(database)):
    return RoleController.update(db, id, role)

@app.delete("/api/roles/{id}",description="Delete role with id")
def delete(id:int, db: Session = Depends(database)):
    return RoleController.delete(db, id)