install:
	composer install

validate:
	composer validate

lint:
	composer exec --verbose phpcs -- src bin
	composer exec --verbose phpstan

test:
	composer exec --verbose phpunit tests
