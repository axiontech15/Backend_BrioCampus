from datetime import datetime
from sqlalchemy import Column, DateTime, Integer, String
from config.database import Base
import datetime

class Role(Base):
    __tablename__ = "roles"
    id = Column(Integer, primary_key=True, index=False)
    name = Column(String(255), unique=True, index=False, nullable=False)
    created_at = Column(DateTime, default=datetime.datetime.now, nullable=True)
    updated_at = Column(DateTime, default=datetime.datetime.now, nullable=True, onupdate=datetime.datetime.now)