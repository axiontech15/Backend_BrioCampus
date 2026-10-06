from datetime import date, datetime

from pydantic import BaseModel, ConfigDict, Field


class GraduateOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    university_id: int
    matricule: str
    full_name: str
    faculty: str
    diploma_date: date
    created_at: datetime | None = None


class GraduateSearchRequest(BaseModel):
    matricule: str | None = Field(default=None, max_length=100)
    full_name: str | None = Field(default=None, max_length=255)
    university_id: int | None = None
    faculty: str | None = Field(default=None, max_length=255)


class GraduateSearchResult(BaseModel):
    found: bool
    graduate: GraduateOut | None = None
    ticket_consumed: bool = False
    remaining_tickets: int = 0


class ImportResult(BaseModel):
    imported: int = 0
    duplicates: int = 0
    invalid: int = 0
    errors: list[str] = Field(default_factory=list)
    palmares_id: int | None = None
