from contextlib import asynccontextmanager
from pathlib import Path

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy import inspect, text

from app.middleware.security import SecurityHeadersMiddleware
from app.routers import register_routers
from config.database import Base, SessionLocal, engine
from config.redis import redis_available
from config.settings import get_settings

# Ensure all models are registered on metadata
import app.models  # noqa: F401


def ensure_schema() -> None:
    """Apply lightweight column additions for existing MySQL databases."""
    insp = inspect(engine)
    if "categories" not in insp.get_table_names():
        return
    cols = {c["name"] for c in insp.get_columns("categories")}
    if "quantity" not in cols:
        with engine.begin() as conn:
            conn.execute(
                text(
                    "ALTER TABLE categories "
                    "ADD COLUMN quantity INT NOT NULL DEFAULT 1"
                )
            )


def seed_defaults() -> None:
    """Create default roles and an admin user if the database is empty."""
    from sqlmodel import select

    from app.models.category import Category
    from app.models.role import Role
    from app.models.user import User
    from config.security import hash_password

    db = SessionLocal()
    try:
        defaults = ["ADMIN", "RH", "UNIVERSITY", "USER"]
        for name in defaults:
            if not db.exec(select(Role).where(Role.name == name)).first():
                db.add(Role(name=name))
        db.commit()

        if not db.exec(select(Category).where(Category.name == "RECHERCHE")).first():
            db.add(
                Category(
                    name="RECHERCHE",
                    description="Tickets de recherche de diplômés",
                    quantity=10,
                    status="ACTIVE",
                )
            )
            db.commit()
        else:
            # Backfill quantity when missing / zero on legacy rows
            for cat in db.exec(select(Category)).all():
                if not getattr(cat, "quantity", None):
                    cat.quantity = 10 if cat.name.upper() == "RECHERCHE" else 1
            db.commit()

        admin_role = db.exec(select(Role).where(Role.name == "ADMIN")).first()
        # Prefer a real TLD — EmailStr rejects reserved names like `.local`.
        legacy = db.exec(
            select(User).where(User.email == "admin@briocampus.local")
        ).first()
        if legacy:
            legacy.email = "admin@briocampus.cd"
            db.commit()
        if admin_role and not db.exec(
            select(User).where(User.email == "admin@briocampus.cd")
        ).first():
            db.add(
                User(
                    role_id=admin_role.id,
                    first_name="Admin",
                    last_name="BrioCampus",
                    email="admin@briocampus.cd",
                    password=hash_password("Admin123!"),
                    status="ACTIVE",
                )
            )
            db.commit()
    finally:
        db.close()


@asynccontextmanager
async def lifespan(_: FastAPI):
    settings = get_settings()
    Path(settings.UPLOAD_DIR).mkdir(parents=True, exist_ok=True)
    Base.metadata.create_all(bind=engine)
    ensure_schema()
    seed_defaults()
    yield


settings = get_settings()
app = FastAPI(
    title=settings.APP_NAME,
    version="0.1.0",
    description="API BrioCampus — vérification des diplômes",
    lifespan=lifespan,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.cors_origins_list,
    allow_credentials=settings.CORS_ALLOW_CREDENTIALS,
    allow_methods=["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
    allow_headers=["Authorization", "Content-Type", "Accept", "X-Requested-With"],
    expose_headers=["X-Request-Id"],
    max_age=600,
)
app.add_middleware(SecurityHeadersMiddleware)

register_routers(app, prefix=settings.API_PREFIX)


@app.get("/", tags=["Health"])
def root():
    return {
        "api": settings.APP_NAME,
        "status": "ok",
        "docs": "/docs",
        "redis": redis_available(),
    }


@app.get("/health", tags=["Health"])
def health():
    return {
        "status": "healthy",
        "redis": redis_available(),
        "env": settings.APP_ENV,
    }
