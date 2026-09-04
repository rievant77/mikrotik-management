# ==========================================
# Stage 1: Frontend Asset Builder
# ==========================================
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY vite.config.js tailwind.config.js* postcss.config.js* ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# ==========================================
# Stage 2: Composer Dependency Builder
# ==========================================
FROM composer:2 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev

# ==========================================
# Stage 3: Production Runtime (PHP-FPM + Nginx + SQLite)
# ==========================================
FROM php:8.2-fpm-alpine

LABEL maintainer="MikroTik Hotspot Manager"

# Install System Packages, Nginx, Supervisor, SQLite & Runtime Libraries
RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    curl \
    tzdata \
    ca-certificates \
    freetype \
    libjpeg-turbo \
    libpng \
    libzip \
    icu-libs \
    oniguruma \
    sqlite-libs

# Install Build Dependencies & Compile PHP Extensions
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    linux-headers \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    sqlite-dev \
    zlib-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_sqlite \
        gd \
        zip \
        mbstring \
        intl \
        bcmath \
        opcache \
        sockets \
        pcntl \
    && apk del .build-deps

# Set working directory
WORKDIR /var/www/html

# Copy application source code & built vendor from composer stage
COPY --from=composer-builder /app /var/www/html

# Copy built frontend assets from node stage
COPY --from=node-builder /app/public/build /var/www/html/public/build

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/entrypoint.sh /docker/entrypoint.sh

RUN chmod +x /docker/entrypoint.sh

# Create necessary runtime directories and set ownership
RUN mkdir -p /var/www/html/database \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/public/uploads \
    /var/log/supervisor \
    /run/nginx \
    /var/log/nginx \
    /var/lib/nginx/tmp \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public/uploads /run/nginx /var/log/nginx /var/lib/nginx /var/log/supervisor \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public/uploads /run/nginx /var/log/nginx /var/lib/nginx /var/log/supervisor

# Expose HTTP port
EXPOSE 80

# Entrypoint script handles migrations & permissions on start
ENTRYPOINT ["/docker/entrypoint.sh"]

# Start Supervisord (runs Nginx, PHP-FPM, and Laravel Scheduler)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
