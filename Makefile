
DOCKER_COMPOSE = docker compose -f docker/docker-compose.yml

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build bash

## —— 🎵 🐳 The MBuddy Docker Makefile 🐳 🎵 ———————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
up: ## Starts the containers
	$(DOCKER_COMPOSE) up -d

down: ## Stops the containers
	$(DOCKER_COMPOSE) down

build: ## Build the containers
	$(DOCKER_COMPOSE) build

bash: ## Run bash from PHP container
	$(DOCKER_COMPOSE) exec php bash

bash-node: ## Run bash from Node container
	$(DOCKER_COMPOSE) run --rm -it node bash

ps: ## List all running containers
	$(DOCKER_COMPOSE) ps

logs: ## Show logs
	$(DOCKER_COMPOSE) exec php php bin/log.php

serve: ## Run PHP server
	$(DOCKER_COMPOSE) exec php php -S 0.0.0.0:8080 -t public public/index.php

serve-dbg: ## Run PHP server with XDebug enabled for all input requests
	$(DOCKER_COMPOSE) exec php php -dxdebug.start_with_request=yes -S 0.0.0.0:8080 -t public public/index.php
