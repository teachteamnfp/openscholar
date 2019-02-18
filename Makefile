.PHONY: build

build: vendor
		docker-compose exec npm sh -c "cd profile/themes/os_base && npm install"

vendor: composer.json composer.lock
		docker-compose exec php composer install
