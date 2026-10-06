from datetime import UTC, datetime

from sqlalchemy import Column, DateTime, ForeignKey, Integer, Numeric, String

from config.database import Base


class Payment(Base):
    __tablename__ = "payments"

    id = Column(Integer, primary_key=True, autoincrement=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False, index=True)
    category_id = Column(Integer, ForeignKey("categories.id"), nullable=True)
    amount = Column(Numeric(12, 2), nullable=False)
    reference = Column(String(100), unique=True, nullable=False, index=True)
    status = Column(String(30), default="PENDING", nullable=False)
    payment_date = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=lambda: datetime.now(UTC), nullable=False)
    ticket_quantity = Column(Integer, default=1, nullable=False)
