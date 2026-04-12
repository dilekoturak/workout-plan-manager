# ============================================================
# Makefile — Workout Plan Manager
# All commands run against the Docker Compose stack.
# Usage: make <target>
# ============================================================

DC = docker compose

# ─────────────────────────────────────────────
# Stack lifecycle
# ─────────────────────────────────────────────

up: ## Build and start all containers
	$(DC) up -d --build

down: ## Stop and remove all containers
	$(DC) down

ps: ## Show running containers
	$(DC) ps

# ─────────────────────────────────────────────
# Logs
# ─────────────────────────────────────────────

logs: ## Stream logs for all containers
	$(DC) logs -f

logs-app: ## Stream logs for the PHP app container only
	$(DC) logs -f app

# ─────────────────────────────────────────────
# Shell access
# ─────────────────────────────────────────────

bash: ## Open a shell inside the PHP container
	$(DC) exec app sh

# ─────────────────────────────────────────────
# Symfony / Doctrine
# ─────────────────────────────────────────────

migrate: ## Run all pending database migrations
	$(DC) exec app php bin/console doctrine:migrations:migrate --no-interaction

fixtures: ## Load dev seed data (Alice, Bob, Carol + 3 plans)
	$(DC) exec app php bin/console doctrine:fixtures:load --no-interaction

setup: ## Run migrations and load seed data
	$(DC) exec app php bin/console doctrine:migrations:migrate --no-interaction
	$(DC) exec app php bin/console doctrine:fixtures:load --no-interaction

cache-clear: ## Clear Symfony cache
	$(DC) exec app php bin/console cache:clear

# ─────────────────────────────────────────────
# Tests
# ─────────────────────────────────────────────

test: ## Run all PHPUnit tests
	$(DC) exec app php bin/phpunit --testdox

test-filter: ## Run tests matching a filter — usage: make test-filter FILTER="UserApi"
	$(DC) exec app php bin/phpunit --testdox --filter=$(FILTER)

.PHONY: up down ps logs logs-app bash migrate fixtures setup cache-clear test test-filter
