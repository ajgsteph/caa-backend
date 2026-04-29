#!/bin/sh

echo "Running Laravel setup..."

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

# Générer la doc uniquement si nécessaire
php artisan scribe:generate || true

echo "Starting services..."
