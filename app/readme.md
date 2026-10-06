# Application BrioCampus

```text
app/
├── controllers/   # Règles métier (CRUD, recherche, import, stats)
├── dependencies/  # JWT Bearer + contrôle des rôles
├── middleware/    # Headers sécurité (CORS côté app, XSS, MIME, SQLi filter)
├── models/        # Tables SQLAlchemy
├── routers/       # Endpoints FastAPI
├── services/      # Redis cache, OTP, e-mail, activity logger
└── validators/    # Schémas Pydantic d'entrée/sortie
```
