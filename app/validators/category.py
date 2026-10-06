from datetime import datetime

from pydantic import BaseModel, ConfigDict, Field


class CategoryCreate(BaseModel):
    name: str = Field(min_length=2, max_length=255)
    description: str | None = Field(default=None, max_length=2000)
    quantity: int = Field(default=1, ge=1, le=10000)
    status: str = Field(default="ACTIVE", max_length=30)


class CategoryUpdate(BaseModel):
    name: str | None = Field(default=None, min_length=2, max_length=255)
    description: str | None = Field(default=None, max_length=2000)
    quantity: int | None = Field(default=None, ge=1, le=10000)
    status: str | None = Field(default=None, max_length=30)


class CategoryOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    name: str
    description: str | None = None
    quantity: int = 1
    status: str
    created_at: datetime | None = None
    updated_at: datetime | None = None


Category = CategoryCreate
