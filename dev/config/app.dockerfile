FROM php:8.1-fpm

ENV NODE_VERSION=16
ENV COMPOSER_VERSION=2.2

# Update repositories
RUN apt-get update

# Install additional packages
RUN apt-get install -y nano zlib1g-dev libpng-dev libzip-dev libicu-dev supervisor ffmpeg mediainfo curl cron git zip unzip

# Install needed php extensions
RUN apt-get clean; docker-php-ext-install pdo_mysql zip gd bcmath pcntl intl

# Install specific version of Composer
RUN curl --silent --show-error https://getcomposer.org/installer | php -- \
    --$COMPOSER_VERSION \
    --install-dir=/usr/local/bin --filename=composer

# Install specific version of Node
RUN curl -sL https://deb.nodesource.com/setup_$NODE_VERSION.x | bash - &&\
    apt-get update &&\
    apt-get install -y --no-install-recommends nodejs

# Replace default crontab
ADD ./crontab /etc/crontab

# Copy supervisor configuration file
#
# docker exec <container-id> supervisorctl status
# docker exec <container-id> supervisorctl tail -f <service>
# docker exec <container-id> supervisorctl restart <service>
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
