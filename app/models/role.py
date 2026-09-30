from sqlmodel import Field, SQLModel
from datetime import UTC, datetime

class Role(SQLModel,table=True):
    id:int = Field(default=None,primary_key=True)
    name:str
    created_at: datetime = Field(default_factory=lambda: datetime.now(UTC))