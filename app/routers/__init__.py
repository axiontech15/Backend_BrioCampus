from fastapi import FastAPI

from app.routers import (
    auth,
    categories,
    graduates,
    logs,
    notifications,
    roles,
    tickets,
    universities,
    users,
)


def register_routers(app: FastAPI, prefix: str = "/api") -> None:
    app.include_router(auth.router, prefix=prefix)
    app.include_router(roles.router, prefix=prefix)
    app.include_router(users.router, prefix=prefix)
    app.include_router(categories.router, prefix=prefix)
    app.include_router(universities.router, prefix=prefix)
    app.include_router(tickets.router, prefix=prefix)
    app.include_router(logs.router, prefix=prefix)
    app.include_router(notifications.router, prefix=prefix)
    app.include_router(graduates.router, prefix=prefix)
