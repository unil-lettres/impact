#!/bin/bash

if [ "$(pwd)" != "/var/www/impact" ]; then
  cd /var/www/impact || { echo "Failed to change directory to /var/www/impact"; exit 1; }
fi

# Notify BugSnag about the deployment
php artisan bugsnag:deploy \
  --builder "Deployer"
