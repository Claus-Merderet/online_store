PHP_CONTAINER=online_store-php-1

build:
	docker compose build
up:
	docker compose up
down:
	docker compose down
symfony:
	docker exec -it $(PHP_CONTAINER) bin/console $(cmd)
bash:
	docker compose exec php bash
entity:
	make symfony cmd="make:entity"
migrate:
	make symfony cmd="doctrine:migrations:migrate"
migrate_test:
	make symfony cmd="doctrine:migrations:migrate --env=test"
migration:
	make symfony cmd="make:migration"
kafka:
	make symfony cmd="app:consume-reports"
analyse:
	vendor/bin/phpstan --memory-limit=512M analyse
fix:
	 vendor/bin/php-cs-fixer fix src/
test:
	docker exec -it $(PHP_CONTAINER) ./vendor/bin/phpunit $(args)
fixtures:
	make symfony cmd="doctrine:fixtures:load"
fixtures_test:
	make symfony cmd="doctrine:fixtures:load --env=test"