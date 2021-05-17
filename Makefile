default:

build:
	docker-compose build --no-cache
	cp .env.example .env
	docker-compose run composer composer install
	docker-compose run npm npm ci
	docker-compose run php ./artisan key:generate --ansi
	docker-compose run php ./artisan jwt:secret
	docker-compose run npm npm run development

up:
	docker-compose up

migrate-seed:
	docker-compose run php ./artisan migrate:fresh
	docker-compose run php ./artisan migrate-services
	docker-compose run php ./artisan db:seed

test: migrate-seed
	docker-compose run php ./vendor/bin/parallel-lint --exclude ./vendor ./
	docker-compose run php ./vendor/bin/phpunit

# REQUIRES FFMPEG
frames:
	ffmpeg -loop 1 -i resources/frames/introduction.png -f lavfi -i anullsrc=channel_layout=5.1:sample_rate=48000 \
		-c:v libx264 -t 3 -pix_fmt yuv420p -vf scale=1920:1080 resources/frames/introduction.mp4
	ffmpeg -loop 1 -i resources/frames/aims.png -f lavfi -i anullsrc=channel_layout=5.1:sample_rate=48000 \
		-c:v libx264 -t 3 -pix_fmt yuv420p -vf scale=1920:1080 resources/frames/aims.mp4
	ffmpeg -loop 1 -i resources/frames/importance.png -f lavfi -i anullsrc=channel_layout=5.1:sample_rate=48000 \
		-c:v libx264 -t 3 -pix_fmt yuv420p -vf scale=1920:1080 resources/frames/importance.mp4
