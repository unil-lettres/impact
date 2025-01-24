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

# Check if SHIB_HOSTNAME and SHIB_CONTACT are set
if [ -n "$SHIB_HOSTNAME" ] && [ -n "$SHIB_CONTACT" ]; then
  # Copy Shibboleth configuration files from backup if the files do not exist
  if [ ! -f /etc/shibboleth/shibboleth2.xml ]; then
    echo "Copying Shibboleth config files from backup."
    cp -af /etc/shibboleth-backup/. /etc/shibboleth/
    echo "Files copied successfully."
  fi

  # Update Shibboleth main configuration file (shibboleth2.xml)
  if grep -q "myhost.com" "/etc/shibboleth/shibboleth2.xml"; then
    sed -i "s|myhost.com|$SHIB_HOSTNAME|g" "/etc/shibboleth/shibboleth2.xml"
    echo "Replaced all occurrences of DNS placeholder by $SHIB_HOSTNAME in Shibboleth configuration."

    sed -i "s|aai@$SHIB_HOSTNAME|$SHIB_CONTACT|g" "/etc/shibboleth/shibboleth2.xml"
    echo "Replaced contact email address in Shibboleth configuration."
  else
    echo "Shibboleth already configured. No action needed."
  fi

  # Check if Shibboleth key or certificate file exists, if not generate them
  if [[ ! -f /etc/shibboleth/sp-key.pem && ! -f /etc/shibboleth/sp-cert.pem ]]; then
    echo "Shibboleth key and certificate files missing. Generated new key and certificate for $SHIB_HOSTNAME hostname"
    shib-keygen -f -u _shibd -h $SHIB_HOSTNAME -y 10 -o /etc/shibboleth
  else
    echo "Shibboleth key and certificate files already exist. No action needed."
  fi

  # Update Apache configuration file (000-default.conf)
  # https://shibboleth.atlassian.net/wiki/spaces/SHIB2/pages/2577072242/SPReverseProxy
  if grep -q "ServerName impact" "/etc/apache2/sites-available/000-default.conf"; then
    sed -i "s|ServerName impact|ServerName https://$SHIB_HOSTNAME:443|g" "/etc/apache2/sites-available/000-default.conf"
    echo "Replaced ServerName by $SHIB_HOSTNAME in Apache configuration."
  else
    echo "Apache already configured. No action needed."
  fi
else
  echo "Shibboleth environment variables are not set. Skipping Shibboleth configuration."
fi

echo "Optimize view, routes, events, configs..."
php artisan optimize

echo "Starting Migration..."
php artisan migrate --force

echo "Clear expired password reset tokens"
php artisan auth:clear-resets

echo "If needed, create the symlink to make storage public..."
php artisan storage:link

# run commands from dockerfile
"${@}"
