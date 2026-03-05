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
if [ ! -f .env ]; then
    cp .env.example .env
    # Override DB settings for devcontainer
    sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mysql/' .env
    sed -i 's/DB_DATABASE=your_database_name/DB_DATABASE=laradashboard/' .env
    sed -i 's/DB_USERNAME=your_database_user/DB_USERNAME=laradashboard/' .env
    sed -i 's/DB_PASSWORD=your_database_password/DB_PASSWORD=secret/' .env
    sed -i 's/REDIS_HOST=127.0.0.1/REDIS_HOST=redis/' .env
    php artisan key:generate --no-interaction
    echo ".env created and configured for devcontainer."
else
    echo ".env already exists, skipping."
fi

echo "==> Linking storage..."
php artisan storage:link --no-interaction

echo "==> Running migrations with seeder..."
php artisan migrate:fresh --seed --no-interaction

echo "==> Building frontend assets..."
npm run build
