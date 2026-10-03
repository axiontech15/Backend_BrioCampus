from pydantic import BaseModel,Field
from datetime import UTC, datetime


class Ticket(BaseModel):
    id: int
    user_id: int = Field(default=None)
    category_id: int = Field(default=None)
    title: str = Field(min_length=3 ,max_length=255)
    description: str = Field(min_length=3 ,max_length=255)
    status: bool = Field(default=True)
    created_at: datetime = Field(default_factory=lambda: datetime.now(UTC))
    updated_at: datetime = Field(default_factory=lambda: datetime.now(UTC))
    closed_at: datetime = Field(default=None)