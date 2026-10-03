
from config.database import Base
from sqlalchemy import DateTime, Column, Integer, String, Boolean

class Ticket(Base):
    __tablename__ = "tickets"
    id = Column(Integer, primary_key=True, index=False)
    title = Column(String(255), unique=True, index=False, nullable=False)
    description = Column(String(255), unique=True, index=False, nullable=False)
    status = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime, default=DateTime.now, nullable=True)
    updated_at = Column(DateTime, default=DateTime.now, nullable=True, onupdate=DateTime.now)
    closed_at = Column(DateTime, default=None, nullable=True)