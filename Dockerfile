# ---------- FRONTEND BUILD ----------
FROM node:20 AS node-builder

WORKDIR /app

COPY package.json ./
RUN npm install

COPY resources ./resources
COPY vite.config.js ./

RUN npm run build


# ---------- PHP APP ----------
FROM php:8.3-fpm

# System deps
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    zlib1g-dev \
    libcurl4-openssl-dev \
    curl \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis

# Opcache (fontos Laravelhez)
RUN docker-php-ext-install opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Composer cache optimalizálás
COPY composer.json ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --prefer-dist \
    --no-interaction \
    --no-scripts

# App copy
COPY . .

# Frontend build copy
COPY --from=node-builder /app/public/build ./public/build

# Laravel optimalizálás
RUN php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]