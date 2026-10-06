from fastapi import APIRouter, Depends
from sqlmodel import Session

from app.controllers.ticket import TicketController
from app.dependencies.auth import get_current_user, require_roles
from app.models.user import User
from app.services.logger import ActivityLogger
from app.validators.ticket import TicketCreate, TicketPurchaseRequest, TicketUpdate
from config.database import get_db

router = APIRouter(prefix="/tickets", tags=["Tickets"])


@router.get("")
def index(
    db: Session = Depends(get_db),
    user: User = Depends(get_current_user),
):
    role = user.role.name.upper() if user.role else ""
    mine_only = role != "ADMIN"
    return TicketController.index(db, user=user, mine_only=mine_only)


@router.get("/balance")
def balance(
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("RH", "ADMIN")),
):
    return TicketController.balance(db, user)


@router.post("/purchase", status_code=201)
def purchase(
    payload: TicketPurchaseRequest,
    db: Session = Depends(get_db),
    user: User = Depends(require_roles("RH")),
):
    result = TicketController.purchase(db, user, payload)
    ActivityLogger.write(
        db,
        action="PAYMENT",
        description=f"Tickets purchased: {payload.quantity}",
        user_id=user.id,
    )
    return result


@router.get("/{ticket_id}")
def show(
    ticket_id: int,
    db: Session = Depends(get_db),
    _: User = Depends(get_current_user),
):
    return TicketController.show(db, ticket_id)


@router.post("", status_code=201)
def create(
    payload: TicketCreate,
    db: Session = Depends(get_db),
    user: User = Depends(get_current_user),
):
    return TicketController.create(db, user, payload)


@router.put("/{ticket_id}")
def update(
    ticket_id: int,
    payload: TicketUpdate,
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN", "RH")),
):
    return TicketController.update(db, ticket_id, payload)


@router.delete("/{ticket_id}")
def delete(
    ticket_id: int,
    db: Session = Depends(get_db),
    _: User = Depends(require_roles("ADMIN")),
):
    return TicketController.delete(db, ticket_id)
