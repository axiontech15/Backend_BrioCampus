from fastapi import FastAPI
from app.validators.role import Role
app = FastAPI()

@app.get("/")
def hello():
    return {"api":"hello api"}

@app.get("/api/roles",description="Print all roles in database",status_code=200)
def index():
    return {}

@app.get("/api/roles/{id}",description="Show role information with id",status_code=200)
def show(id:int):
    return {}

@app.post("/api/roles",description="Create role",status_code=201)
def create(role:Role):
    return {}

@app.put("/api/roles/{id}",description="Update role with id")
def update(id:int):
    return {}

@app.delete("/api/roles/{id}",description="Delete role with id")
def delete(id:int):
    return {}