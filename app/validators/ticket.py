from datetime import datetime

from pydantic import BaseModel, ConfigDict, Field


class TicketCreate(BaseModel):
    category_id: int
    subject: str = Field(min_length=3, max_length=255)
    description: str | None = Field(default=None, max_length=5000)
    priority: str = Field(default="MEDIUM", max_length=30)


class TicketUpdate(BaseModel):
    category_id: int | None = None
    subject: str | None = Field(default=None, min_length=3, max_length=255)
    description: str | None = Field(default=None, max_length=5000)
    status: str | None = Field(default=None, max_length=30)
    priority: str | None = Field(default=None, max_length=30)


class TicketOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    user_id: int
    category_id: int
    subject: str
    description: str | None = None
    status: str
    priority: str
    created_at: datetime | None = None
    updated_at: datetime | None = None
    closed_at: datetime | None = None


class TicketPurchaseRequest(BaseModel):
    """quantity is optional: when omitted, category.quantity (pack size) is used."""
    quantity: int | None = Field(default=None, ge=1, le=10000)
    category_id: int | None = None
    amount: float = Field(gt=0)


Ticket = TicketCreate
