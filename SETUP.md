# Setup Guide

Everything you need to run, use, and test this project from scratch.

---

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (includes Docker Compose)
- [Mailtrap](https://mailtrap.io) account (free) — to receive test emails

---

## 1. Clone the Repository

```bash
git clone https://github.com/dilekoturak/workout-plan-manager.git
cd workout-plan-manager
```

---

## 2. Create Your Environment File

```bash
cp .env.example .env
```

Open `.env` and fill in your values:

```dotenv
POSTGRES_DB=workout_plan_manager
POSTGRES_USER=postgres
POSTGRES_PASSWORD=yourpassword          # choose any password

MESSENGER_TRANSPORT_DSN=amqp://guest:guest@rabbitmq:5672/%2f/messages

MAILER_DSN=smtp://USER:PASS@sandbox.smtp.mailtrap.io:2525
```

> **How to get Mailtrap credentials:**
> Log in to [mailtrap.io](https://mailtrap.io) → Email Testing → Inboxes → My Inbox → SMTP Settings → copy Username and Password.

---

## 3. Start All Containers

```bash
make up
```

This builds and starts 7 containers:

| Container | What it does | URL |
|---|---|---|
| `workout_app` | PHP-FPM (Symfony backend) | — |
| `workout_nginx` | Nginx — single entry point (API + frontend) | http://localhost:8080 |
| `workout_frontend` | Vue 3 SPA (nginx, internal only) | — |
| `workout_db` | PostgreSQL 16 | localhost:5432 |
| `workout_db_test` | PostgreSQL 16 (test only) | localhost:5433 |
| `workout_rabbitmq` | RabbitMQ message broker | http://localhost:15672 |
| `workout_worker` | Symfony Messenger consumer | — |

Wait until all containers are healthy:

```bash
make ps
```

All containers should show `Up` or `healthy`.

---

## 4. Run Database Migrations & Load Sample Data

```bash
make setup
```

This runs the migrations (creates all tables) and loads seed data: 3 users (Alice, Bob, Carol) and 3 workout plans.

---

## 5. Verify Everything Is Running

```bash
# API health check
curl http://localhost:8080/api/workout-plans

# Frontend + API (same port)
open http://localhost:8080

# RabbitMQ management UI
open http://localhost:15672
# Login: guest / guest
```

---

## 6. Using the App

Open **http://localhost:8080** in your browser.

**Users page** (`/users`):
- Create, edit, delete users

**Workout Plans page** (`/workout-plans`):
- Create a plan with workout days and exercises
- Click a plan card to open the detail sheet
- **Plan Details tab** — edit the plan name, days, and exercises
- **Assigned Users tab** — assign or unassign users from the plan

---

## 7. Testing Email Notifications

Emails are sent asynchronously via RabbitMQ. To see them:

1. Make sure your `MAILER_DSN` in `.env` points to Mailtrap (see Step 2)
2. Perform one of these actions:
   - Assign a user to a plan → email sent to that user
   - Update a plan that has users → email sent to all assigned users
   - Delete a plan that has users → email sent to all assigned users
3. Check [mailtrap.io](https://mailtrap.io) → My Inbox

To watch the worker process messages in real time:

```bash
docker compose logs worker -f
```

---

## 8. Running Tests

```bash
make test
```

Tests run against an isolated test database (`workout_db_test`) — your development data is never touched.

To run a specific test:

```bash
make test-filter FILTER="UserApi"
```

---

## 9. API Reference

### Users

| Method | URL | Description |
|---|---|---|
| `GET` | `/api/users` | List all users |
| `GET` | `/api/users/{id}` | Get user by ID |
| `POST` | `/api/users` | Create user |
| `PUT` | `/api/users/{id}` | Update user |
| `DELETE` | `/api/users/{id}` | Delete user |

**Create user request body:**
```json
{
  "firstName": "Alice",
  "lastName": "Smith",
  "email": "alice@example.com"
}
```

### Workout Plans

| Method | URL | Description |
|---|---|---|
| `GET` | `/api/workout-plans` | List all plans |
| `GET` | `/api/workout-plans/{id}` | Get plan with days + exercises |
| `POST` | `/api/workout-plans` | Create plan |
| `PUT` | `/api/workout-plans/{id}` | Replace plan |
| `DELETE` | `/api/workout-plans/{id}` | Delete plan |
| `POST` | `/api/workout-plans/{planId}/assign/{userId}` | Assign user to plan |
| `DELETE` | `/api/workout-plans/{planId}/assign/{userId}` | Unassign user |

**Create plan request body:**
```json
{
  "name": "Push Pull Legs",
  "days": [
    {
      "name": "Push Day",
      "exercises": [
        { "name": "Bench Press", "sets": 4, "reps": 8, "notes": null },
        { "name": "Overhead Press", "sets": 3, "reps": 10, "notes": null }
      ]
    },
    {
      "name": "Pull Day",
      "exercises": [
        { "name": "Pull Up", "sets": 4, "reps": 8, "notes": null }
      ]
    }
  ]
}
```

Interactive API docs (Swagger UI): **http://localhost:8080/api/doc**

---

## Useful Commands

```bash
make up               # Start all containers
make down             # Stop and remove all containers
make ps               # Show container status
make logs             # Stream all logs
make logs-app         # Stream PHP app logs only
make bash             # Shell into the PHP container
make setup            # Run migrations + load seed data
make migrate          # Run migrations only
make fixtures         # Load seed data only
make test             # Run all PHPUnit tests
make cache-clear      # Clear Symfony cache
```

---

## Stopping the Project

```bash
make down
```

Your database data is persisted in Docker volumes — it will still be there next time you run `make up`.

To also remove all data:

```bash
docker compose down -v
```
