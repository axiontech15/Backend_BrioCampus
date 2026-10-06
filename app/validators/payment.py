from datetime import datetime
from decimal import Decimal

from pydantic import BaseModel, ConfigDict, Field


class PaymentOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    user_id: int
    category_id: int | None = None
    amount: Decimal
    reference: str
    status: str
    payment_date: datetime | None = None
    created_at: datetime | None = None
    ticket_quantity: int = 1


class PaymentCreate(BaseModel):
    category_id: int | None = None
    amount: float = Field(gt=0)
    ticket_quantity: int = Field(ge=1, le=1000)
