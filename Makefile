.PHONY: help install start stop composer db fixtures db-full reset test store store-drop index cache-clear symfony

DOCKER_COMPOSE = docker compose
PHP_CONT = $(DOCKER_COMPOSE) exec php
CONSOLE = $(PHP_CONT) bin/console

help: ## Show this help message
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

##@ Docker
start: ## Start containers (with --wait)
	$(DOCKER_COMPOSE) up --wait

stop: ## Stop containers
	$(DOCKER_COMPOSE) stop

install: ## Full installation (Docker, Composer, Database, Fixtures)
	$(DOCKER_COMPOSE) build
	$(MAKE) start
	$(MAKE) composer
	$(MAKE) db
	$(MAKE) fixtures

##@ Database
db: ## Create database and run migrations
	$(CONSOLE) doctrine:database:create --if-not-exists
	$(CONSOLE) doctrine:migrations:migrate --no-interaction

fixtures: ## Load database fixtures
	$(CONSOLE) foundry:load --no-interaction

db-full: ## Create DB, run migrations, load fixtures and set up AI stores
	$(MAKE) db
	$(MAKE) fixtures
	$(CONSOLE) ai:message-store:setup ai.message_store.cache.messages
	$(CONSOLE) ai:store:setup ai.store.postgres.posts
	$(CONSOLE) ai:store:setup ai.store.postgres.tags
	$(CONSOLE) ai:store:index posts
	$(CONSOLE) ai:store:index tags

reset: ## Drop and recreate the database, run db-full, then clear cache
	$(CONSOLE) doctrine:database:drop --force
	$(MAKE) db-full
	$(MAKE) cache-clear

##@ AI Store
store: ## Setup an AI store (args: <storename>)
	$(CONSOLE) ai:store:setup $(filter-out $@,$(MAKECMDGOALS))

store-drop: ## Drop an AI store (args: <storename>)
	$(CONSOLE) ai:store:drop $(filter-out $@,$(MAKECMDGOALS))

index: ## Index an AI store (args: <storename>)
	$(CONSOLE) ai:store:index $(filter-out $@,$(MAKECMDGOALS))

##@ Development
composer: ## Install composer dependencies
	$(PHP_CONT) composer install

test: ## Run PHPUnit tests
	$(PHP_CONT) bin/phpunit --testdox

cache-clear: ## Clear Symfony cache + shared cache pools (var/share/dev/pools/app/)
	$(CONSOLE) c:c
	rm -rf var/share/dev/pools/app/

##@ Utilities
symfony: ## Run a symfony console command — usage: make symfony cmd="cache:clear"
	$(CONSOLE) $(cmd)

# Ignore undefined targets for store/index arguments
%:
	@:
