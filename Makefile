
DOCKER_TAG = bveing/mbuddy

DOCKER_RUN = docker run -it --rm --name MBuddy --add-host host.docker.internal:host-gateway -p 8383:8383 -v "$(PWD)/docker/xdebug.ini":/usr/local/etc/php/conf.d/xdebug.ini -v "$(PWD)":/usr/src/myapp -w /usr/src/myapp $(DOCKER_TAG)

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build bash

## —— 🎵 🐳 The MBuddy Docker Makefile 🐳 🎵 ———————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the docker image
	docker build -t $(DOCKER_TAG) -f docker/Dockerfile .

bash: ## Run bash from Docker container
	$(DOCKER_RUN) bash
