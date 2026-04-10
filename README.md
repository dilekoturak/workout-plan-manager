# Workout Plan Manager

A full-stack workout plan management application built with **Symfony 7** (REST API backend) and **Vue 3** (frontend).

---

## Architecture Overview

### Principles

- **SOLID** — Single responsibility across all layers; interfaces used for extensibility.
- **KISS** — No unnecessary abstractions. Code is readable by any developer familiar with modern web frameworks (e.g., .NET, Laravel).
- **SoC (Separation of Concerns)** — HTTP handling, business logic, and data access are strictly separated.
- **No over-engineering** — No CQRS, no event sourcing, no hexagonal architecture. Clean and pragmatic layering.

---

## Tech Stack

| Layer      | Technology                          |
|------------|-------------------------------------|
| Backend    | PHP 8.4, Symfony 7.2                |
| Database   | PostgreSQL 16                       |
| ORM        | Doctrine ORM 3 + Migrations         |
| Validation | Symfony Validator (PHP Attributes)  |
| Serializer | Symfony Serializer                  |
| CORS       | NelmioCorsBundle                    |
| Frontend   | Vue 3 *(coming soon)*               |

---

## Backend Structure

```
backend/
└── src/
    ├── Controller/     # HTTP layer — receives requests, returns responses, delegates to Services
    ├── Service/        # Business logic layer — all domain rules live here
    ├── Entity/         # Doctrine ORM entities — represent database tables (domain models)
    ├── Repository/     # Data access layer — database queries via Doctrine
    ├── DTO/            # Data Transfer Objects — shape incoming requests and outgoing responses
    └── Exception/      # Custom domain exceptions — meaningful error handling
```

### Layer Responsibilities

- **Controller** → Validates HTTP input, calls the appropriate Service, returns a JSON response. Contains **zero** business logic.
- **Service** → Executes business logic, uses Repositories for data access, throws domain Exceptions.
- **Entity** → Plain PHP class annotated with Doctrine attributes. Represents a database row. No business logic.
- **Repository** → Extends Doctrine's `ServiceEntityRepository`. Provides typed query methods (`findByUserId`, etc.).
- **DTO** → Simple objects used to transfer data between layers. Keeps Entities decoupled from the HTTP layer.
- **Exception** → Domain-specific exceptions (e.g., `WorkoutPlanNotFoundException`) for clean error handling.

---

## Getting Started

### Prerequisites

- PHP 8.4+
- Composer 2+
- PostgreSQL 16+
- Symfony CLI

### Setup

```bash
cd backend
composer install
```

Copy `.env` and update the database credentials:

```bash
cp .env .env.local
# Edit DATABASE_URL in .env.local
```

Create the database and run migrations:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Start the development server:

```bash
symfony server:start
```

---

## Development Log

All development steps are tracked in [`instructions.md`](./instructions.md).
