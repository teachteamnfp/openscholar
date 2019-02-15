#!/usr/bin/env bash

echo "Registering code standards..."
composer global require dealerdirect/phpcodesniffer-composer-installer

PHPCS_OUTPUT=$(phpcs -i)

if ! [[ ${PHPCS_OUTPUT} == *"DrupalPractice"* && ${PHPCS_OUTPUT} == *"Drupal"* ]]; then
  echo "Unable to register code standards. Please fix the problems."
  exit 1
fi

echo "Done!"
