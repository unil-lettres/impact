FROM php:7.4-fpm

# Update repositories
RUN apt-get update

# Install additional packages
RUN apt-get install -y nano zlib1g-dev libpng-dev libxml2-dev libzip-dev libonig-dev supervisor ffmpeg ffmpeg2theora mediainfo curl cron git zip unzip

# Install needed php extensions
RUN apt-get clean; docker-php-ext-install pdo pdo_mysql zip gd bcmath tokenizer ctype json mbstring xml intl

# Install Composer
RUN curl --silent --show-error https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node
RUN apt-get update &&\
  apt-get install -y --no-install-recommends gnupg &&\
  curl -sL https://deb.nodesource.com/setup_14.x | bash - &&\
  apt-get update &&\
  apt-get install -y --no-install-recommends nodejs &&\
  npm install --global gulp-cli

# Replace default crontab
ADD ./config/crontab /etc/crontab

# Copy supervisor configuration file
# This is used to manage cron & php-fpm services
#
# docker exec <container-id> supervisorctl status
# docker exec <container-id> supervisorctl tail -f php
# docker exec <container-id> supervisorctl tail -f cron
# docker exec <container-id> supervisorctl restart php
COPY ./config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
