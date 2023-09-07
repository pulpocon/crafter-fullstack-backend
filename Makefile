CURRENT_DIR:=$(dir $(abspath $(lastword $(MAKEFILE_LIST))))
DOCKER=USER_ID=${shell id -u} GID=${shell id -g} docker
DOCKER_COMPOSE=USER_ID=${shell id -u} GID=${shell id -g} docker compose

.DEFAULT_GOAL := info
.PHONY: default
default: info

.PHONY: info
info:
ifneq ($(OS),Windows_NT)
    @awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z0-9_-]+:.*?##/ { printf "  \033[36m%-27s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)
endif

.PHONY: start
start: ## Start containers
    ${DOCKER_COMPOSE}  -f ${CURRENT_DIR}/docker/docker-compose.yml up -d
.PHONY: stop
stop:## Stop containers
    ${DOCKER_COMPOSE}  -f ${CURRENT_DIR}/docker/docker-compose.yml down --remove-orphans
status:DOCKER_COMMAND=ps ## <U+F308> <U+F05A>  Show containers status
restart: stop start
.PHONY: stats
stats: ## View Docker containers stats
    @${DOCKER} stats

.PHONY: lint-back
lint-back:COMPOSER_COMMAND=lint ## <U+F188> Lints backend code with phpcs

.PHONY: fix-back
fix-back:COMPOSER_COMMAND=fix ## <U+F188> Fixes backend code with phpcs

.PHONY: shell
shell: ## shell in container
    cd ${CURRENT_DIR}/docker/; ${DOCKER_COMPOSE} exec php-fpm bash
