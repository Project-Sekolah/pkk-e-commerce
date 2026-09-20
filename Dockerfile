FROM php:8.3-apache

# Install PHP extensions
RUN apt-get update && apt-get install -y unzip git libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql zip gd

# Enable mod_rewrite
RUN a2enmod rewrite

# Copy application to container
COPY . /var/www/html/

# Apply upload limits inside the PHP runtime as well as the project config.
COPY config/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Set working directory
WORKDIR /var/www/html/

RUN git config --global --add safe.directory /var/www/html

# Install composer and production dependencies
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && composer install --no-dev --optimize-autoloader

# Storage & cache permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Change DocumentRoot to /public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Allow .htaccess override
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

HEALTHCHECK --interval=30s --timeout=10s --start-period=10s --retries=3 \
  CMD curl --fail http://localhost || exit 1

EXPOSE 80

CMD ["apache2-foreground"]

