FROM php:8.5-cli-trixie AS base

ENV DOCKER_RUNNING=true
ENV LANG=en_US.UTF-8
ENV LANGUAGE=en_US:en
ENV LC_ALL=en_US.UTF-8
ENV TZ=Europe/Zurich

ENV COMPOSER_VERSION=2.9.8

# Install PHP extension installer helper
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install OS packages, set locales, timezone, and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    zip \
    unzip \
    openssl \
    ffmpeg \
    mediainfo \
    supervisor \
    ca-certificates \
    gnupg \
    locales \
    tzdata \
    vim \
    && echo "en_US.UTF-8 UTF-8" > /etc/locale.gen \
    && locale-gen en_US.UTF-8 \
    && update-locale LANG=en_US.UTF-8 \
    && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone \
    && install-php-extensions pdo_mysql zip gd bcmath pcntl intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install specific version of Composer
RUN curl --silent --show-error https://getcomposer.org/installer | php -- \
    --version=$COMPOSER_VERSION \
    --install-dir=/usr/local/bin --filename=composer

# Create unprivileged user and prepare writable runtime directories
RUN groupadd -r dockeruser --gid=1000 && \
    useradd -r -g dockeruser --uid=1000 --create-home --home-dir=/home/dockeruser --shell=/sbin/nologin dockeruser && \
    mkdir -p /var/www/impact && \
    chown -R dockeruser:dockeruser /var/www/impact

# Copy PHP configuration file
COPY docker/config/php.ini /usr/local/etc/php/php.ini

# Copy supervisor configuration file
#
# docker exec <container-id> supervisorctl status
# docker exec <container-id> supervisorctl tail -f <service>
# docker exec <container-id> supervisorctl restart <service>
COPY --chown=dockeruser:dockeruser docker/config/docker-worker-supervisord.conf /etc/supervisor/supervisord.conf

WORKDIR /var/www/impact

FROM base AS dev

# Switch to unprivileged user
USER dockeruser

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]

FROM base AS prod

# Copy source code
COPY --chown=dockeruser:dockeruser site/ /var/www/impact

# Copy K8s post-start script and entrypoint script
COPY --chown=dockeruser:dockeruser --chmod=755 docker/config/k8s-poststart.sh /var/www/impact/k8s-poststart.sh
COPY --chown=dockeruser:dockeruser --chmod=755 docker/config/docker-worker-prod-entrypoint.sh /bin/docker-entrypoint.sh

# Switch to unprivileged user
USER dockeruser

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

ENTRYPOINT ["/bin/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
