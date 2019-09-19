#!make

SETUP_TESTSUITE=bash bin/install-wp-tests.sh wordpress_test root '' localhost latest

DEFAULT_GOAL := help
help:
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z0-9_-]+:.*?##/ { printf "  \033[36m%-27s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

.PHONY: build-project
build-project: ## Runs the npm and composer installations, and builds the zip file
	composer install && npm install && npm run build

.PHONY: build-dev
build-dev: ## Runs the npm and composer installations without the zip file
	composer install && npm install && npm run dev

.PHONY: run-dev
run-dev: ## Runs the dev npm script
	npm run dev

.PHONY: run-watch
run-watch: ## Runs the watch npm script
	npm run watch

.PHONY: setup-testsuite
setup-testsuite: ## Set up the default testsuite - install test WordPress and database
	$(shell $(SETUP_TESTSUITE))

.PHONY: clobber
clobber: ## Run pcov clobber script - to use pcov instead of Xdebug
	composer clobber

.PHONY: unclobber
unclobber: ## Run pcov unclobber script - to revert the usage of pcov instead of Xdebug
	composer unclobber

.PHONY: test-unit
test-unit: ## Run unit tests
	composer run-tests-unit

.PHONY: test-unit-coverage
test-unit-coverage: ## Run unit tests with code coverage
	composer run-tests-unit-cov

.PHONY: test-integration
test-integration: ## Run integration tests
	composer run-tests-integ

.PHONY: test-integration-coverage
test-integration-coverage: ## Run integration tests with code coverage
	composer run-tests-integ-cov

.PHONY: check-cs
check-cs: ## Check the code with PHPCS against the coding standards
	composer check-cs

.PHONY: fix-cs
fix-cs: ## Run the PHPCBF command for code beautification according to coding standards
	composer fix-cs
