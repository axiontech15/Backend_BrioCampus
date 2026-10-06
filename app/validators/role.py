from datetime import datetime

from pydantic import BaseModel, ConfigDict, Field


class RoleCreate(BaseModel):
    name: str = Field(min_length=2, max_length=50)


class RoleUpdate(BaseModel):
    name: str = Field(min_length=2, max_length=50)


class RoleOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    name: str
    created_at: datetime | None = None
    updated_at: datetime | None = None


# Backward-compatible alias used by existing imports
Role = RoleCreate
