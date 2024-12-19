#!/bin/bash

if [ "$(pwd)" != "/var/www/impact" ]; then
  cd /var/www/impact || { echo "Failed to change directory to /var/www/impact"; exit 1; }
fi

# Ensure mounted volumes have correct permissions
chown -R www-data:www-data storage/app/public
chown -R www-data:www-data storage/logs

# Notify BugSnag about the deployment
php artisan bugsnag:deploy \
  --builder "Deployer"
