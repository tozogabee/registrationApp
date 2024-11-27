FROM php:8.1-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy application files
COPY ./app /var/www/html/

# Set permissions for the Apache user
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

