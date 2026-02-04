FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Configure PHP-FPM with more worker processes and request timeout
RUN mkdir -p /usr/local/etc/php-fpm.d && \
    echo '[www]' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'pm = dynamic' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'pm.max_children = 20' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'pm.start_servers = 5' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'pm.min_spare_servers = 2' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'pm.max_spare_servers = 10' >> /usr/local/etc/php-fpm.d/www.conf && \
    echo 'request_terminate_timeout = 300' >> /usr/local/etc/php-fpm.d/www.conf

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy existing application directory contents
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-req=php

# Change ownership
RUN chown -R www-data:www-data /app

EXPOSE 9000

CMD ["php-fpm"]
