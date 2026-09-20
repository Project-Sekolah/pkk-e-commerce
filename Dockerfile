# ============================================================
# FRONTEND BUILD
# ============================================================

FROM node:22-alpine AS frontend

WORKDIR /build

COPY package*.json ./

RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./

RUN npm run build


# ============================================================
# COMPOSER DEPENDENCIES
# ============================================================

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


# ============================================================
# PRODUCTION
# ============================================================

FROM php:8.4-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

WORKDIR /var/www/html


# ============================================================
# SYSTEM DEPENDENCIES + PHP EXTENSIONS
# ============================================================

RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        gd \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
    && rm -rf /var/lib/apt/lists/*


# ============================================================
# APACHE MPM
# Hanya mpm_prefork yang digunakan oleh PHP Apache
# ============================================================

RUN a2dismod mpm_event 2>/dev/null || true \
    && a2dismod mpm_worker 2>/dev/null || true \
    && a2dismod mpm_mpmt 2>/dev/null || true \
    && a2dismod mpm_prefork 2>/dev/null || true \
    && a2enmod mpm_prefork \
    && a2enmod rewrite


# ============================================================
# LARAVEL APPLICATION
# ============================================================

COPY --from=vendor /app/vendor ./vendor

COPY . .

COPY --from=frontend /build/public/build ./public/build


# ============================================================
# PHP CONFIGURATION
# ============================================================

COPY config/php/uploads.ini \
    /usr/local/etc/php/conf.d/uploads.ini


# ============================================================
# APACHE DOCUMENT ROOT
# ============================================================

RUN sed -i \
        's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
        /etc/apache2/sites-available/000-default.conf \
    && sed -i \
        's/AllowOverride None/AllowOverride All/g' \
        /etc/apache2/apache2.conf \
    && echo 'ServerName localhost' >> /etc/apache2/apache2.conf


# ============================================================
# LARAVEL DIRECTORIES
# ============================================================

RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R ug+rwX \
        storage \
        bootstrap/cache


# ============================================================
# ENTRYPOINT
# ============================================================

COPY docker-entrypoint.sh \
    /usr/local/bin/docker-entrypoint

RUN chmod +x /usr/local/bin/docker-entrypoint


# ============================================================
# HEALTHCHECK
# ============================================================

HEALTHCHECK \
    --interval=30s \
    --timeout=10s \
    --start-period=30s \
    --retries=3 \
    CMD curl --fail http://localhost:${PORT:-8080}/ || exit 1


# ============================================================
# PORT
# ============================================================

EXPOSE 8080


# ============================================================
# START CONTAINER
# ============================================================

ENTRYPOINT ["docker-entrypoint"]

CMD ["apache2-foreground"]