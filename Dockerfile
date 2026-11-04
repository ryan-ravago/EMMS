# syntax=docker/dockerfile:1.7
#
# EMMS image, one Dockerfile, three runtime targets:
#   app  - PHP-FPM (also used for the queue worker, scheduler and migrations)
#   web  - Caddy: HTTPS, static files, hands PHP requests to the app container
#
# Build:  docker build --target app -t emms-app .   |   docker build --target web -t emms-web .
# (compose.testing.yaml / compose.production.yaml build both for you)

ARG PHP_VERSION=8.3.36
ARG NODE_VERSION=22
ARG CADDY_VERSION=2.10

# ---------------------------------------------------------------------------
# PHP base: the extensions the app needs and nothing else
# ---------------------------------------------------------------------------
FROM php:${PHP_VERSION}-fpm-alpine AS php-base

COPY --from=ghcr.io/mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

# bcmath, gd (PDF images), intl (Filament), opcache, pcntl (queue worker signals), pdo_mysql, zip.
# fcgi is only for the php-fpm health check; tzdata so the TZ variable works.
RUN apk add --no-cache fcgi tzdata \
    && install-php-extensions bcmath gd intl opcache pcntl pdo_mysql zip \
    && rm -rf /usr/local/bin/install-php-extensions /tmp/* /var/cache/apk/* \
    # The image ships php.ini templates and pool files we replace below.
    && rm -f /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf.default /usr/local/etc/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/docker.conf \
    && rm -f /usr/local/etc/php/conf.d/docker-php-ext-sodium.ini.disabled

WORKDIR /var/www/html

# ---------------------------------------------------------------------------
# Composer dependencies (production only) and the generated autoloader
# ---------------------------------------------------------------------------
FROM php-base AS vendor

COPY --from=composer:2.8 /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_NO_INTERACTION=1 COMPOSER_MEMORY_LIMIT=-1

# Dependencies first, so this layer is reused until composer.lock changes.
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .

# google/apiclient-services ships all 650+ Google APIs (about 200 MB). EMMS uses Gmail and Sheets only.
# If the code starts using another Google API, add its name here (e.g. "Gmail Sheets Drive").
ARG GOOGLE_SERVICES="Gmail Sheets"
RUN set -eu; cd vendor/google/apiclient-services/src; \
    for entry in *; do \
        name="${entry%.php}"; \
        case " ${GOOGLE_SERVICES} " in *" ${name} "*) ;; *) rm -rf -- "${entry}" ;; esac; \
    done; \
    ls

# Scripts are skipped above because they boot the app; run them now with throwaway settings (nothing is stored in the image).
RUN composer dump-autoload --no-dev --classmap-authoritative --no-scripts \
    && APP_ENV=production APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
       CACHE_STORE=array SESSION_DRIVER=array QUEUE_CONNECTION=sync DB_CONNECTION=sqlite DB_DATABASE=:memory: \
       sh -c 'php artisan package:discover --ansi && php artisan filament:assets --ansi && php artisan vendor:publish --tag=laravel-assets --ansi --force' \
    && rm -rf storage/framework/views/*

# ---------------------------------------------------------------------------
# Front-end assets
# ---------------------------------------------------------------------------
FROM node:${NODE_VERSION}-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---------------------------------------------------------------------------
# app: PHP-FPM / queue / scheduler. Runs as www-data (uid 82), no shell tools beyond Alpine's.
# ---------------------------------------------------------------------------
FROM php-base AS app

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint && chmod +x /usr/local/bin/entrypoint

# Only what runs: no tests, docs, node tooling, dumps or env files (see .dockerignore).
COPY --from=vendor --chown=www-data:www-data /var/www/html /var/www/html
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Writable places only: storage (a volume in compose) and bootstrap/cache (a tmpfs in compose).
RUN mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R u+rwX,g+rX,o-rwx storage bootstrap/cache

ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr

USER www-data
EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -q pong || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm", "--nodaemonize"]

# ---------------------------------------------------------------------------
# web: Caddy with the public folder baked in (static files are served without touching PHP)
# ---------------------------------------------------------------------------
FROM caddy:${CADDY_VERSION}-alpine AS web

COPY docker/caddy/Caddyfile /etc/caddy/Caddyfile
COPY docker/caddy/tls /etc/caddy/tls

# Only the public folder; the app code never reaches this image.
COPY --from=app /var/www/html/public /var/www/html/public
RUN rm -f /var/www/html/public/.htaccess

EXPOSE 80 443 443/udp
