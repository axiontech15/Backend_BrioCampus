from datetime import datetime

from pydantic import BaseModel, ConfigDict


class LogOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    user_id: int | None = None
    action: str
    description: str | None = None
    ip_address: str | None = None
    user_agent: str | None = None
    is_archived: bool = False
    archived_at: datetime | None = None
    created_at: datetime | None = None
