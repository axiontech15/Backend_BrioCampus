from datetime import UTC, datetime
from uuid import uuid4

from fastapi import HTTPException
from sqlalchemy import func
from sqlmodel import Session, select

from app.models.category import Category
from app.models.payment import Payment
from app.models.ticket import Ticket
from app.models.user import User
from app.validators.payment import PaymentOut
from app.validators.ticket import TicketCreate, TicketOut, TicketPurchaseRequest, TicketUpdate


class TicketController:
    @staticmethod
    def _serialize(ticket: Ticket) -> dict:
        return TicketOut.model_validate(ticket).model_dump(mode="json")

    @classmethod
    def index(cls, db: Session, user: User | None = None, mine_only: bool = False) -> list[dict]:
        stmt = select(Ticket).order_by(Ticket.id.desc())
        if mine_only and user:
            stmt = stmt.where(Ticket.user_id == user.id)
        return [cls._serialize(t) for t in db.exec(stmt).all()]

    @classmethod
    def show(cls, db: Session, ticket_id: int) -> dict:
        ticket = db.exec(select(Ticket).where(Ticket.id == ticket_id)).first()
        if not ticket:
            raise HTTPException(status_code=404, detail="Ticket not found")
        return cls._serialize(ticket)

    @classmethod
    def create(cls, db: Session, user: User, payload: TicketCreate) -> dict:
        if not db.exec(select(Category).where(Category.id == payload.category_id)).first():
            raise HTTPException(status_code=400, detail="Invalid category_id")
        ticket = Ticket(
            user_id=user.id,
            category_id=payload.category_id,
            subject=payload.subject.strip(),
            description=payload.description,
            status="OPEN",
            priority=payload.priority.upper(),
        )
        db.add(ticket)
        db.commit()
        db.refresh(ticket)
        return cls._serialize(ticket)

    @classmethod
    def update(cls, db: Session, ticket_id: int, payload: TicketUpdate) -> dict:
        ticket = db.exec(select(Ticket).where(Ticket.id == ticket_id)).first()
        if not ticket:
            raise HTTPException(status_code=404, detail="Ticket not found")
        data = payload.model_dump(exclude_unset=True)
        if "status" in data and data["status"]:
            status = data.pop("status").upper()
            ticket.status = status
            if status in {"CLOSED", "RESOLVED"}:
                ticket.closed_at = datetime.now(UTC)
        if "priority" in data and data["priority"]:
            ticket.priority = data.pop("priority").upper()
        for key, value in data.items():
            setattr(ticket, key, value)
        db.commit()
        db.refresh(ticket)
        return cls._serialize(ticket)

    @classmethod
    def delete(cls, db: Session, ticket_id: int) -> dict:
        ticket = db.exec(select(Ticket).where(Ticket.id == ticket_id)).first()
        if not ticket:
            raise HTTPException(status_code=404, detail="Ticket not found")
        data = cls._serialize(ticket)
        db.delete(ticket)
        db.commit()
        return data

    @classmethod
    def balance(cls, db: Session, user: User) -> dict:
        remaining = db.exec(
            select(func.count())
            .select_from(Ticket)
            .where(Ticket.user_id == user.id, Ticket.status == "OPEN")
        ).one()
        return {"user_id": user.id, "remaining_tickets": int(remaining or 0)}

    @classmethod
    def purchase(cls, db: Session, user: User, payload: TicketPurchaseRequest) -> dict:
        category_id = payload.category_id
        category: Category | None = None
        if category_id is None:
            category = db.exec(
                select(Category).where(Category.name.in_(["RECHERCHE", "SEARCH", "TICKET"]))
            ).first()
            if not category:
                category = Category(
                    name="RECHERCHE",
                    description="Tickets de recherche diplômés",
                    quantity=1,
                    status="ACTIVE",
                )
                db.add(category)
                db.flush()
            category_id = category.id
        else:
            category = db.exec(select(Category).where(Category.id == category_id)).first()
            if not category:
                raise HTTPException(status_code=400, detail="Invalid category_id")

        # Pack size comes from the category; payload.quantity may override if provided.
        pack_qty = int(getattr(category, "quantity", None) or 1)
        quantity = payload.quantity if payload.quantity else pack_qty

        reference = f"PAY-{uuid4().hex[:16].upper()}"
        payment = Payment(
            user_id=user.id,
            category_id=category_id,
            amount=payload.amount,
            reference=reference,
            status="PAID",
            payment_date=datetime.now(UTC),
            ticket_quantity=quantity,
        )
        db.add(payment)
        db.flush()

        tickets: list[Ticket] = []
        for i in range(quantity):
            ticket = Ticket(
                user_id=user.id,
                category_id=category_id,
                subject=f"Search credit #{i + 1} ({reference})",
                description=f"Ticket catégorie {category.name}",
                status="OPEN",
                priority="MEDIUM",
            )
            db.add(ticket)
            tickets.append(ticket)

        db.commit()
        db.refresh(payment)
        remaining = cls.balance(db, user)["remaining_tickets"]
        return {
            "payment": PaymentOut.model_validate(payment).model_dump(mode="json"),
            "tickets_created": len(tickets),
            "remaining_tickets": remaining,
        }

    @classmethod
    def consume_one(cls, db: Session, user: User) -> Ticket:
        ticket = db.exec(
            select(Ticket)
            .where(Ticket.user_id == user.id, Ticket.status == "OPEN")
            .order_by(Ticket.id.asc())
            .with_for_update()
        ).first()
        if not ticket:
            raise HTTPException(status_code=402, detail="No remaining search tickets")
        ticket.status = "CLOSED"
        ticket.closed_at = datetime.now(UTC)
        db.flush()
        return ticket
