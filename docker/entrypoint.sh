#!/bin/sh
# Entrypoint for the CAA backend container.
#
# Re-builds the Laravel runtime caches at boot so that environment variables
# coming from docker-compose / Kubernetes are picked up — `composer install`
# runs at build time, when those values are not yet known.
#
# Migrations are intentionally NOT run here. Run them once after `compose up`
# from your host:
#     docker compose exec app php artisan migrate --force
#     docker compose exec app php artisan db:seed --force   # optional
#
# This avoids a race when the `app` and `worker` containers boot in parallel.

set -e

cd /app

# Drop any cache files that were baked into the image (composer post-install).
php artisan config:clear  >/dev/null 2>&1 || true
php artisan route:clear   >/dev/null 2>&1 || true
php artisan view:clear    >/dev/null 2>&1 || true

# Re-cache for production performance. config:cache reads the live env, so it
# must run after compose injects DB_HOST / REDIS_HOST / etc.
if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Ensure the storage symlink exists in case storage/app/public is a fresh volume.
if [ ! -L public/storage ]; then
    php artisan storage:link >/dev/null 2>&1 || true
fi

exec "$@"
