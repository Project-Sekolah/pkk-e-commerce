FROM node:22-alpine AS frontend

WORKDIR /build
COPY package*.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js .
RUN npm run build

FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-req=ext-mysqli \
    --ignore-platform-req=ext-pdo_mysql

FROM php:8.4-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd pdo pdo_mysql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=frontend /build/public/build ./public/build
COPY config/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint

RUN sed -i \
        's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
        /etc/apache2/sites-available/000-default.conf \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf \
    && chmod +x /usr/local/bin/docker-entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD curl --fail http://localhost/ || exit 1

EXPOSE 80
ENTRYPOINT ["docker-entrypoint"]
CMD ["apache2-foreground"]