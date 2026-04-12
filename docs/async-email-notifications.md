# Async Email Notifications

When a user is assigned to a plan, or a plan is updated or deleted, an email is sent to the relevant users. This happens in the **background** so the HTTP response is not delayed.

---

## How It Works

```
HTTP request comes in
        │
        ▼
WorkoutPlanService
        │
        │  dispatches a message to the queue
        ▼
    RabbitMQ
        │
        │  worker picks up the message
        ▼
    Handler → sends email → Mailtrap
```

The API responds immediately. Email sending is handled by the worker in the background.

---

## When Is an Email Sent?

| Event | Recipient |
|---|---|
| User assigned to a plan | The assigned user |
| Plan updated | All users assigned to that plan |
| Plan deleted | All users assigned to that plan |

---

## Messages & Handlers

Each event has a **Message** (what happened?) and a **Handler** (what to do?).

**UserAssigned** → carries userId + planId → handler fetches data from DB and sends email

**PlanModified** → carries planId → handler fetches all assigned users and sends each an email

**PlanDeleted** → carries plan name + email list directly (the plan is already deleted from DB by the time the worker runs, so the data is embedded in the message beforehand)

---

## Testing

```bash
# Watch worker logs
docker compose logs worker -f

# Create a user
curl -X POST http://localhost:8080/api/users \
  -H "Content-Type: application/json" \
  -d '{"firstName":"Ali","lastName":"Yilmaz","email":"ali@example.com"}'

# Create a plan
curl -X POST http://localhost:8080/api/workout-plans \
  -H "Content-Type: application/json" \
  -d '{"name":"Full Body","days":[{"name":"Day 1","exercises":[{"name":"Squat","sets":4,"reps":8}]}]}'

# Assign user to plan (replace with real UUIDs)
curl -X POST http://localhost:8080/api/workout-plans/{PLAN_ID}/assign/{USER_ID}
```

Check the email at [mailtrap.io](https://mailtrap.io). RabbitMQ management UI: `http://localhost:15672` (guest / guest)
