#!/usr/bin/env bash
set -e

echo "==> Fixing workspace permissions..."
sudo chown -R $(id -u):$(id -g) /var/www/html

echo "==> Configuring git safe directory..."
git config --global --add safe.directory /var/www/html

echo "==> Installing Composer dependencies..."
composer install --no-interaction

echo "==> Installing NPM dependencies..."
npm install

echo "==> Setting up .env..."
FRESH_INSTALL=false
if [ ! -f .env ]; then
    FRESH_INSTALL=true
    cp .env.example .env
    # Load credentials from .env.devcontainer
    source .devcontainer/.env.devcontainer
    # Override DB settings for devcontainer
    sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mysql/' .env
    sed -i "s/DB_DATABASE=your_database_name/DB_DATABASE=${DB_DATABASE}/" .env
    sed -i "s/DB_USERNAME=your_database_user/DB_USERNAME=${DB_USERNAME}/" .env
    sed -i "s/DB_PASSWORD=your_database_password/DB_PASSWORD=${DB_PASSWORD}/" .env
    sed -i 's/REDIS_HOST=127.0.0.1/REDIS_HOST=redis/' .env
    echo "VITE_DEV_HOST=0.0.0.0" >> .env
    php artisan key:generate --no-interaction
    echo ".env created and configured for devcontainer."
else
    echo ".env already exists, skipping."
fi

echo "==> Linking storage..."
php artisan storage:link --no-interaction

echo "==> Running migrations..."
php artisan migrate --no-interaction

if [ "$FRESH_INSTALL" = true ]; then
    echo "==> Seeding database..."
    php artisan db:seed --no-interaction
fi

echo "==> Building frontend assets..."
npm run build
