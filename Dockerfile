FROM dunglas/frankenphp:1-php8.4-bookworm AS base

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && install-php-extensions intl zip pdo_mysql

WORKDIR /app

# Build frontend assets (Vite + Tailwind).
# public/build is gitignored, so the image must build it itself.
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# Install PHP dependencies and prepare the application
FROM base AS dependencies

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist

COPY . .

# Remove stale dev-only bootstrap cache (e.g. Laravel\Pail\PailServiceProvider)
# so Laravel re-discovers production packages at runtime without DB access.
RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php

RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && composer dump-autoload --no-dev --no-scripts

# Runtime image
FROM base AS runtime

COPY --from=dependencies --chown=www-data:www-data /app /app
COPY --from=assets --chown=www-data:www-data /app/public/build /app/public/build
COPY --chown=www-data:www-data Caddyfile /etc/frankenphp/Caddyfile

RUN setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
    && chown -R www-data:www-data /config/caddy /data/caddy

# CACHE_STORE / SESSION_DRIVER hit Postgres (Supabase), so they need DB_* env
# vars from Vercel. Override them on Vercel if the app outgrows the defaults.
ENV PORT=80 \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    QUEUE_CONNECTION=sync \
    SESSION_DRIVER=database \
    CACHE_STORE=database

USER www-data

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
