.PONY: i t it

COMPOSER = $(shell which composer)
PHPUNIT = vendor/bin/phpunit
PHPCS = vendor/bin/php-cs-fixer
PHP = $(shell which php)

it: i t

i:
	$(COMPOSER) install -o -a

t:
	$(PHPUNIT)
	$(PHPCS) fix -v
	$(PHP) vendor/bin/phpstan analyze -l 3 src
	$(PHP) vendor/bin/phpstan analyze -l 1 tests
