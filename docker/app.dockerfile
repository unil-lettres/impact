FROM php:8.3-apache-bookworm AS base

ENV DOCKER_RUNNING=true

ENV NODE_VERSION=20
ENV COMPOSER_VERSION=2.6

# Update repositories
RUN apt-get update

# Install additional packages
RUN apt-get install -y \
    git \
    curl \
    nano \
    zip \
    unzip \
    openssl \
    cron \
    ffmpeg \
    mediainfo \
    supervisor \
    zlib1g-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    ca-certificates \
    gnupg

# Install needed php extensions
RUN apt-get clean; docker-php-ext-install pdo_mysql zip gd bcmath pcntl intl

# Install specific version of Composer
RUN curl --silent --show-error https://getcomposer.org/installer | php -- \
    --$COMPOSER_VERSION \
    --install-dir=/usr/local/bin --filename=composer

# Install specific version of Node
RUN mkdir -p /etc/apt/keyrings; \
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
    | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg; \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" \
    | tee /etc/apt/sources.list.d/nodesource.list; \
    apt-get update; \
    apt-get install -y --no-install-recommends nodejs

RUN a2enmod rewrite remoteip; \
    { \
    echo RemoteIPHeader X-Real-IP ; \
    echo RemoteIPTrustedProxy 10.0.0.0/8 ; \
    echo RemoteIPTrustedProxy 172.16.0.0/12 ; \
    echo RemoteIPTrustedProxy 192.168.0.0/16 ; \
    } > /etc/apache2/conf-available/remoteip.conf; \
    a2enconf remoteip

# Replace default crontab
ADD docker/config/crontab /etc/crontab

# Copy PHP configuration file
COPY docker/config/php.ini /usr/local/etc/php/php.ini

RUN mkdir -p /var/www/impact
WORKDIR /var/www/impact

FROM base AS dev

# Copy Apache configuration file
COPY docker/config/vhost-dev.conf /etc/apache2/sites-available/000-default.conf

# Copy supervisor configuration file
#
# docker exec <container-id> supervisorctl status
# docker exec <container-id> supervisorctl tail -f <service>
# docker exec <container-id> supervisorctl restart <service>
COPY docker/config/docker-dev-supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy the entrypoint script
COPY docker/config/docker-dev-entrypoint.sh /bin/docker-entrypoint.sh
RUN chmod +x /bin/docker-entrypoint.sh

ENTRYPOINT ["/bin/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

FROM base AS prod

ENV SHIBBOLETH_VERSION=3.4

# Copy Apache configuration file
COPY docker/config/vhost-prod.conf /etc/apache2/sites-available/000-default.conf

# Install additional packages needed for production
RUN apt-get install -y \
    ntp \
    libapache2-mod-shib \
    shibboleth-sp-common \
    shibboleth-sp-utils

# Enable needed Apache modules
RUN a2enmod rewrite ssl shib

# Generate Shibboleth configurations files
RUN curl --output /etc/shibboleth/shibboleth2.xml \
    "https://help.switch.ch/aai/docs/shibboleth/SWITCH/$SHIBBOLETH_VERSION/sp/deployment/download/customize.php/shibboleth2.xml?osType=nonwindows&hostname=myhost.com&targetURL=https%3A%2F%2Fmyhost.com%2FShibboleth.sso%2FSession&keyPath=%2Fetc%2Fshibboleth%2Fsp-key.pem&certPath=%2Fetc%2Fshibboleth%2Fsp-cert.pem&federation=SWITCHaai&supportEmail=aai%40myhost.com&wayfURL=https%3A%2F%2Fwayf.switch.ch%2FSWITCHaai%2FWAYF&metadataURL=http%3A%2F%2Fmetadata.aai.switch.ch%2Fmetadata.switchaai%2Bidp.xml&metadataFile=metadata.switchaai%2Bidp.xml&eduIDEntityID=https%3A%2F%2Feduid.ch%2Fidp%2Fshibboleth&hide=windows-only,metadataattributespart1,metadataattributespart2,eduid-only,interfederation,"
RUN curl --output /etc/shibboleth/attribute-map.xml \
    "https://help.switch.ch/aai/docs/shibboleth/SWITCH/$SHIBBOLETH_VERSION/sp/deployment/download/customize.php/attribute-map.xml?osType=nonwindows&hide=eduid-only,"
RUN curl --output /etc/shibboleth/attribute-policy.xml \
    "https://help.switch.ch/aai/docs/shibboleth/SWITCH/$SHIBBOLETH_VERSION/sp/deployment/download/customize.php/attribute-policy.xml?osType=nonwindows&hide="
RUN curl --output /etc/shibboleth/SWITCHaaiRootCA.crt.pem \
    https://ca.aai.switch.ch/SWITCHaaiRootCA.crt.pem

# Set handlerSSL to false in Shibboleth configuration file
# https://shibboleth.atlassian.net/wiki/spaces/SHIB2/pages/2577072242/SPReverseProxy
RUN sed -i "s|handlerSSL=\"true\"|handlerSSL=\"false\"|g" "/etc/shibboleth/shibboleth2.xml"

# Create a backup directory & copy all files from /etc/shibboleth/ to the backup directory
RUN mkdir -p /etc/shibboleth-backup
RUN cp -a /etc/shibboleth/. /etc/shibboleth-backup/

# Create a directory for the Shibboleth Unix domain socket
# Avoid "failed to bind to socket" issue
RUN mkdir -p /var/run/shibboleth/

# Copy the application, except data listed in dockerignore
COPY site/ /var/www/impact

# Install php dependencies
RUN cd /var/www/impact && \
    composer install --optimize-autoloader --no-interaction --no-dev

# Install js dependencies & compile
RUN cd /var/www/impact && \
    npm install && \
    npm run prod

# Copy Kubernetes poststart script
COPY docker/config/k8s-poststart.sh /var/www/impact/k8s-poststart.sh
RUN chmod +x /var/www/impact/k8s-poststart.sh

# Remove node_modules folder since it's not needed anymore
RUN rm -rf /var/www/impact/node_modules

# Change ownership of the application to www-data
RUN chown -R www-data:www-data /var/www/impact

# Copy supervisor configuration file
#
# docker exec <container-id> supervisorctl status
# docker exec <container-id> supervisorctl tail -f <service>
# docker exec <container-id> supervisorctl restart <service>
COPY docker/config/docker-prod-supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy the entrypoint script
COPY docker/config/docker-prod-entrypoint.sh /bin/docker-entrypoint.sh
RUN chmod +x /bin/docker-entrypoint.sh

ENTRYPOINT ["/bin/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
