#!/bin/sh
set -e

echo "==> Starting MikroTik Management Container..."

# 1. Ensure .env exists
if [ ! -f /var/www/html/.env ]; then
    echo "==> Creating .env from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# 2. Ensure SQLite database directory & file exist
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    echo "==> Initializing SQLite database file..."
    touch /var/www/html/database/database.sqlite
fi

# 3. Ensure upload & cache directories exist
mkdir -p /var/www/html/public/uploads/avatars
mkdir -p /var/www/html/public/uploads/branding
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs

# 4. Generate Application Key if not present
if ! grep -q "APP_KEY=base64:" /var/www/html/.env; then
    echo "==> Generating Application Key..."
    php /var/www/html/artisan key:generate --force
fi

# 5. Run Database Migrations
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "==> Checking MySQL connection..."
    max_tries=30
    count=0
    until php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';port='.(getenv('DB_PORT') ?: 3306).';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch(Exception \$e) { exit(1); }" 2>/dev/null || [ $count -ge $max_tries ]; do
        echo "==> Waiting for MySQL database to become available ($count/$max_tries)..."
        sleep 2
        count=$((count+1))
    done
fi

echo "==> Running database migrations..."
php /var/www/html/artisan migrate --force

# 6. Ensure Storage Symlink
php /var/www/html/artisan storage:link || true

# 7. Optimize Cache in Production
if [ "$APP_ENV" = "production" ]; then
    echo "==> Optimizing configuration & route cache..."
    php /var/www/html/artisan config:cache || true
    php /var/www/html/artisan route:cache || true
    php /var/www/html/artisan view:cache || true
fi

# 8. Set File Permissions
echo "==> Adjusting permissions for www-data..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public/uploads
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public/uploads

echo "==> Application ready! Launching Supervisord..."
exec "$@"
