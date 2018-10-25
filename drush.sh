#!/usr/bin/env bash

# Run drush command through docker.
DRUSH_COMMAND="docker-compose exec php drush @local $@";
$DRUSH_COMMAND;
