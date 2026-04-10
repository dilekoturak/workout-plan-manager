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

| Layer          | Technology                          |
|----------------|-------------------------------------|
| Backend        | PHP 8.4, Symfony 7.2                |
| Database       | PostgreSQL 16                       |
| ORM            | Doctrine ORM 3 + Migrations         |
| Validation     | Symfony Validator (PHP Attributes)  |
| Serializer     | Symfony Serializer                  |
| CORS           | NelmioCorsBundle                    |
| Web Server     | Nginx 1.27 (reverse proxy)          |
| Containerization | Docker, Docker Compose            |
| Frontend       | Vue 3, Vite, TypeScript             |
| State Management | Pinia                             |
| UI Components  | shadcn-vue + Tailwind CSS           |
| HTTP Client    | Axios                               |
| Server State   | TanStack Query (vue-query)          |
| Icons          | lucide-vue-next                     |

---

## Project Structure

```
workout-plan-manager/
├── docker-compose.yml          # Orchestrates all services (app, nginx, database)
├── Makefile                    # Developer shortcuts (make up, make migrate, etc.)
├── .env.example                # Safe template for Docker Compose env vars
├── docker/
│   └── nginx/
│       └── default.conf        # Nginx reverse proxy configuration
├── backend/
│   ├── Dockerfile              # Multi-stage PHP 8.4-fpm image (dev + prod)
│   └── src/
│       ├── Controller/         # HTTP layer
│       ├── Service/            # Business logic layer
│       ├── Entity/             # Doctrine ORM entities
│       ├── Repository/         # Data access layer
│       ├── DTO/                # Request / Response shaping
│       └── Exception/          # Domain exceptions
└── frontend/                   # Vue 3 SPA
    └── src/
        ├── users/              # Users domain (view, types, api, store)
        ├── workout-plans/      # Workout Plans domain (view, detail, types, api, store)
        ├── shared/             # Cross-domain utilities
        │   ├── components/
        │   │   └── layout/     # AppLayout, Navbar
        │   ├── api/            # Axios client
        │   └── types/          # Shared TypeScript types
        ├── components/
        │   └── ui/             # shadcn-vue components (untouched)
        └── router/
```

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

- Docker & Docker Compose

### Setup

```bash
# 1. Clone the repository
git clone https://github.com/dilekoturak/workout-plan-manager.git
cd workout-plan-manager

# 2. Create your local env file from the template
cp .env.example .env
# Edit .env and set your POSTGRES_PASSWORD

# 3. Build and start all containers
make up

# 4. Run database migrations
make migrate
```

The API will be available at **http://localhost:8080**.

### Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

The frontend will be available at **http://localhost:5173**.

### Useful Commands

```bash
make logs          # Stream all container logs
make bash          # Shell into the PHP container
make migrate       # Run pending migrations
make fixtures      # Load dev seed data (Alice, Bob, Carol + 3 plans)
make test          # Run all PHPUnit tests
make cache-clear   # Clear Symfony cache
make down          # Stop all containers
```

### Local Development (without Docker)

```bash
cd backend
cp .env .env.local
# Edit .env.local — set DATABASE_URL to point to your local PostgreSQL

composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony server:start
```

---

## Development Log

All development steps are tracked in [`instructions.md`](./instructions.md).

---

## API Endpoints

### Users
| Method | URL | Description |
|--------|-----|-------------|
| `GET` | `/api/users` | List all users |
| `GET` | `/api/users/{id}` | Get user by ID |
| `POST` | `/api/users` | Create user |
| `PUT` | `/api/users/{id}` | Update user |
| `DELETE` | `/api/users/{id}` | Delete user |

### Workout Plans
| Method | URL | Description |
|--------|-----|-------------|
| `GET` | `/api/workout-plans` | List all plans |
| `GET` | `/api/workout-plans/{id}` | Get plan with days + exercises |
| `POST` | `/api/workout-plans` | Create plan (nested days + exercises) |
| `PUT` | `/api/workout-plans/{id}` | Replace plan |
| `DELETE` | `/api/workout-plans/{id}` | Delete plan |
| `POST` | `/api/workout-plans/{planId}/assign/{userId}` | Assign user to plan |
| `DELETE` | `/api/workout-plans/{planId}/assign/{userId}` | Unassign user |

Swagger UI: **http://localhost:8080/api/doc**
