PHP_CONTAINER=online_store-php-1

build:
	docker compose build
up:
	docker compose up
down:
	docker compose down
symfony:
	docker exec -it $(PHP_CONTAINER) php bin/console $(cmd)

entity:
	make symfony cmd="make:entity"

migrate:
	make symfony cmd="doctrine:migrations:migrate"
migration:
	make symfony cmd="make:migration"
analyse:
	vendor/bin/phpstan --memory-limit=512M analyse
