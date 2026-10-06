# Briocampus - guide technique

Briocampus est une API FastAPI pour la vérification des diplômes. Ce document décrit l'état technique du dépôt et les commandes nécessaires pour lancer le backend localement.

## État actuel

| Composant | État |
| --- | --- |
| API | FastAPI — auth JWT, CRUD admin/RH/université, recherche diplômés |
| Python | 3.12+ |
| Dépendances | `uv` / `pyproject.toml` |
| Base de données | MySQL via SQLAlchemy (`DATABASE_URL`) |
| Cache | Redis (`REDIS_URL`) — listes rôles/catégories/universités/users + OTP |
| Sécurité | CORS, JWT Bearer, headers XSS/MIME/clickjacking, filtre injection, ORM paramétré |

Les fonctionnalités métier sont décrites dans le [README fonctionnel](README.md) et le [suivi des tâches](todo.md).

## Structure du dépôt

```text
.
├── app/
│   ├── controllers/     # Logique métier
│   ├── dependencies/    # Auth JWT / rôles
│   ├── middleware/      # Headers sécurité + filtre XSS/SQLi
│   ├── models/          # Modèles SQLAlchemy
│   ├── routers/         # Routes API
│   ├── services/        # Cache Redis, OTP, e-mail, logs
│   └── validators/      # Schémas Pydantic
├── config/              # Settings, DB, Redis, JWT
├── database/            # Documentation du schéma
├── documentation/       # Guides
├── public/main.py       # Point d'entrée FastAPI
├── .env.example
└── pyproject.toml
```

## Prérequis

- Python 3.12+
- [`uv`](https://docs.astral.sh/uv/)
- MySQL (base `briocampus`)
- Redis (optionnel mais recommandé pour le cache et les OTP)

## Installation

```bash
uv sync
cp .env.example .env
# Adapter DATABASE_URL, REDIS_URL, JWT_SECRET
```

## Lancement en développement

```bash
uv run uvicorn --app-dir public main:app --reload --host 127.0.0.1 --port 8080
```

Au démarrage, les tables sont créées et un admin par défaut est seedé :

- e-mail : `admin@briocampus.cd`
- mot de passe : `Admin123!`

## Vérification

- Swagger : <http://127.0.0.1:8080/docs>
- Health : <http://127.0.0.1:8080/health>
- Login :

```bash
curl -X POST http://127.0.0.1:8080/api/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@briocampus.cd","password":"Admin123!"}'
```

## Endpoints principaux

| Préfixe | Rôle | Description |
| --- | --- | --- |
| `/api/auth/*` | public / authentifié | Login, refresh, OTP, reset password, compte |
| `/api/roles` | ADMIN | CRUD rôles |
| `/api/users` | ADMIN (+ RH profils) | CRUD utilisateurs / profils |
| `/api/categories` | ADMIN (écriture) | Catégories de tickets |
| `/api/universities` | ADMIN / UNIVERSITY | Universités + profil univ. |
| `/api/tickets` | RH / ADMIN | Solde, achat, CRUD tickets |
| `/api/rh/search` | RH | Recherche diplômé (consomme 1 ticket si trouvé) |
| `/api/rh/stats` | RH | Tickets restants + profils consultés |
| `/api/universities/palmares/import` | UNIVERSITY | Import CSV palmarès |
| `/api/universities/stats` | UNIVERSITY | Stats diplômés / RH |
| `/api/logs` | ADMIN (+ own) | Consultation, archive, purge |
| `/api/notifications` | authentifié | Notifications |

### Import CSV palmarès

Colonnes requises : `matricule`, `full_name`, `faculty`, `diploma_date` (`YYYY-MM-DD`).

## Sécurité

- **JWT** : Bearer access + refresh tokens
- **CORS** : origines via `CORS_ORIGINS` (dev frontend Vite : `http://localhost:5173`)
- **MIME** : `X-Content-Type-Options: nosniff` + contrôle MIME upload
- **XSS** : headers `X-XSS-Protection`, CSP ; filtre sur path/query
- **SQLi** : requêtes ORM paramétrées uniquement ; filtre signatures dangereuses
- **Mots de passe** : bcrypt
