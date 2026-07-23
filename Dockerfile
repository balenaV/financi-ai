FROM node:22-bookworm-slim AS node

FROM composer:2 AS composer

FROM php:8.5-fpm-bookworm

ARG APP_UID=1000
ARG APP_GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libonig-dev \
        libpq-dev \
        libsqlite3-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        intl \
        mbstring \
        pcntl \
        pdo_pgsql \
        pdo_sqlite \
        pgsql \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY --from=node /usr/local/ /usr/local/

RUN groupmod -o -g "${APP_GID}" www-data \
    && usermod -o -u "${APP_UID}" -g www-data www-data

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini

USER www-data

CMD ["php-fpm"]
