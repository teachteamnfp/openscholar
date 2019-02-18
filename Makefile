.PHONY: build

build: vendor
		docker-compose exec -T npm sh -c "cd profile/themes/os_base && npm install"

vendor: composer.json composer.lock
		docker-compose exec -T php composer install
