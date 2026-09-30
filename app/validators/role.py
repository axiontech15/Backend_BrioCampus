from pydantic import BaseModel,Field

class Role(BaseModel):
    id: int
    name: str = Field(min_length=3 ,max_length=255)