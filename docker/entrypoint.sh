#!/bin/bash

cd /var/www

if [ ! -f /var/www/.env ]; then
    cp /var/www/.env.example /var/www/.env
fi

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link || true
php artisan scribe:generate || true
service nginx start
exec php-fpm -F