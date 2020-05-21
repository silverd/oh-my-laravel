#!/bin/sh

chmod -R 777 storage
chmod -R 777 bootstrap/cache

/etc/init.d/php-fpm reload

php artisan migrate --force
