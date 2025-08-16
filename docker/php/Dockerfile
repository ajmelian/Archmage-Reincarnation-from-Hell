FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \    git zip unzip libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \    libzip-dev libonig-dev libxml2-dev default-mysql-client \ && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \ && docker-php-ext-install gd mysqli pdo pdo_mysql zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Optional dev deps install (safe if composer.json not present)
RUN [ -f composer.json ] && composer install --no-interaction || true

# PHP-FPM config tweaks (optional)
