from datetime import UTC, datetime

from sqlalchemy import Column, DateTime, ForeignKey, Integer, String

from config.database import Base


class Palmares(Base):
    __tablename__ = "palmares"

    id = Column(Integer, primary_key=True, autoincrement=True)
    university_id = Column(
        Integer, ForeignKey("universities.id"), nullable=False, index=True
    )
    file_path = Column(String(500), nullable=False)
    file_name = Column(String(255), nullable=False)
    uploaded_by = Column(Integer, ForeignKey("users.id"), nullable=False)
    status = Column(String(30), default="PENDING", nullable=False)
    uploaded_at = Column(DateTime, default=lambda: datetime.now(UTC), nullable=False)
    verified_at = Column(DateTime, nullable=True)
    verified_by = Column(Integer, ForeignKey("users.id"), nullable=True)
