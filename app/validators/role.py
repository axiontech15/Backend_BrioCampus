from pydantic import BaseModel,Field
import datetime

class Role(BaseModel):
    name: str = Field(min_length=3 ,max_length=255)