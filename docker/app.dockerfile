FROM php:8.5-apache-trixie AS base

ENV DOCKER_RUNNING=true
ENV LANG=en_US.UTF-8
ENV LANGUAGE=en_US:en
ENV LC_ALL=en_US.UTF-8
ENV TZ=Europe/Zurich

ENV NODE_VERSION=22
ENV COMPOSER_VERSION=2.9.8

# Update repositories & install additional packages
RUN apt-get update && apt-get install -y \
    git \
    curl \
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
    gnupg \
    locales \
    tzdata

# Generate and set locale
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    locale-gen en_US.UTF-8 && \
    update-locale LANG=en_US.UTF-8

# Set timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install needed php extensions
RUN apt-get clean; docker-php-ext-install pdo_mysql zip gd bcmath pcntl intl

# Install specific version of Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --version=$COMPOSER_VERSION \
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

# Copy Apache configuration file
COPY docker/config/vhost-prod.conf /etc/apache2/sites-available/000-default.conf

# Copy the application, except data listed in dockerignore
COPY site/ /var/www/impact

# Install php dependencies
RUN cd /var/www/impact && \
    composer install --optimize-autoloader --no-interaction --no-dev

# Install js dependencies & compile
RUN cd /var/www/impact && \
    npm install && \
    npm run prod && \
    npm cache clean --force && \
    rm -rf /root/.npm && \
    rm -rf /var/www/impact/node_modules

# Copy Kubernetes poststart script
COPY docker/config/k8s-poststart.sh /var/www/impact/k8s-poststart.sh
RUN chmod +x /var/www/impact/k8s-poststart.sh

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
