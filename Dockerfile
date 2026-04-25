# ---------- FRONTEND BUILD ----------
FROM node:20 AS node-builder
WORKDIR /app
COPY package.json ./
RUN npm install
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

# ---------- PHP APP ----------
FROM php:8.3-cli

# Rendszerfüggőségek telepítése
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

# PHP kiterjesztések
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache

# Composer telepítése
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Composer csomagok telepítése
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# App forráskód másolása
COPY . .

# Frontend build másolása
COPY --from=node-builder /app/public/build ./public/build

# Jogosultságok beállítása (fontos a fájlrendszer írásához)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Port beállítása
EXPOSE 8080

# Alkalmazás indítása a Laravel beépített szerverével
# A --host=0.0.0.0 elengedhetetlen a Railway-en belüli eléréshez
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]