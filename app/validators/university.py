from datetime import datetime

from pydantic import BaseModel, ConfigDict, EmailStr, Field


class UniversityCreate(BaseModel):
    name: str = Field(min_length=2, max_length=255)
    logo: str | None = Field(default=None, max_length=500)
    address: str | None = Field(default=None, max_length=2000)
    status: str = Field(default="ACTIVE", max_length=30)
    # Compte utilisateur lié (rôle UNIVERSITY)
    email: EmailStr
    password: str = Field(min_length=8, max_length=128)
    first_name: str = Field(min_length=1, max_length=100)
    last_name: str = Field(min_length=1, max_length=100)
    phone: str | None = Field(default=None, max_length=30)


class UniversityUpdate(BaseModel):
    name: str | None = Field(default=None, min_length=2, max_length=255)
    logo: str | None = Field(default=None, max_length=500)
    address: str | None = Field(default=None, max_length=2000)
    status: str | None = Field(default=None, max_length=30)


class UniversityOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    name: str
    logo: str | None = None
    address: str | None = None
    status: str
    created_at: datetime | None = None
    updated_at: datetime | None = None
    contact_email: str | None = None
