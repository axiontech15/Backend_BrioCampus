from datetime import UTC, datetime

from sqlalchemy import Column, DateTime, ForeignKey, Integer, String
from sqlalchemy.orm import relationship

from config.database import Base


class User(Base):
    __tablename__ = "users"

    id = Column(Integer, primary_key=True, autoincrement=True)
    role_id = Column(Integer, ForeignKey("roles.id"), nullable=False, index=True)
    university_id = Column(
        Integer, ForeignKey("universities.id"), nullable=True, index=True
    )
    first_name = Column(String(100), nullable=False)
    last_name = Column(String(100), nullable=False)
    email = Column(String(255), unique=True, nullable=False, index=True)
    password = Column(String(255), nullable=False)
    matricule = Column(String(100), unique=True, nullable=True, index=True)
    phone = Column(String(30), nullable=True)
    status = Column(String(30), default="ACTIVE", nullable=False)
    created_at = Column(DateTime, default=lambda: datetime.now(UTC), nullable=False)
    updated_at = Column(
        DateTime,
        default=lambda: datetime.now(UTC),
        onupdate=lambda: datetime.now(UTC),
        nullable=False,
    )

    role = relationship("Role", lazy="joined")
    university = relationship("University", lazy="joined", foreign_keys=[university_id])
