#FROM php:8.1-apache

# Install mysqli extension
#RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

#RUN apt-get update && apt-get install -y \
#    unzip \
#    git \
#    libzip-dev \
#    && docker-php-ext-install zip

# Install PHP dependencies using Composer
#RUN composer install --no-dev --optimize-autoloader

# Copy application files
#COPY ./app /var/www/html/

# Set permissions for the Apache user
#RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html
# Use PHP Apache base image
# Use PHP Apache base image
# Use PHP Apache base image
FROM php:8.2-fpm

#RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    && docker-php-ext-install mysqli zip

RUN docker-php-ext-enable mysqli

#RUN a2enmod rewrite

RUN apt-get update && apt-get install -y nginx



# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer.json and composer.lock to the container
COPY composer.json ./

# Run composer install to set up dependencies
RUN composer install

# Copy the rest of the application code
COPY ./app /var/www/html/

# Set permissions for the Apache user
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80



