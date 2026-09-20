FROM php:8.4-apache

# ============================================================
# Install system dependencies & PHP extensions
# ============================================================
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

# ============================================================
# Enable Apache mod_rewrite
# ============================================================
RUN a2enmod rewrite

# ============================================================
# Copy Laravel application
# ============================================================
COPY . /var/www/html/

# ============================================================
# PHP upload configuration
# ============================================================
COPY config/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# ============================================================
# Working directory
# ============================================================
WORKDIR /var/www/html/

# ============================================================
# Git safe directory
# ============================================================
RUN git config --global --add safe.directory /var/www/html

# ============================================================
# Install Composer
# ============================================================
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
        --install-dir=/usr/local/bin \
        --filename=composer \
    && php -r "unlink('composer-setup.php');"

# ============================================================
# Install Laravel dependencies
# ============================================================
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# ============================================================
# Laravel storage & cache permissions
# ============================================================
RUN mkdir -p \
        /var/www/html/storage/framework/cache \
        /var/www/html/storage/framework/sessions \
        /var/www/html/storage/framework/views \
        /var/www/html/storage/logs \
        /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache \
    && chmod -R 775 \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache

# ============================================================
# Configure Apache DocumentRoot -> Laravel /public
# ============================================================
RUN sed -i \
    's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf

# ============================================================
# Allow .htaccess override
# ============================================================
RUN sed -i \
    's/AllowOverride None/AllowOverride All/g' \
    /etc/apache2/apache2.conf

# ============================================================
# Apache ServerName
# ============================================================
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# ============================================================
# Railway health check
# ============================================================
HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD curl --fail http://localhost/ || exit 1

# ============================================================
# Expose Apache
# ============================================================
EXPOSE 80

# ============================================================
# Start Apache
# ============================================================
CMD ["apache2-foreground"]