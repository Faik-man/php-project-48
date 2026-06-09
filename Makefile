install:
	composer install

validate:
	composer validate

lint:
	composer exec --verbose phpcs -- src bin
