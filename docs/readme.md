# Modélisation de la base de données — BrioCampus

> Présentation des neuf tables du projet, de leurs responsabilités et de leurs relations.

## Sommaire

1. [Objectif](#objectif)
2. [Rôles](#1-rôles)
3. [Utilisateurs](#2-utilisateurs)
4. [Universités](#3-universités)
5. [Palmarès](#4-palmarès)
6. [Catégories](#5-catégories)
7. [Tickets](#6-tickets)
8. [Paiements](#7-paiements)
9. [Journaux d’activité](#8-journaux-dactivité)
10. [Notifications](#9-notifications)
11. [Relations principales](#relations-principales)
12. [Clés étrangères](#clés-étrangères)
13. [Principes de modélisation](#principes-de-modélisation)
14. [Structure finale](#structure-finale)

## Objectif

La base de données de BrioCampus doit permettre de gérer les utilisateurs, leurs rôles, les universités, les palmarès, les tickets, les catégories, les paiements, les notifications et les journaux d’activité.

Le modèle reprend les tables identifiées dans le modèle initial :

`roles` · `users` · `universities` · `palmares` · `categories` · `tickets` · `payments` · `logs` · `notifications`

L’objectif est d’éviter de stocker plusieurs fois la même information et de représenter correctement les relations entre les différentes entités.

## 1. Rôles

**Table :** `roles`

| Colonne | Description |
| --- | --- |
| `id` | Identifiant du rôle |
| `name` | Nom du rôle |

Cette table contient les différents rôles pouvant être attribués aux utilisateurs.

Exemples :

| `id` | `name` |
| ---: | --- |
| 1 | `ADMIN` |
| 2 | `RH` |
| 3 | `USER` |

La table `users` référence le rôle par `users.role_id → roles.id`. Un rôle peut ainsi être réutilisé par plusieurs utilisateurs, sans répéter son nom dans chaque enregistrement.

## 2. Utilisateurs

**Table :** `users`

| Colonne | Description |
| --- | --- |
| `id` | Identifiant de l’utilisateur |
| `role_id` | Rôle de l’utilisateur (`roles.id`) |
| `university_id` | Université de l’utilisateur (`universities.id`) |
| `first_name` | Prénom |
| `last_name` | Nom |
| `email` | Adresse électronique |
| `password` | Mot de passe hashé |
| `matricule` | Matricule académique |
| `phone` | Numéro de téléphone |
| `status` | État du compte |
| `created_at` | Date de création |
| `updated_at` | Date de dernière mise à jour |

Cette table représente les utilisateurs de la plateforme et contient leurs informations personnelles.

- `users.role_id → roles.id`
- `users.university_id → universities.id`

### Pourquoi le matricule est dans `users` ?

Dans le modèle initial, `MATRICUL` apparaissait dans `UNIVERSITE`. Or, un matricule appartient à un étudiant/utilisateur, et non à une université entière.

```text
À éviter : universities.matricule
À utiliser : users.matricule
```

## 3. Universités

**Table :** `universities`

| Colonne | Description |
| --- | --- |
| `id` | Identifiant de l’université |
| `name` | Nom de l’université |
| `logo` | Chemin ou URL du logo |
| `address` | Adresse |
| `status` | État de l’université (active ou désactivée, par exemple) |
| `created_at` | Date de création |
| `updated_at` | Date de dernière mise à jour |

Une université peut être associée à plusieurs utilisateurs :

```text
universities 1 ─────── N users
```

## 4. Palmarès

**Table :** `palmares`

| Colonne | Description |
| --- | --- |
| `id` | Identifiant du palmarès |
| `university_id` | Université concernée (`universities.id`) |
| `file_path` | Chemin du fichier |
| `file_name` | Nom du fichier |
| `uploaded_by` | Utilisateur ayant téléversé le fichier (`users.id`) |
| `status` | État du palmarès |
| `uploaded_at` | Date de téléversement |
| `verified_at` | Date de vérification |
| `verified_by` | Utilisateur ayant vérifié le fichier (`users.id`) |

Le palmarès est un document téléversé par une université, puis vérifié par le personnel/RH. Ses informations de fichier sont donc conservées dans une table distincte plutôt que directement dans `universities`.

Les statuts possibles comprennent `PENDING`, `VERIFIED` et `REJECTED`.

Une université peut avoir plusieurs palmarès au fil du temps :

```text
Université X
├── Palmarès 2024
├── Palmarès 2025
└── Palmarès 2026

universities 1 ─────── N palmares
```

## 5. Catégories

**Table :** `categories`

| Colonne | Description |
| --- | --- |
| `id` | Identifiant de la catégorie |
| `name` | Nom de la catégorie |
| `description` | Description |
| `status` | État de la catégorie |
| `created_at` | Date de création |
| `updated_at` | Date de dernière mise à jour |

Les catégories permettent de classer les tickets ou les opérations selon leur nature.

Exemples : inscription, paiement, problème de compte, document et assistance.

Une catégorie étant réutilisable, les tickets stockent `category_id` plutôt que de répéter le nom de la catégorie : `tickets.category_id → categories.id`. Cela évite les répétitions et permet de modifier un nom à un seul endroit.

## 6. Tickets

**Table :** `tickets`

| Colonne | Description |
| --- | --- |
| `id` | Identifiant du ticket |
| `user_id` | Utilisateur ayant créé le ticket (`users.id`) |
| `category_id` | Catégorie du ticket (`categories.id`) |
| `subject` | Titre du ticket |
| `description` | Détails du problème |
| `status` | État du ticket |
| `priority` | Niveau de priorité |
| `created_at` | Date de création |
| `updated_at` | Date de dernière mise à jour |
| `closed_at` | Date de fermeture |

Un ticket représente une demande ou un problème signalé par un utilisateur.

**Statuts possibles :** `OPEN`, `IN_PROGRESS`, `RESOLVED`, `CLOSED`.

**Priorités possibles :** `LOW`, `MEDIUM`, `HIGH`, `URGENT`.

### Pourquoi ne pas stocker `NB_TICKETS` ?

Le nombre de tickets d’un utilisateur est calculable à partir des lignes de la table `tickets` avec `COUNT(*)`. Par exemple, si l’utilisateur A possède 12 tickets, le nombre 12 peut être obtenu à partir des données existantes. Le stocker séparément créerait un risque d’incohérence.

## 7. Paiements

**Table :** `payments`

| Colonne | Description |
| --- | --- |
| `id` | Identifiant du paiement |
| `user_id` | Payeur (`users.id`) |
| `category_id` | Type de paiement éventuel (`categories.id`) |
| `amount` | Montant payé |
| `reference` | Référence de la transaction |
| `status` | État du paiement |
| `payment_date` | Date effective du paiement |
| `created_at` | Date de création de l’enregistrement |

Les statuts possibles sont `PENDING`, `PAID`, `FAILED` et `CANCELLED`.

Même si une transaction possède une référence, il est préférable de conserver une clé primaire interne (`payments.id`). La référence peut également être soumise à une contrainte `UNIQUE`.

## 8. Journaux d’activité

**Table :** `logs`

| Colonne | Description |
| --- | --- |
| `id` | Identifiant du journal |
| `user_id` | Utilisateur ayant effectué l’action (`users.id`) |
| `action` | Type d’action |
| `description` | Détail de l’action |
| `ip_address` | Adresse IP associée à l’action |
| `user_agent` | Informations relatives au navigateur/client |
| `created_at` | Date de l’action |

Cette table conserve une trace des actions importantes effectuées sur la plateforme.

Exemples d’actions : `LOGIN`, `LOGOUT`, `CREATE`, `UPDATE`, `DELETE`, `UPLOAD`, `VERIFY` et `PAYMENT`.

Exemple d’entrée :

```text
USER        : 15
ACTION      : LOGIN
IP          : 192.168.1.20
DATE        : 2026-09-29 14:30
```

Les logs sont distincts des données métier : un ticket représente une activité métier, tandis qu’un log constitue une trace d’activité du système.

## 9. Notifications

**Table :** `notifications`

| Colonne | Description |
| --- | --- |
| `id` | Identifiant de la notification |
| `user_id` | Destinataire (`users.id`) |
| `title` | Titre de la notification |
| `message` | Contenu de la notification |
| `type` | Type de notification |
| `is_read` | Indique si elle a été consultée |
| `created_at` | Date de création |
| `read_at` | Date de lecture |

Les types possibles sont `INFO`, `SUCCESS`, `WARNING` et `ERROR`.

`is_read` indique rapidement si la notification a été lue, tandis que `read_at` conserve la date de lecture.

| État | `is_read` | `read_at` |
| --- | --- | --- |
| Non lue | `false` | `NULL` |
| Lue | `true` | Date de lecture, par exemple `2026-09-29 15:30` |

## Relations principales

```text
ROLES
  │ 1
  │
  │ N
USERS
  ├── N : 1 ─── UNIVERSITIES
  ├── N : 1 ─── CATEGORIES
  ├── 1 : N ─── TICKETS
  ├── 1 : N ─── PAYMENTS
  ├── 1 : N ─── LOGS
  └── 1 : N ─── NOTIFICATIONS

UNIVERSITIES
  └── 1 : N ─── PALMARES

CATEGORIES
  ├── 1 : N ─── TICKETS
  └── 1 : N ─── PAYMENTS
```

## Clés étrangères

| Table | Clé étrangère | Référence |
| --- | --- | --- |
| `users` | `role_id` | `roles.id` |
| `users` | `university_id` | `universities.id` |
| `palmares` | `university_id` | `universities.id` |
| `palmares` | `uploaded_by` | `users.id` |
| `palmares` | `verified_by` | `users.id` |
| `tickets` | `user_id` | `users.id` |
| `tickets` | `category_id` | `categories.id` |
| `payments` | `user_id` | `users.id` |
| `payments` | `category_id` | `categories.id` |
| `logs` | `user_id` | `users.id` |
| `notifications` | `user_id` | `users.id` |

## Principes de modélisation

Chaque table possède une responsabilité claire :

| Table | Responsabilité |
| --- | --- |
| `users` | Informations relatives aux personnes |
| `roles` | Rôles disponibles |
| `universities` | Informations relatives aux universités |
| `palmares` | Documents/palmarès associés aux universités |
| `categories` | Catégories réutilisables |
| `tickets` | Demandes des utilisateurs |
| `payments` | Transactions |
| `logs` | Traces des actions effectuées |
| `notifications` | Messages système destinés aux utilisateurs |

Cette séparation évite de placer dans `users` ou `universities` des informations qui appartiennent à d’autres entités.

### Éviter les données calculables

Le nombre de tickets ne devrait pas être stocké dans `users`, car il peut être calculé à partir de `tickets.user_id` :

```text
À éviter :
users
-------------------------
id | name | nb_tickets
```

De même, le matricule appartient à l’utilisateur/étudiant, et non à l’université :

```text
À éviter :
universities
-------------------------
id | name | matricule
```

> **Principe :** une information doit être stockée dans la table de l’entité à laquelle elle appartient réellement.

## Structure finale

```text
roles
├── id
└── name

users
├── id
├── role_id FK
├── university_id FK
├── first_name
├── last_name
├── email
├── password
├── matricule
├── phone
├── status
├── created_at
└── updated_at

universities
├── id
├── name
├── logo
├── address
├── status
├── created_at
└── updated_at

palmares
├── id
├── university_id FK
├── file_path
├── file_name
├── uploaded_by FK
├── status
├── uploaded_at
├── verified_at
└── verified_by FK

categories
├── id
├── name
├── description
├── status
├── created_at
└── updated_at

tickets
├── id
├── user_id FK
├── category_id FK
├── subject
├── description
├── status
├── priority
├── created_at
├── updated_at
└── closed_at

payments
├── id
├── user_id FK
├── category_id FK
├── amount
├── reference
├── status
├── payment_date
└── created_at

logs
├── id
├── user_id FK
├── action
├── description
├── ip_address
├── user_agent
└── created_at

notifications
├── id
├── user_id FK
├── title
├── message
├── type
├── is_read
├── created_at
└── read_at
```

## Conclusion

Cette structure conserve les neuf tables prévues dans le projet, tout en rendant leurs relations explicites et en évitant les duplications inutiles.

Les principaux choix par rapport au modèle initial :

- `matricule` est associé à `users` ;
- `nb_tickets` est supprimé, car calculable ;
- `palmares` possède ses propres informations de fichier et de vérification ;
- `tickets` référence `users` et `categories` ;
- `payments` référence `users` ;
- `logs` référence `users` ;
- `notifications` référence `users` ;
- les clés étrangères sont explicitement définies ;
- chaque table possède une responsabilité bien définie.
