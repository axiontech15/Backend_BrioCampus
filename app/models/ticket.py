from datetime import UTC, datetime

from sqlalchemy import Column, DateTime, ForeignKey, Integer, String, Text

from config.database import Base


class Ticket(Base):
    __tablename__ = "tickets"

    id = Column(Integer, primary_key=True, autoincrement=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False, index=True)
    category_id = Column(
        Integer, ForeignKey("categories.id"), nullable=False, index=True
    )
    subject = Column(String(255), nullable=False)
    description = Column(Text, nullable=True)
    status = Column(String(30), default="OPEN", nullable=False, index=True)
    priority = Column(String(30), default="MEDIUM", nullable=False)
    created_at = Column(DateTime, default=lambda: datetime.now(UTC), nullable=False)
    updated_at = Column(
        DateTime,
        default=lambda: datetime.now(UTC),
        onupdate=lambda: datetime.now(UTC),
        nullable=False,
    )
    closed_at = Column(DateTime, nullable=True)
