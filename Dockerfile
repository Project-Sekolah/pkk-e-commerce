FROM php:8.4-apache

# Install dependencies sistem dan PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
        gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Aktifkan mod_rewrite
RUN a2enmod rewrite

# Copy project Laravel
COPY . /var/www/html/

WORKDIR /var/www/html/

# PHP upload configuration
COPY config/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Git safe directory
RUN git config --global --add safe.directory /var/www/html

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
        --install-dir=/usr/local/bin \
        --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Pastikan Composer menggunakan PHP yang benar
RUN php -v \
    && php -m \
    && composer --version

# Install dependency Laravel
RUN COMPOSER_ALLOW_SUPERUSER=1 \
    composer install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction

# Siapkan folder Laravel
RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache

# Permission Laravel
RUN chown -R www-data:www-data \
        storage \
        bootstrap/cache \
    && chmod -R 775 \
        storage \
        bootstrap/cache

# Apache menggunakan folder public Laravel
RUN sed -i \
    's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf

# Izinkan .htaccess
RUN sed -i \
    's/AllowOverride None/AllowOverride All/g' \
    /etc/apache2/apache2.conf

# Server name
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Railway health check
HEALTHCHECK \
    --interval=30s \
    --timeout=10s \
    --start-period=30s \
    --retries=3 \
    CMD curl --fail http://localhost/ || exit 1

EXPOSE 80

CMD ["apache2-foreground"]