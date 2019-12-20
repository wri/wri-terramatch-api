default:

migrate-seed:
	docker-compose run php ./artisan migrate
	docker-compose run php ./artisan migrate-services
	docker-compose run php ./artisan db:seed

build:
	docker-compose build
	cp .env.example .env
	docker-compose run composer composer install
	docker-compose run npm npm ci
	docker-compose run php ./artisan key:generate --ansi
	docker-compose run php ./artisan jwt:secret
	docker-compose run npm npm run development

test: migrate-seed
	docker-compose run php ./vendor/bin/phpunit

permissions:
	chmod -R a+rw storage

erd:
	dot -T png -o public/images/erd.png erd.gv