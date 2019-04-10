include .env
export

.PHONY: build

DIR := ${CURDIR}

build: vendor
	docker-compose run --rm -T node sh -c "cd profile/themes && ./../../node_modules/.bin/gulp sass"

vendor: composer.json composer.lock
	docker-compose exec -T php composer install
