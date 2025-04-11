FROM php:8.2-apache

# Install PDO MySQL driver
RUN docker-php-ext-install pdo pdo_mysql

# Enable mod_rewrite
RUN a2enmod rewrite

# Create persistent session folder
RUN mkdir -p /var/www/html/sessions && chmod 777 /var/www/html/sessions

# Copy custom PHP config
COPY php.ini /usr/local/etc/php/conf.d/

# Copy app code
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
