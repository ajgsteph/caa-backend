# Base PHP
FROM php:8.4-fpm

# Installer dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www

# Copier le projet
COPY . .

# Installer les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Permissions Laravel
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Copier config Nginx
COPY docker/nginx/default.conf /etc/nginx/sites-available/default

# Exposer port (Render utilise 8000 souvent)
EXPOSE 8000

# Script de démarrage
CMD service nginx start && php-fpm