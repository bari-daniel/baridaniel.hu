#!/bin/sh
set -e

cd /var/www/html

if [ -z "${APP_KEY}" ]; then
  php artisan key:generate --ansi --force
fi

php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
