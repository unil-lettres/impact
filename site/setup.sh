#!/usr/bin/env bash

# Setup the Laravel environment file
if [ -z "$CI" ]; then
  # Copy file for local dev with docker
  cp .env.example .env
else
  # Copy file for CI
  cp .env.dusk.ci .env
fi

# Install php dependencies
composer install --no-interaction

# Update the application key
php artisan key:generate

if [ -z "$CI" ]; then
  # Install js dependencies for local dev
  npm install
  npm run dev
fi

# Run migrations & seed data
php artisan migrate:fresh --seed

# Create the symlink to make storage public
php artisan storage:link
