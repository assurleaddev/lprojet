#!/bin/bash

set -e

echo "==> Pulling latest code..."
git pull origin main

echo "==> Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Running migrations..."
php artisan migrate --force --no-interaction

echo "==> Clearing and rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> Fixing storage permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "==> Restarting queue workers..."
php artisan queue:restart

echo ""
echo "Done."
