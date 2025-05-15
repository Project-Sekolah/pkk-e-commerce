FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN a2enmod rewrite

COPY . /var/www/html/

# Ubah DocumentRoot Apache ke /public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Izinkan penggunaan .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Tambahkan ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Set working directory (optional)
WORKDIR /var/www/html/public

EXPOSE 80

CMD ["apache2-foreground"]
