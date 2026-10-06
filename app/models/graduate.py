from datetime import UTC, date, datetime

from sqlalchemy import Column, Date, DateTime, ForeignKey, Integer, String, UniqueConstraint

from config.database import Base


class Graduate(Base):
    """Diplômés importés via le palmarès (recherche RH)."""

    __tablename__ = "graduates"
    __table_args__ = (
        UniqueConstraint("university_id", "matricule", name="uq_graduate_univ_matricule"),
    )

    id = Column(Integer, primary_key=True, autoincrement=True)
    university_id = Column(
        Integer, ForeignKey("universities.id"), nullable=False, index=True
    )
    palmares_id = Column(Integer, ForeignKey("palmares.id"), nullable=True)
    matricule = Column(String(100), nullable=False, index=True)
    full_name = Column(String(255), nullable=False)
    faculty = Column(String(255), nullable=False)
    diploma_date = Column(Date, nullable=False)
    created_at = Column(DateTime, default=lambda: datetime.now(UTC), nullable=False)
    updated_at = Column(
        DateTime,
        default=lambda: datetime.now(UTC),
        onupdate=lambda: datetime.now(UTC),
        nullable=False,
    )


class ProfileSearch(Base):
    """Historique des recherches RH ayant trouvé un profil."""

    __tablename__ = "profile_searches"

    id = Column(Integer, primary_key=True, autoincrement=True)
    rh_user_id = Column(Integer, ForeignKey("users.id"), nullable=False, index=True)
    graduate_id = Column(Integer, ForeignKey("graduates.id"), nullable=False, index=True)
    university_id = Column(Integer, ForeignKey("universities.id"), nullable=False)
    ticket_id = Column(Integer, ForeignKey("tickets.id"), nullable=True)
    created_at = Column(DateTime, default=lambda: datetime.now(UTC), nullable=False)
