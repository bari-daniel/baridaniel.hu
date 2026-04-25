# ---------- FRONTEND BUILD ----------
FROM node:20 AS node-builder
WORKDIR /app
COPY package.json ./
RUN npm install
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

# ---------- PHP APP ----------
# FRISSÍTVE: PHP 8.4 a Laravel 13-hoz
FROM php:8.4-cli

# System deps
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libonig-dev libpng-dev libjpeg-dev libfreetype6-dev libxml2-dev zlib1g-dev libcurl4-openssl-dev curl \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Composer install (fontos: a lock fájlt is másold be!)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# App copy
COPY . .

# Frontend build
COPY --from=node-builder /app/public/build ./public/build

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

# Web szerver indítása
CMD ["sh", "-c", "mkdir -p /var/www/html/storage/framework/views /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions && chown -R www-data:www-data /var/www/html/storage && chmod -R 775 /var/www/html/storage && php artisan serve --host=0.0.0.0 --port=8080"]