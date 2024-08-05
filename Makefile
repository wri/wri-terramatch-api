default:

composer:
	docker-compose run composer composer install --ignore-platform-reqs

rebuild-with-composer:
	docker-compose build --no-cache
	docker-compose run composer composer install --ignore-platform-reqs

ls:
	ls -lh
	pwd

build:
	docker-compose build --no-cache
	cp .env.example .env
	docker-compose run composer composer install --ignore-platform-reqs
	docker-compose run npm npm ci
	docker-compose run php php ./artisan key:generate --ansi
	docker-compose run php php ./artisan jwt:secret
	docker-compose run npm npm run development

up:
	docker-compose up -d

migrate-seed:
	docker-compose exec php php ./artisan migrate:fresh
	docker-compose exec php php ./artisan migrate-services
	docker-compose exec php php ./artisan db:seed

migrate-seed-test:
	echo "create database if not exists terramatch_test;" | docker-compose exec -T mariadb mysql -h localhost -u root -proot
	echo "grant all on terramatch_test.* to 'wri'@'%';" | docker-compose exec -T mariadb mysql -h localhost -u root -proot
	docker-compose exec -T php php artisan --env=testing migrate:fresh
	docker-compose exec -T php php artisan --env=testing migrate-services
	docker-compose exec -T php php artisan --env=testing db:seed

test: lint migrate-seed-test
	docker-compose exec -T php ./vendor/bin/phpunit

test-single:
	docker-compose exec php ./vendor/bin/phpunit --filter $(t)

ts: test-single

quick-test: lint migrate-seed-test
	docker-compose exec php ./vendor/bin/phpunit --exclude=skipPipeline,slow

lint:
	docker-compose exec -T php ./vendor/bin/php-cs-fixer fix -v --dry-run --stop-on-violation --using-cache=no

lint-fix:
	docker-compose exec php ./vendor/bin/php-cs-fixer fix -v

lint-test:
	docker-compose exec php ./vendor/bin/php-cs-fixer fix -v
	docker-compose exec php ./vendor/bin/phpunit --testsuite V2

lint-test-all:
	docker-compose exec php ./vendor/bin/php-cs-fixer fix -v
	docker-compose exec php ./vendor/bin/phpunit

# REQUIRES FFMPEG
frames:
	ffmpeg -loop 1 -i resources/frames/introduction.png -f lavfi -i anullsrc=channel_layout=5.1:sample_rate=48000 \
		-c:v libx264 -t 3 -pix_fmt yuv420p -vf scale=1920:1080 resources/frames/introduction.mp4
	ffmpeg -loop 1 -i resources/frames/aims.png -f lavfi -i anullsrc=channel_layout=5.1:sample_rate=48000 \
		-c:v libx264 -t 3 -pix_fmt yuv420p -vf scale=1920:1080 resources/frames/aims.mp4
	ffmpeg -loop 1 -i resources/frames/importance.png -f lavfi -i anullsrc=channel_layout=5.1:sample_rate=48000 \
		-c:v libx264 -t 3 -pix_fmt yuv420p -vf scale=1920:1080 resources/frames/importance.mp4

doc:
	npm run doc-v2

clean-files:
	docker-compose exec php rm -rf ./storage/framework/cache/laravel-excel
	docker-compose exec php rm -rf ./storage/app/public/*
	docker-compose exec php rm -rf ./storage/app/translations/*
	docker-compose exec php rm -rf ./public/storage/*
