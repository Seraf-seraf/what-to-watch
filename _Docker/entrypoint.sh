#!/bin/sh

composer install
cron &
php artisan migrate

exec supervisord -c /etc/supervisor/supervisor.ini
