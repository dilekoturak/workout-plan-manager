# Database Diagram

## Current Schema

```
┌─────────────────────────────┐          ┌─────────────────────────────┐
│        workout_plans        │          │           users             │
├─────────────────────────────┤          ├─────────────────────────────┤
│ id          UUID  PK        │          │ id          UUID  PK        │
│ name        VARCHAR(255)    │          │ first_name  VARCHAR(255)    │
│ created_at  TIMESTAMP       │          │ last_name   VARCHAR(255)    │
│ updated_at  TIMESTAMP       │          │ email       VARCHAR(255) UQ │
│                             │          │ created_at  TIMESTAMP       │
│                             │          │ updated_at  TIMESTAMP       │
└────────┬──────────┬──────────┘          └──────────────────┬──────────┘
         │ 1        │ 1                                      │ 1
         │          │                                        │
         │ N        │ N                                      │ N
         │          └──────────────────┐          ┌──────────┘
         │                             ▼          ▼
         │                    ┌─────────────────────────┐
         │                    │    user_workout_plans    │
         │                    ├─────────────────────────┤
         │                    │ id              UUID  PK │
         │                    │ user_id         UUID  FK │
         │                    │ workout_plan_id UUID  FK │
         │                    │ assigned_at  TIMESTAMP   │
         │                    │ UNIQUE (user_id,         │
         │                    │         workout_plan_id) │
         │                    └─────────────────────────┘
         │
         ▼
┌─────────────────────────────┐
│        workout_days         │
├─────────────────────────────┤
│ id              UUID  PK    │
│ name            VARCHAR(255)│
│ workout_plan_id UUID  FK    │
└──────────────┬──────────────┘
               │ 1
               │
               │ N
               ▼
┌─────────────────────────────┐
│          exercises          │
├─────────────────────────────┤
│ id             UUID  PK     │
│ name           VARCHAR(255) │
│ sets           INT  NULL    │
│ reps           INT  NULL    │
│ notes          TEXT NULL    │
│ workout_day_id UUID  FK     │
└─────────────────────────────┘
```

## Cleaner View

```
users
  id           UUID  PK
  first_name   VARCHAR(255)
  last_name    VARCHAR(255)
  email        VARCHAR(255)  UNIQUE
  created_at   TIMESTAMP
  updated_at   TIMESTAMP

workout_plans
  id           UUID  PK
  name         VARCHAR(255)
  created_at   TIMESTAMP
  updated_at   TIMESTAMP

workout_days
  id              UUID  PK
  name            VARCHAR(255)
  workout_plan_id UUID  FK → workout_plans.id  ON DELETE CASCADE

exercises
  id              UUID  PK
  name            VARCHAR(255)
  sets            INT           NULL
  reps            INT           NULL
  notes           TEXT          NULL
  workout_day_id  UUID  FK → workout_days.id  ON DELETE CASCADE

user_workout_plans
  id              UUID  PK
  user_id         UUID  FK → users.id         ON DELETE CASCADE
  workout_plan_id UUID  FK → workout_plans.id ON DELETE CASCADE
  assigned_at     TIMESTAMP
  UNIQUE (user_id, workout_plan_id)
```

## Entity Relationships

| Entity         | Relation     | Entity           | Via                  |
|----------------|--------------|------------------|----------------------|
| WorkoutPlan    | OneToMany    | WorkoutDay       | workout_plan_id      |
| WorkoutDay     | OneToMany    | Exercise         | workout_day_id       |
| User           | ManyToMany*  | WorkoutPlan      | user_workout_plans   |

> *Modelled as two ManyToOne relationships through the `user_workout_plans` join table, which also holds `assigned_at` metadata.

## Cascade Rules

| Relationship                      | On Delete              |
|-----------------------------------|------------------------|
| workout_plans → workout_days      | CASCADE (ORM + DB)     |
| workout_days  → exercises         | CASCADE (ORM + DB)     |
| users         → user_workout_plans| CASCADE (ORM + DB)     |
| workout_plans → user_workout_plans| CASCADE (ORM + DB)     |
