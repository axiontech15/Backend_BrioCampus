from collections.abc import Generator

from sqlalchemy import create_engine
from sqlalchemy.orm import DeclarativeBase
from sqlmodel import Session

from config.settings import get_settings

settings = get_settings()

engine = create_engine(
    settings.DATABASE_URL,
    pool_pre_ping=True,
    pool_recycle=3600,
)


class Base(DeclarativeBase):
    pass


def SessionLocal() -> Session:
    return Session(engine)


def get_db() -> Generator[Session, None, None]:
    with Session(engine) as db:
        yield db
