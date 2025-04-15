#!/bin/bash

if [ -n "$CODESPACE_NAME" ]; then
  # Set the APP_URL envar to the codespace URL
  APP_URL="https://$CODESPACE_NAME-8787.app.github.dev"
  if grep -q '^APP_URL=' ./site/.env; then
    sed -i "s|^APP_URL=.*|APP_URL=$APP_URL|" ./site/.env
  else
    echo "APP_URL=$APP_URL" >> ./site/.env
  fi

  # Ensure the CODESPACE_NAME is available in Laravel
  echo "CODESPACE_NAME=$CODESPACE_NAME" >> ./site/.env
fi

# Populate the database with dummy data
cd ./site
php artisan db:seed
