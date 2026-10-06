from datetime import datetime

from pydantic import BaseModel, ConfigDict, EmailStr, Field


class UserCreate(BaseModel):
    role_id: int
    university_id: int | None = None
    first_name: str = Field(min_length=1, max_length=100)
    last_name: str = Field(min_length=1, max_length=100)
    email: EmailStr
    password: str = Field(min_length=8, max_length=128)
    matricule: str | None = Field(default=None, max_length=100)
    phone: str | None = Field(default=None, max_length=30)
    status: str = Field(default="ACTIVE", max_length=30)


class UserUpdate(BaseModel):
    role_id: int | None = None
    university_id: int | None = None
    first_name: str | None = Field(default=None, min_length=1, max_length=100)
    last_name: str | None = Field(default=None, min_length=1, max_length=100)
    email: EmailStr | None = None
    password: str | None = Field(default=None, min_length=8, max_length=128)
    matricule: str | None = Field(default=None, max_length=100)
    phone: str | None = Field(default=None, max_length=30)
    status: str | None = Field(default=None, max_length=30)


class AccountUpdate(BaseModel):
    first_name: str | None = Field(default=None, min_length=1, max_length=100)
    last_name: str | None = Field(default=None, min_length=1, max_length=100)
    phone: str | None = Field(default=None, max_length=30)
    password: str | None = Field(default=None, min_length=8, max_length=128)


class UserOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    role_id: int
    university_id: int | None = None
    first_name: str
    last_name: str
    email: EmailStr
    matricule: str | None = None
    phone: str | None = None
    status: str
    created_at: datetime | None = None
    updated_at: datetime | None = None
    role_name: str | None = None


class ProfileCreate(BaseModel):
    first_name: str = Field(min_length=1, max_length=100)
    last_name: str = Field(min_length=1, max_length=100)
    email: EmailStr
    password: str = Field(min_length=8, max_length=128)
    matricule: str | None = Field(default=None, max_length=100)
    phone: str | None = Field(default=None, max_length=30)
    university_id: int | None = None


class ProfileUpdate(BaseModel):
    first_name: str | None = Field(default=None, min_length=1, max_length=100)
    last_name: str | None = Field(default=None, min_length=1, max_length=100)
    phone: str | None = Field(default=None, max_length=30)
    matricule: str | None = Field(default=None, max_length=100)
    university_id: int | None = None
    status: str | None = Field(default=None, max_length=30)
