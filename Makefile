DOCKER_COMPOSE = docker compose
DOCKER_COMPOSE_PROD = docker compose -f compose.prod.yaml --env-file .env.prod
PHP_CONTAINER = php
APP_URL = http://localhost:8080
TEST_DATABASE_URL = sqlite:///%kernel.project_dir%/var/test/test.db
BACKUP_DIR = backups
BACKUP_FILE ?= $(BACKUP_DIR)/runtracker-$$(date +%Y%m%d-%H%M%S).db

.PHONY: help build up down restart logs ps shell sh composer sf console install db cache-clear lint lint-fix phpstan deptrac test qa prod-build prod-up prod-down prod-logs prod-db-backup prod-db-restore

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Build Docker images
	$(DOCKER_COMPOSE) build

up: ## Start containers in background
	$(DOCKER_COMPOSE) up -d
	@echo ""
	@echo "  Application: $(APP_URL)"
	@echo ""

down: ## Stop and remove containers
	$(DOCKER_COMPOSE) down --remove-orphans

restart: down up ## Restart containers

logs: ## Show container logs (follow mode)
	$(DOCKER_COMPOSE) logs -f --tail=100

ps: ## Show running containers
	$(DOCKER_COMPOSE) ps

shell: ## Open shell in PHP container
	$(DOCKER_COMPOSE) exec -u app $(PHP_CONTAINER) sh

sh: shell ## Alias for shell

composer: ## Run Composer command (usage: make composer args="require symfony/orm-pack")
	$(DOCKER_COMPOSE) exec -u app $(PHP_CONTAINER) composer $(args)

sf: ## Run Symfony console command (usage: make sf args="make:controller")
	$(DOCKER_COMPOSE) exec -u app $(PHP_CONTAINER) php bin/console $(args)

console: sf ## Alias for sf

install: ## Install Composer dependencies
	$(DOCKER_COMPOSE) exec -u app $(PHP_CONTAINER) composer install

db: ## Open SQLite database shell
	$(DOCKER_COMPOSE) exec -u app $(PHP_CONTAINER) sh -lc 'sqlite3 var/data/app.db'

cache-clear: ## Clear Symfony cache
	$(DOCKER_COMPOSE) exec -u app $(PHP_CONTAINER) php bin/console cache:clear

## ---- Quality & Testing ----

lint: ## Run PHP-CS-Fixer in dry-run mode
	$(DOCKER_COMPOSE) exec -u app $(PHP_CONTAINER) sh -lc 'test -x vendor/bin/php-cs-fixer && vendor/bin/php-cs-fixer fix --dry-run --diff || php bin/console lint:container'

lint-fix: ## Fix code style with PHP-CS-Fixer
	$(DOCKER_COMPOSE) exec -u app $(PHP_CONTAINER) sh -lc 'test -x vendor/bin/php-cs-fixer && vendor/bin/php-cs-fixer fix || echo "php-cs-fixer is not installed"'

phpstan: ## Run PHPStan static analysis
	$(DOCKER_COMPOSE) exec -u app $(PHP_CONTAINER) sh -lc 'test -x vendor/bin/phpstan && vendor/bin/phpstan analyse || echo "phpstan is not installed"'

deptrac: ## Run architecture dependency checks (Deptrac)
	$(DOCKER_COMPOSE) exec -u app $(PHP_CONTAINER) sh -lc 'test -x vendor/bin/deptrac && vendor/bin/deptrac analyse || echo "deptrac is not installed"'

test: ## Run PHPUnit tests
	$(DOCKER_COMPOSE) exec -u app -e APP_ENV=test -e APP_DEBUG=1 -e DATABASE_URL='$(TEST_DATABASE_URL)' $(PHP_CONTAINER) sh -lc 'test -x bin/phpunit && bin/phpunit || php bin/console lint:container'

qa: lint phpstan deptrac test ## Run all quality checks (lint + phpstan + deptrac + tests)

## ---- Production ----

prod-build: ## Build production Docker images
	$(DOCKER_COMPOSE_PROD) build

prod-up: ## Start production containers in background
	$(DOCKER_COMPOSE_PROD) up -d

prod-down: ## Stop and remove production containers
	$(DOCKER_COMPOSE_PROD) down --remove-orphans

prod-logs: ## Show production container logs (follow mode)
	$(DOCKER_COMPOSE_PROD) logs -f --tail=100

prod-db-backup: ## Copy production SQLite database to BACKUP_FILE
	mkdir -p $(BACKUP_DIR)
	$(DOCKER_COMPOSE_PROD) exec -T -u app $(PHP_CONTAINER) sqlite3 var/data/app.db ".backup '/tmp/runtracker-backup.db'"
	$(DOCKER_COMPOSE_PROD) cp $(PHP_CONTAINER):/tmp/runtracker-backup.db $(BACKUP_FILE)
	$(DOCKER_COMPOSE_PROD) exec -T -u app $(PHP_CONTAINER) rm -f /tmp/runtracker-backup.db

prod-db-restore: ## Restore production SQLite database from BACKUP_FILE
	test -n "$(BACKUP_FILE)"
	test -f "$(BACKUP_FILE)"
	$(DOCKER_COMPOSE_PROD) cp $(BACKUP_FILE) $(PHP_CONTAINER):/tmp/runtracker-restore.db
	$(DOCKER_COMPOSE_PROD) exec -T -u app $(PHP_CONTAINER) sh -lc 'sqlite3 /tmp/runtracker-restore.db "PRAGMA quick_check;" | grep -qx ok'
	$(DOCKER_COMPOSE_PROD) exec -T -u app $(PHP_CONTAINER) sh -lc 'sqlite3 var/data/app.db ".restore /tmp/runtracker-restore.db" && rm -f /tmp/runtracker-restore.db'
