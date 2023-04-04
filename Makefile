static-fix:
	PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix
	vendor/bin/psalm

tests-init:
	bin/console doctrine:database:drop --force -e test
	bin/console doctrine:database:create -e test
	bin/console doctrine:schema:create -e test
	bin/console doctrine:fixture:load -e test --purge-with-truncate -n
.PHONY: tests
tests: tests-init
	vendor/bin/phpunit tests/SymfonyCartography/

.PHONY: tests-application
tests-application:
	vendor/bin/phpunit tests/TestApplication/

.PHONY: tests-all
tests-all: static-fix
	vendor/bin/phpunit tests
	./update-doc.py
