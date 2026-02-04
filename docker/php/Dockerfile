FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first
COPY composer.json composer.lock* ./

# Install dependencies (without scripts)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-req=php 2>/dev/null || composer install --no-interaction --prefer-dist --ignore-platform-req=php || true

# Copy the rest of the application
COPY . .

# Run post-install scripts
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-req=php 2>/dev/null || true

# Set permissions
RUN chown -R www-data:www-data /var/www

EXPOSE 9000
CMD ["php-fpm"]
