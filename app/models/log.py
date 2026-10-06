from datetime import UTC, datetime

from sqlalchemy import Boolean, Column, DateTime, ForeignKey, Integer, String, Text

from config.database import Base


class Log(Base):
    __tablename__ = "logs"

    id = Column(Integer, primary_key=True, autoincrement=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True, index=True)
    action = Column(String(50), nullable=False, index=True)
    description = Column(Text, nullable=True)
    ip_address = Column(String(45), nullable=True)
    user_agent = Column(String(500), nullable=True)
    is_archived = Column(Boolean, default=False, nullable=False)
    archived_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=lambda: datetime.now(UTC), nullable=False)
