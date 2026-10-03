from pydantic import BaseModel,Field
from datetime import UTC, datetime

class Category(BaseModel):
    id: int
    name: str = Field(min_length=3 ,max_length=255)
    description: str = Field(min_length=3 ,max_length=255)
    status: bool = Field(default=True)
    created_at: datetime = Field(default_factory=lambda: datetime.now(UTC))