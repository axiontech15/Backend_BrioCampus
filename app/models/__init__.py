from app.models.category import Category
from app.models.graduate import Graduate, ProfileSearch
from app.models.log import Log
from app.models.notification import Notification
from app.models.palmares import Palmares
from app.models.payment import Payment
from app.models.role import Role
from app.models.ticket import Ticket
from app.models.university import University
from app.models.user import User

__all__ = [
    "Role",
    "User",
    "University",
    "Palmares",
    "Category",
    "Ticket",
    "Payment",
    "Log",
    "Notification",
    "Graduate",
    "ProfileSearch",
]
