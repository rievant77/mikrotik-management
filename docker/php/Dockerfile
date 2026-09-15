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
# STAGE 2: FrankenPHP Production Runtime (PHP 8.4)
# ==========================================
FROM dunglas/frankenphp:1-php8.4-alpine

ENV SERVER_NAME=":80"
ENV COMPOSER_ALLOW_SUPERUSER=1

# Pasang ekstensi PHP lengkap via installer resmi FrankenPHP
RUN install-php-extensions \
    pdo_mysql \
    sockets \
    gd \
    zip \
    intl \
    bcmath \
    pcntl \
    exif \
    curl \
    xml

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 1. Salin seluruh source code aplikasi
COPY . .

# 2. Salin hasil compile Vite dari Stage 1 & simpan backup di /opt/build
COPY --from=frontend /app/public/build /opt/build
COPY --from=frontend /app/public/build /app/public/build

# 3. Install dependensi Composer & simpan backup vendor di /opt/vendor
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-reqs \
    && cp -r vendor /opt/vendor \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# 4. Entrypoint script
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
