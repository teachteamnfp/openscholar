#!/usr/bin/env bash

echo "Running pre-commit hook"

echo "Checking Docker status..."

# If Docker container is available, then check code standard with Docker.
# Otherwise, check it in standalone way.
IS_DOCKER_CONTAINER_AVAILABLE=false
DOCKER_INFO=$(docker info)

if [[ $? -eq 0 ]]; then
  DOCKER_CONTAINER_STATUS=$(docker ps)

  if ! [[ ${DOCKER_CONTAINER_STATUS} == *"openscholar_php"* ]]; then
    echo "Docker is installed, but OpenScholar PHP container is not running. Start the container and try committing again."
    exit 1
  fi

  IS_DOCKER_CONTAINER_AVAILABLE=true
fi

if [[ ${IS_DOCKER_CONTAINER_AVAILABLE} = true ]] ; then
  echo "Docker container is available. Checking code standards now..."
  git diff --cached --name-only | xargs docker-compose exec -T php composer code-standard $1
else
  echo "Docker is not available. Checking code standards now..."
  git diff --cached --name-only | xargs composer code-standard $1
fi

if [[ $? -eq 0 ]]; then
  exit 0
fi

echo "There are code standard issues. Trying to fix them automatically with phpcbf..."

if [[ ${IS_DOCKER_CONTAINER_AVAILABLE} = true ]] ; then
  git diff --cached --name-only | xargs docker-compose exec -T php composer code-standard-fix $1
else
  git diff --cached --name-only | xargs composer code-standard-fix $1
fi

exit 1
