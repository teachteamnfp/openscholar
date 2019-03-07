include .env
export

.PHONY: build

DIR := ${CURDIR}

build: vendor
	docker-compose run -T node sh -c "ls -al"
	docker-compose run -T node sh -c "npm install"
	docker-compose run -T node sh -c "npm install && cd profile/themes && ls -al"
	docker-compose run -T node sh -c "npm install && cd profile/themes && ./../../node_modules/.bin/gulp sass"

vendor: composer.json composer.lock
	docker-compose exec -T php composer install
