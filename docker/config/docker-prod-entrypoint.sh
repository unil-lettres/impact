#!/bin/bash
set -e

echoerr() { echo "$@" 1>&2; }

check_vars_exist() {
  var_names=("$@")

  for var_name in "${var_names[@]}"; do
    if [ -z "${!var_name}" ]; then
      echoerr "error: missing ${var_name} environment variable"
      exit 1
    fi
  done
}

# Ensure there is no local .env file
if [ -f ".env" ]; then
  mv .env .env.bak
  echoerr ".env file detected - moved to .env.bak"
  echoerr "Please update your configuration to use environment variables in the container!"
fi

# Check a number of essential variables are set
check_vars_exist \
  APP_KEY \
  APP_URL \
  DB_DATABASE \
  DB_HOST \
  DB_PASSWORD \
  DB_PORT \
  DB_USERNAME

echo "Optimize view, routes, events, configs..."
php artisan optimize

echo "Starting Migration..."
php artisan migrate --force

echo "Clear expired password reset tokens"
php artisan auth:clear-resets

echo "If needed, create the symlink to make storage public..."
php artisan storage:link

# run commands from dockerfile
exec "${@}"
