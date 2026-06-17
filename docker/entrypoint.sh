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

# Les commandes artisan ci-dessus tournent en root et créent des fichiers
# (laravel.log, caches) appartenant à root ; php-fpm tourne en www-data et ne
# pourrait plus écrire les logs (→ 500). On rétablit les droits avant de démarrer.
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

service nginx start
exec php-fpm -F