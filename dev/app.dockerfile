FROM php:7.3-fpm

# Update packages
RUN apt-get update

# Install PHP and composer dependencies
RUN apt-get install -y git curl nano zlib1g-dev libpng-dev libxml2-dev supervisor ffmpeg ffmpeg2theora mediainfo curl cron git zip unzip

# Install needed extensions
# Here you can install any other extension that you need during the test and deployment process
RUN apt-get clean; docker-php-ext-install pdo pdo_mysql zip gd bcmath tokenizer ctype json mbstring xml

# Installs Composer to easily manage your PHP dependencies.
RUN curl --silent --show-error https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node
RUN apt-get update &&\
  apt-get install -y --no-install-recommends gnupg &&\
  curl -sL https://deb.nodesource.com/setup_10.x | bash - &&\
  apt-get update &&\
  apt-get install -y --no-install-recommends nodejs &&\
  npm config set registry https://registry.npm.taobao.org --global &&\
  npm install --global gulp-cli

# Add impact-cron file in the cron directory
ADD ./config/cron /etc/cron.d/impact
RUN chmod 0644 /etc/cron.d/impact

# Copy supervisor configuration file
# docker exec <container-id> supervisorctl status
# docker exec <container-id> supervisorctl tail -f php
# docker exec <container-id> supervisorctl tail -f cron
# docker exec <container-id> supervisorctl restart php
COPY ./config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord"]
