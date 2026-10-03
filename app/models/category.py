from sqlalchemy import Column, Integer, String , Boolean , DateTime
from config.database import Base

class Category(Base):
    __tablename__ = "categories"
    id = Column(Integer, primary_key=True, index=False)
    name = Column(String(255), unique=True, index=False, nullable=False)
    status = Column(Boolean, default=True, nullable=False)
    created_at = Column(DateTime, default=DateTime.now, nullable=True)
    updated_at = Column(DateTime, default=DateTime.now, nullable=True, onupdate=DateTime.now)