#!/usr/bin/env bash
#!/bin/sh
set -e

# If we want to debug the tests suite that fails we would change this variable.
#TEST_SUITE="solr"
#EMBEDLYAPIKEY=""

# If we want to debug the failing tests, set the variable to 1.
#DEBUG=1

cp docker_travis/docker-compose.override.yml.travis docker-compose.override.yml
docker-compose up -d
docker-compose exec web docker_travis/docker.install.sh
docker-compose exec web docker_travis/run.sh
