#!/bin/sh

echo "Running Laravel setup..."

# Aller dans le dossier Laravel
cd /var/www

# Générer la clé si pas définie
php artisan key:generate --force

# Lancer les migrations
php artisan migrate --force


php artisan migrate --force

# Générer la doc uniquement si nécessaire
php artisan scribe:generate || true

# Démarrer nginx en arrière-plan
service nginx start

# Démarrer php-fpm au premier plan (garde le container vivant)
php-fpm -F

echo "Starting services..."
