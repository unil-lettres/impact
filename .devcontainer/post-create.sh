#!/bin/bash

if [ -n "$CODESPACE_NAME" ]; then
  echo "Set the APP_URL envar with the Codespace URL..."
  APP_URL="https://$CODESPACE_NAME-8787.app.github.dev"
  if grep -q '^APP_URL=' ./site/.env; then
    sed -i "s|^APP_URL=.*|APP_URL=$APP_URL|" ./site/.env
  else
    echo "APP_URL=$APP_URL" >> ./site/.env
  fi

  # Ensure the CODESPACE_NAME is available in Laravel
  echo "CODESPACE_NAME=$CODESPACE_NAME" >> ./site/.env
fi

echo "Waiting for composer dependencies to be installed..."
# This is needed to use the artisan commands
while [ ! -f ./site/vendor/autoload.php ]; do
    sleep 1
done

echo "Checking if the database is ready..."
# This is needed to seed the database
until php ./site/artisan migrate:status > /dev/null 2>&1; do
    sleep 1
done

echo "Seed the database..."
php ./site/artisan db:seed
