#!/usr/bin/env bash

# Copy the .env file for CI
cp .env.dusk.ci .env
# Remove the .env file used for local testing
# to fallback to the main .env file
rm .env.dusk.testing

# Install php dependencies
composer install --no-interaction

# Install js dependencies & compile
npm install
npm run prod

# Run migrations & seed data
php artisan migrate:fresh --seed

# Create the symlink to make storage public
php artisan storage:link
