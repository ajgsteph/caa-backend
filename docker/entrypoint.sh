#!/bin/bash
set -e

cd /var/www

if [ ! -f /var/www/.env ]; then
    cp /var/www/.env.example /var/www/.env
fi

# php artisan key:generate --force
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan scribe:generate  

service nginx start
php-fpm -F