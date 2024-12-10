FROM php:7.4-fpm

# Install dockerize so we can wait for containers to be ready
ENV DOCKERIZE_VERSION 0.6.1

RUN curl -s -f -L -o /tmp/dockerize.tar.gz https://github.com/jwilder/dockerize/releases/download/v$DOCKERIZE_VERSION/dockerize-linux-amd64-v$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf /tmp/dockerize.tar.gz \
    && rm /tmp/dockerize.tar.gz

# Install Composer

ENV COMPOSER_VERSION 2.1.5

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=$COMPOSER_VERSION

# Install nodejs
RUN curl -sL https://deb.nodesource.com/setup_14.x | bash

# Install libs
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libz-dev \
        libpq-dev \
        libjpeg-dev \
        libpng-dev \
        libssl-dev \
        libzip-dev \
        libmagickwand-dev --no-install-recommends \
        unzip \
        libxml2-dev \
        zip \
        nano \
        cron \
        supervisor \
        nodejs \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apt-get clean \
    && pecl install redis \
    && docker-php-ext-configure gd \
    && docker-php-ext-configure zip \
    && docker-php-ext-install \
        gd \
        exif \
        opcache \
        pdo \
        pdo_mysql \
        sockets \
        pcntl \
        zip \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*;

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . /var/www/
COPY ./env/.env /var/www/
COPY ./env/laravel.ini /usr/local/etc/php/conf.d/

# Copy crontab file to the cron.d directory
#ADD ./env/crontab /etc/cron.d/crontab
# Give execution rights on the cron job
#RUN chmod 0644 /etc/cron.d/crontab

RUN echo "* * * * * www-data php /var/www/artisan schedule:run >> /var/log/cron.log 2>&1" >> /etc/crontab
RUN touch /var/log/cron.log

# Copy supervisor conf
COPY ./env/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

RUN chmod +x /var/www/server-run.sh

RUN composer install

#CMD ["/var/www/server_run.sh"]

#EXPOSE 9000

#CMD bash -c "composer install && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && php artisan storage:link && chown -R www-data:www-data /var/www/public/storage && mkdir -p /var/www/public/media && chown -R www-data:www-data /var/www/public/media && chown -R www-data:www-data /var/www/public/kcfinder/upload"

RUN composer install \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && php artisan storage:link \
    && chown -R www-data:www-data /var/www/public/storage \
    && mkdir -p /var/www/public/media \
    && chown -R www-data:www-data /var/www/public/media \
    && chown -R www-data:www-data /var/www/public/kcfinder/upload \
    #&& crontab -u www-data /etc/cron.d/crontab \
    && cron

