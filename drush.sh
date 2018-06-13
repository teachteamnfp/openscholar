#!/usr/bin/env bash

# Run drush command through docker.
DRUSH_COMMAND="docker-compose exec php drush -r web/ $@";
$DRUSH_COMMAND;
