# CAA backend — FrankenPHP (Caddy + PHP) single-binary image
#
#   Stage 1 (deps)    : install PHP extensions + composer dependencies
#   Stage 2 (runtime) : minimal image with the compiled extensions and the app

ARG PHP_VERSION=8.3
ARG FRANKENPHP_VERSION=1

# ---------------------------------------------------------------------------
# Base layer: FrankenPHP + PHP extensions required by Laravel + project libs
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION} AS base

# install-php-extensions handles dependencies + cleanup automatically.
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        redis \
        zip

# Composer (kept available in the runtime image so artisan tinker / debug works,
# but the final vendor/ is built only once in the deps stage below).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# ---------------------------------------------------------------------------
# Stage 1: install PHP dependencies (production only)
# ---------------------------------------------------------------------------
FROM base AS deps

# Install vendor first using only composer.* so the layer caches well.
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --no-autoloader \
        --prefer-dist

# Copy the rest of the application and finalize the autoloader.
COPY . .
RUN composer dump-autoload --classmap-authoritative --no-dev \
 && composer run-script post-autoload-dump --no-dev || true

# ---------------------------------------------------------------------------
# Stage 2: runtime image
# ---------------------------------------------------------------------------
FROM base AS runtime

ENV APP_ENV=production \
    APP_DEBUG=false \
    SERVER_NAME=":80"

# Production-tuned PHP configuration.
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php-prod.ini /usr/local/etc/php/conf.d/zz-app.ini

# Caddy / FrankenPHP config (serves Laravel public/ via php_server).
COPY docker/Caddyfile /etc/caddy/Caddyfile

# Bring in the built application.
COPY --from=deps --chown=www-data:www-data /app /app

# Make sure the runtime-writable directories exist and are owned by www-data.
RUN mkdir -p \
        storage/app/public \
        storage/app/private \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && rm -f public/storage \
 && ln -s ../storage/app/public public/storage

# Entrypoint warms the config/view cache before handing off to Caddy or to a
# queue:work / scheduler command.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

# 80 = HTTP, 443 + 443/udp = HTTPS / HTTP3 (FrankenPHP can auto-issue certs).
EXPOSE 80 443 443/udp

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
