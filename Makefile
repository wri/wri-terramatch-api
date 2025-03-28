default:

composer:
	docker compose run composer composer install --ignore-platform-reqs

build-with-composer:
	docker buildx bake
	docker compose run composer composer install --ignore-platform-reqs

up:
	docker compose up -d

migrate-seed:
	docker compose exec php php ./artisan migrate:fresh
	docker compose exec php php ./artisan migrate-services
	docker compose exec php php ./artisan db:seed

migrate-seed-test:
	echo "create database if not exists terramatch_test;" | docker compose exec -T mariadb mysql -h localhost -u root -proot
	echo "grant all on terramatch_test.* to 'wri'@'%';" | docker compose exec -T mariadb mysql -h localhost -u root -proot
	docker compose exec -T php php artisan --env=testing migrate:fresh
	docker compose exec -T php php artisan --env=testing migrate-services
	docker compose exec -T php php artisan --env=testing db:seed

test: lint migrate-seed-test
	docker compose exec -T php ./vendor/bin/phpunit

test-single:
	docker compose exec php ./vendor/bin/phpunit --filter $(t)

ts: test-single

quick-test: lint migrate-seed-test
	docker compose exec php ./vendor/bin/phpunit --exclude=skipPipeline,slow

lint:
	docker compose exec -T php ./vendor/bin/php-cs-fixer fix -v --dry-run --stop-on-violation --using-cache=no

lint-fix:
	docker compose exec php ./vendor/bin/php-cs-fixer fix -v

lint-test:
	docker compose exec php ./vendor/bin/php-cs-fixer fix -v
	docker compose exec php ./vendor/bin/phpunit --testsuite V2

lint-test-all:
	docker compose exec php ./vendor/bin/php-cs-fixer fix -v
	docker compose exec php ./vendor/bin/phpunit

doc:
	npm run doc-v2

clean-files:
	docker compose exec php rm -rf ./storage/framework/cache/laravel-excel
	docker compose exec php rm -rf ./storage/app/public/*
	docker compose exec php rm -rf ./storage/app/translations/*
	docker compose exec php rm -rf ./public/storage/*
