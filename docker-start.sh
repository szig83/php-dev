#!/bin/bash

# Script to create the Docker network and start containers for the PHP development environment

NETWORK_NAME="php_dev_network"
BUILD_FLAG=false

# Check for build parameter
if [ "$1" = "--build" ]; then
  BUILD_FLAG=true
fi

# Check if the network already exists
docker network ls | grep -q "$NETWORK_NAME"

# shellcheck disable=SC2181
if [ $? -eq 0 ]; then
  echo "Network '$NETWORK_NAME' already exists. No action needed."
else
  docker network create "$NETWORK_NAME"
  if [ $? -eq 0 ]; then
    echo "Network '$NETWORK_NAME' created successfully."
  else
    echo "Failed to create network '$NETWORK_NAME'."
    exit 1
  fi
fi

# Function to start services with or without build
start_service() {
  local service_dir=$1
  local service_name=$2
  echo "Starting Docker Compose for $service_name..."
  cd "$service_dir" || exit 1
  if [ "$BUILD_FLAG" = true ]; then
    docker-compose up -d --build
  else
    docker-compose up -d
  fi
  # shellcheck disable=SC2181
  if [ $? -eq 0 ]; then
    echo "$service_name container started successfully."
  else
    echo "Failed to start $service_name container."
  fi
  cd - || exit 1
}

# Start services
start_service "docker/containers/postgres" "Postgres"
start_service "docker/containers/keydb" "KeyDB"
start_service "docker/containers/php" "PHP"
start_service "docker/containers/nginx" "Nginx"

echo "All services started."

echo "Running 'docker ps' to check container status..."
docker ps

echo "Done."
