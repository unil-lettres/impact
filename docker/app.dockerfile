FROM php:8.5-apache-trixie AS base

ENV DOCKER_RUNNING=true
ENV LANG=en_US.UTF-8
ENV LANGUAGE=en_US:en
ENV LC_ALL=en_US.UTF-8
ENV TZ=Europe/Zurich

ENV NODE_VERSION=24
ENV PNPM_VERSION=12
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

# Apache RemoteIP and Rewrite configuration
RUN a2enmod rewrite remoteip && \
    { \
      echo "RemoteIPHeader X-Real-IP" ; \
      echo "RemoteIPTrustedProxy 10.0.0.0/8" ; \
      echo "RemoteIPTrustedProxy 172.16.0.0/12" ; \
      echo "RemoteIPTrustedProxy 192.168.0.0/16" ; \
    } > /etc/apache2/conf-available/remoteip.conf && \
    a2enconf remoteip

# Create unprivileged user and set permissions for Apache runtime directories
RUN groupadd -r dockeruser --gid=1000 && \
    useradd -r -g dockeruser --uid=1000 --create-home --home-dir=/home/dockeruser --shell=/sbin/nologin dockeruser && \
    sed -i 's/: ${APACHE_RUN_USER:=www-data}/: ${APACHE_RUN_USER:=dockeruser}/' /etc/apache2/envvars && \
    sed -i 's/: ${APACHE_RUN_GROUP:=www-data}/: ${APACHE_RUN_GROUP:=dockeruser}/' /etc/apache2/envvars && \
    mkdir -p /var/run/apache2 /var/lock/apache2 /var/log/apache2 /var/www/impact && \
    chown -R dockeruser:dockeruser /var/run/apache2 /var/lock/apache2 /var/log/apache2 /var/www/impact

# Copy PHP configuration file
COPY docker/config/php.ini /usr/local/etc/php/php.ini

# Copy supervisor configuration file
#
# docker exec <container-id> supervisorctl status
# docker exec <container-id> supervisorctl tail -f <service>
# docker exec <container-id> supervisorctl restart <service>
COPY --chown=dockeruser:dockeruser docker/config/docker-app-supervisord.conf /etc/supervisor/supervisord.conf

WORKDIR /var/www/impact

FROM base AS base-node

# Install specific version of Node and pnpm
RUN mkdir -p /etc/apt/keyrings && \
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg && \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list && \
    apt-get update && \
    apt-get install -y --no-install-recommends nodejs && \
    corepack enable && \
    corepack prepare pnpm@$PNPM_VERSION --activate && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

FROM base-node AS frontend-builder

WORKDIR /app

COPY site/package.json site/pnpm-lock.yaml* site/pnpm-workspace.yaml ./
RUN pnpm install --frozen-lockfile

COPY site/ .
RUN pnpm run prod

FROM base-node AS dev

# Install pnpm as dockeruser so Corepack configuration and cache remain accessible
RUN su -s /bin/sh dockeruser -c "corepack install --global pnpm@$PNPM_VERSION && pnpm --version"

# Copy Apache configuration file and entrypoint script
COPY --chown=dockeruser:dockeruser docker/config/vhost-dev.conf /etc/apache2/sites-available/000-default.conf
COPY --chown=dockeruser:dockeruser --chmod=755 docker/config/docker-app-dev-entrypoint.sh /bin/docker-entrypoint.sh

# Switch to unprivileged user
USER dockeruser

ENTRYPOINT ["/bin/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]

FROM base AS prod

# Copy source code
COPY --chown=dockeruser:dockeruser site/ /var/www/impact

# Copy compiled assets from frontend-builder
COPY --chown=dockeruser:dockeruser --from=frontend-builder /app/public /var/www/impact/public

# Copy Apache configuration file, K8s post-start script and entrypoint script
COPY --chown=dockeruser:dockeruser docker/config/vhost-prod.conf /etc/apache2/sites-available/000-default.conf
COPY --chown=dockeruser:dockeruser --chmod=755 docker/config/k8s-poststart.sh /var/www/impact/k8s-poststart.sh
COPY --chown=dockeruser:dockeruser --chmod=755 docker/config/docker-app-prod-entrypoint.sh /bin/docker-entrypoint.sh

# Switch to unprivileged user
USER dockeruser

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

ENTRYPOINT ["/bin/docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
