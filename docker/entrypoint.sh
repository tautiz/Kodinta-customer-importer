#!/bin/sh
set -eu

if [ ! -f .env ]; then
    cp .env.example .env
fi

composer install --no-interaction --prefer-dist --optimize-autoloader

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

if [ -f package-lock.json ]; then
    npm ci --no-audit --no-fund
else
    npm install --no-audit --no-fund
fi

npm run build
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=8000
