from sqlmodel import Session
from app.validators.ticket import Ticket

class TicketController():
    
    @staticmethod
    def index(db: Session):
        return db.query(Ticket).all()
    
    @staticmethod
    def create(db: Session, ticket: Ticket):
        ticket = Ticket(title=ticket.title, description=ticket.description, status=ticket.status)
        db.add(ticket)
        db.commit()
        db.refresh(ticket)
        return ticket
    
    @staticmethod
    def delete(db: Session, id: int):
        ticket = db.query(Ticket).filter(Ticket.id == id).first()
        if not ticket:
            return None
        db.delete(ticket)
        db.commit()
        return ticket
    
    @staticmethod
    def update(db: Session, id: int, ticket: Ticket):
        db_ticket = db.query(Ticket).filter(Ticket.id == id).first()
        if not db_ticket:
            return None
        db_ticket.title = ticket.title
        db_ticket.description = ticket.description
        db_ticket.status = ticket.status
        db.commit()
        db.refresh(db_ticket)
        return db_ticket