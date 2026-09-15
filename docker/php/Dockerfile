# ==========================================
# STAGE 1: Build Frontend Assets (Vite & Tailwind)
# ==========================================
FROM node:20-alpine AS frontend
WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY resources ./resources
COPY vite.config.js ./
COPY public ./public
RUN npm run build

# ==========================================
# STAGE 2: PHP-FPM Production Runtime
# ==========================================
FROM php:8.3-fpm-alpine

# Install system dependencies & PHP extensions (termasuk curl, xml, sockets)
RUN apk update && apk add --no-cache \
    git \
    curl \
    curl-dev \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    libxml2-dev \
    icu-dev \
    oniguruma-dev \
    mysql-client \
    linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql mbstring exif pcntl bcmath gd zip intl sockets curl xml \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# 1. Salin seluruh source code aplikasi
COPY . .

# 2. Salin hasil compile Vite dari Stage 1 & simpan backup di /opt/build
COPY --from=frontend /app/public/build /opt/build
COPY --from=frontend /app/public/build /var/www/html/public/build

# 3. Install dependensi Composer & simpan backup vendor di /opt/vendor
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-reqs \
    && cp -r vendor /opt/vendor \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 4. Entrypoint script
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
