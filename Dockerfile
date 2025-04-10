FROM php:8.2-apache

# Enable mod_rewrite (optional, for .htaccess support)
RUN a2enmod rewrite

# Copy your app files into Apache's root directory
COPY . /var/www/html/

# Set permissions (optional but recommended)
WORKDIR /var/www/html/
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
