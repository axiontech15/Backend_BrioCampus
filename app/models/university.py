from datetime import UTC, datetime

from sqlalchemy import Column, DateTime, Integer, String, Text

from config.database import Base


class University(Base):
    __tablename__ = "universities"

    id = Column(Integer, primary_key=True, autoincrement=True)
    name = Column(String(255), unique=True, nullable=False)
    logo = Column(String(500), nullable=True)
    address = Column(Text, nullable=True)
    status = Column(String(30), default="ACTIVE", nullable=False)
    created_at = Column(DateTime, default=lambda: datetime.now(UTC), nullable=False)
    updated_at = Column(
        DateTime,
        default=lambda: datetime.now(UTC),
        onupdate=lambda: datetime.now(UTC),
        nullable=False,
    )
