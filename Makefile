.PHONY: help install start stop composer db fixtures store store-drop index

DOCKER_COMPOSE = docker compose
PHP_CONT = $(DOCKER_COMPOSE) exec php
CONSOLE = $(PHP_CONT) bin/console

help: ## Show this help message
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

##@ General Commands
install: ## Full installation (Docker, Composer, Database, Fixtures)
	$(DOCKER_COMPOSE) build
	$(DOCKER_COMPOSE) up -d
	$(PHP_CONT) composer install
	$(MAKE) db
	$(MAKE) fixtures

start: ## Start containers (with --wait)
	$(DOCKER_COMPOSE) up --wait

stop: ## Stop containers
	$(DOCKER_COMPOSE) stop

##@ Development Commands
composer: ## Install composer dependencies
	$(PHP_CONT) composer install

db: ## Setup database (Create & Migrate)
	$(CONSOLE) doctrine:database:create --if-not-exists
	$(CONSOLE) doctrine:migrations:migrate --no-interaction

fixtures: ## Load database fixtures
	$(CONSOLE) doctrine:fixtures:load --no-interaction

##@ AI Store Commands
store: ## Setup an AI store (args: <storename>)
	$(CONSOLE) ai:store:setup $(filter-out $@,$(MAKECMDGOALS))

store-drop: ## Drop an AI store (args: <storename>)
	$(CONSOLE) ai:store:drop $(filter-out $@,$(MAKECMDGOALS))

index: ## Index an AI store (args: <storename>)
	$(CONSOLE) ai:store:index $(filter-out $@,$(MAKECMDGOALS))

# Ignore undefined targets for store/index arguments
%:
	@:
