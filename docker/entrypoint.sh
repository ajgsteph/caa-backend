#!/bin/sh
# Entrypoint for the CAA backend container.
#
# Re-builds the Laravel runtime caches at boot so that environment variables
# coming from the platform (Render, Kubernetes…) are picked up — `composer
# install` runs at build time, when those values are not yet known.

set -e

cd /app

# Drop any cache files that were baked into the image (composer post-install).
php artisan config:clear  >/dev/null 2>&1 || true
php artisan route:clear   >/dev/null 2>&1 || true
php artisan view:clear    >/dev/null 2>&1 || true

# Re-cache for production performance. config:cache reads the live env, so it
# must run after the platform injects DB_HOST / APP_KEY / etc.
if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Run database migrations on boot — Render only runs the Dockerfile, there is
# no `compose exec` to invoke them manually. --force is required outside local.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

# Ensure the storage symlink exists in case storage/app/public is a fresh volume.
if [ ! -L public/storage ]; then
    php artisan storage:link >/dev/null 2>&1 || true
fi

exec "$@"
