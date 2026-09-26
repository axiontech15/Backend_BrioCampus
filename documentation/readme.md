# Briocampus - guide technique

Briocampus est une API en cours de développement pour la vérification des diplômes. Ce document décrit l'état technique du dépôt et les commandes nécessaires pour lancer le backend localement.

## État actuel

| Composant | État dans ce dépôt |
| --- | --- |
| API | FastAPI, avec une route racine de démonstration (`GET /`) |
| Python | Version 3.12 ou supérieure |
| Gestion des dépendances | `uv`, dépendances déclarées dans `pyproject.toml` |
| Interface React | Pas encore présente dans ce dépôt |
| Base de données MySQL | Pas encore configurée |
| Cache Redis | Pas encore configuré |

Les fonctionnalités métier et les travaux prévus sont décrits dans le [README fonctionnel](README.md) et le [suivi des tâches](todo.md).

## Structure du dépôt

```text
.
├── documentation/
│   ├── README.md           # Présentation fonctionnelle
│   ├── readme.md           # Ce guide technique
│   └── todo.md             # Tâches et état d'avancement
├── public/
│   └── main.py             # Point d'entrée de l'API FastAPI
└── pyproject.toml          # Métadonnées et dépendances Python
```

## Prérequis

- Python 3.12 ou une version ultérieure
- [`uv`](https://docs.astral.sh/uv/)

## Installation

Depuis la racine du dépôt, synchronisez l'environnement et les dépendances :

```bash
uv sync
```

La dépendance applicative déclarée actuellement est `fastapi[standard]`. Elle fournit notamment le serveur ASGI utilisé pour exécuter l'API.

## Lancement en développement

Depuis la racine du dépôt :

```bash
uv run uvicorn --app-dir public main:app --reload --host 127.0.0.1 --port 8080
```

- `--app-dir public` indique où se trouve le module `main`.
- `main:app` désigne l'objet FastAPI `app` défini dans `public/main.py`.
- `--reload` redémarre le serveur lorsqu'un fichier change; à réserver au développement.
- `--host 127.0.0.1` limite l'accès à la machine locale. Pour rendre le serveur accessible depuis le réseau, remplacez cette valeur par `0.0.0.0` et configurez votre environnement en conséquence.
- `--port 8080` choisit le port d'écoute; il peut être remplacé si le port est déjà utilisé.

Ne redirigez pas les erreurs vers `/dev/null` pendant le développement : les messages du serveur sont utiles pour diagnostiquer les problèmes. La commande ci-dessus fonctionne également sous Windows avec `uv` installé.

## Vérification de l'API

Une fois le serveur démarré, les routes FastAPI de documentation sont accessibles ici :

- Interface Swagger : <http://127.0.0.1:8080/docs>
- Schéma OpenAPI : <http://127.0.0.1:8080/openapi.json>
- Route racine de démonstration : <http://127.0.0.1:8080/>

Pour vérifier la route racine depuis un terminal :

```bash
curl http://127.0.0.1:8080/
```

La route racine est actuellement un placeholder; elle ne représente pas encore un parcours métier de vérification de diplôme.