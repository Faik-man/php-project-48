install:
	composer install

validate:
	composer validate

lint:
	composer exec --verbose phpcs -- src bin
	composer exec --verbose phpstan

test:
	composer exec --verbose phpunit tests

test-coverage:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-clover=build/logs/clover.xml

test-coverage-text:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-text
