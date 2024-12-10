#!/bin/bash

# turn on bash's job control
set -m

# Run cron
cron

# Run services
service supervisor start

# Run supervisor
supervisorctl start laravel-worker:*

composer install

cp /var/www/env/.env /var/www/.env

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

php artisan storage:link

chown -R www-data:www-data /var/www/public/storage

mkdir -p /var/www/public/media

chown -R www-data:www-data /var/www/public/media

chown -R www-data:www-data /var/www/public/kcfinder/upload
