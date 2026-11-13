#!/bin/sh
# Runs before every EMS container (php-fpm, queue worker, scheduler, migrations).
set -eu

cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY is not set. Generate one with: docker compose run --rm --no-deps -e APP_KEY=x app php artisan key:generate --show" >&2
    exit 1
fi

if [ "${APP_ENV:-production}" = "production" ] && [ "${APP_DEBUG:-false}" = "true" ]; then
    echo "Refusing to start: APP_DEBUG=true in production would show stack traces and settings to visitors." >&2
    exit 1
fi

# The storage volume is empty the first time it is used.
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs

# Settings come from the environment, so the caches are built when the container starts (a few hundred milliseconds),
# not when the image is built. bootstrap/cache is a tmpfs, so every container builds its own.
if [ "${SKIP_OPTIMIZE:-0}" != "1" ]; then
    php artisan optimize --no-interaction
    php artisan filament:optimize --no-interaction || echo "filament:optimize skipped" >&2
fi

exec "$@"
