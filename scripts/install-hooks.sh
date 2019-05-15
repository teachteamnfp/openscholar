#!/usr/bin/env bash

if [[ -z "$COMPOSER_DEV_MODE" ]]; then
   echo "No Dev, not installing pre-commit hooks."
   exit
fi

GIT_DIR=$(git rev-parse --git-dir)

echo "Installing hooks..."
# this command creates symlink to our pre-commit script
ln -s ./../../scripts/pre-commit.sh ${GIT_DIR}/hooks/pre-commit
echo "Done!"
