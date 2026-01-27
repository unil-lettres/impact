FROM php:8.5-cli-trixie AS base

ENV DOCKER_RUNNING=true
ENV LANG=en_US.UTF-8
ENV LANGUAGE=en_US:en
ENV LC_ALL=en_US.UTF-8
ENV TZ=Europe/Zurich

ENV COMPOSER_VERSION=2.8.12

# Update repositories & install additional packages
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    openssl \
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

# Copy PHP configuration file
COPY docker/config/php.ini /usr/local/etc/php/php.ini

RUN mkdir -p /var/www/impact
WORKDIR /var/www/impact

FROM base AS dev

# Copy supervisor configuration file
#
# docker exec <container-id> supervisorctl status
# docker exec <container-id> supervisorctl tail -f <service>
# docker exec <container-id> supervisorctl restart <service>
COPY docker/config/docker-worker-supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

FROM base AS prod

# Copy the application, except data listed in dockerignore
COPY site/ /var/www/impact

# Install php dependencies
RUN cd /var/www/impact && \
    composer install --optimize-autoloader --no-interaction --no-dev

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
COPY docker/config/docker-worker-supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy the entrypoint script
COPY docker/config/docker-worker-prod-entrypoint.sh /bin/docker-entrypoint.sh
RUN chmod +x /bin/docker-entrypoint.sh

ENTRYPOINT ["/bin/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
