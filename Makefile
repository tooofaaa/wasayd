.PHONY: up down build shell artisan migrate seed test fresh tinker logs npm-dev npm-build composer-install queue horizon

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build --no-cache

shell:
	docker compose exec app bash

artisan:
	docker compose exec app php artisan $(filter-out $@,$(MAKECMDGOALS))

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan db:seed

test:
	docker compose exec app php artisan test --parallel

fresh:
	docker compose exec app php artisan migrate:fresh --seed

tinker:
	docker compose exec app php artisan tinker

logs:
	docker compose logs -f

npm-dev:
	docker compose exec app npm run dev

npm-build:
	docker compose exec app npm run build

composer-install:
	docker compose exec app composer install

queue:
	docker compose exec app php artisan queue:work

horizon:
	docker compose exec app php artisan horizon

tools:
	docker compose --profile tools up -d

%:
	@:
