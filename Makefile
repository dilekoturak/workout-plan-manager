# ============================================================
# Makefile — Workout Plan Manager
# All commands run against the Docker Compose stack.
# Usage: make <target>
# ============================================================

DC = docker compose

# ─────────────────────────────────────────────
# Stack lifecycle
# ─────────────────────────────────────────────

## Build and start all containers in the background
up:
	$(DC) up -d --build

## Stop and remove all containers
down:
	$(DC) down

## Stop containers without removing them
stop:
	$(DC) stop

## Restart all containers
restart:
	$(DC) restart

## Show running containers
ps:
	$(DC) ps

# ─────────────────────────────────────────────
# Logs
# ─────────────────────────────────────────────

## Stream logs for all containers
logs:
	$(DC) logs -f

## Stream logs for the app (PHP-FPM) container only
logs-app:
	$(DC) logs -f app

## Stream logs for the database container only
logs-db:
	$(DC) logs -f database

# ─────────────────────────────────────────────
# Shell access
# ─────────────────────────────────────────────

## Open a shell inside the PHP app container
bash:
	$(DC) exec app sh

## Open a psql session inside the database container
psql:
	$(DC) exec database psql -U $${POSTGRES_USER} -d $${POSTGRES_DB}

# ─────────────────────────────────────────────
# Symfony / Doctrine
# ─────────────────────────────────────────────

## Run all pending database migrations
migrate:
	$(DC) exec app php bin/console doctrine:migrations:migrate --no-interaction

## Show migration status
migration-status:
	$(DC) exec app php bin/console doctrine:migrations:status

## Generate a new blank migration file
migration-diff:
	$(DC) exec app php bin/console doctrine:migrations:diff

## Clear Symfony cache
cache-clear:
	$(DC) exec app php bin/console cache:clear

## Run Symfony console command — usage: make console CMD="debug:router"
console:
	$(DC) exec app php bin/console $(CMD)

# ─────────────────────────────────────────────
# Test database
# ─────────────────────────────────────────────

## Create the test database schema (run once after first `make up`)
test-db-create:
	$(DC) exec app php bin/console doctrine:database:create --env=test --if-not-exists
	$(DC) exec app php bin/console doctrine:schema:create --env=test

## Drop and recreate the test database schema (useful after entity changes)
test-db-reset:
	$(DC) exec app php bin/console doctrine:schema:drop --env=test --force --full-database
	$(DC) exec app php bin/console doctrine:schema:create --env=test

# ─────────────────────────────────────────────
# Code quality
# ─────────────────────────────────────────────

## Install Composer dependencies inside the container
composer-install:
	$(DC) exec app composer install

## Run PHPUnit tests
test:
	$(DC) exec app php bin/phpunit --testdox

## Run PHPUnit tests for a specific file or filter — usage: make test-filter FILTER="UserApi"
test-filter:
	$(DC) exec app php bin/phpunit --testdox --filter=$(FILTER)

.PHONY: up down stop restart ps logs logs-app logs-db bash psql migrate migration-status migration-diff cache-clear console composer-install test test-filter test-db-create test-db-reset
