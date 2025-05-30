FROM php:8.2-apache

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable mod_rewrite
RUN a2enmod rewrite

# Copy aplikasi ke container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Install dependencies tambahan untuk composer dan ekstensi zip
RUN apt-get update && apt-get install -y unzip git libzip-dev \
    && docker-php-ext-install zip


RUN git config --global --add safe.directory /var/www/html

# Install composer dan dependencies
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && composer install --no-dev --optimize-autoloader

# Ubah DocumentRoot ke /public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Izinkan .htaccess override
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Tambahkan ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

HEALTHCHECK --interval=30s --timeout=10s --start-period=10s --retries=3 \
  CMD curl --fail http://localhost || exit 1

EXPOSE 80

CMD ["apache2-foreground"]
