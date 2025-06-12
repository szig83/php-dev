#!/bin/bash
# Indítsuk el a PHP-FPM-et és az Nginx-et
php-fpm -D
nginx -g "daemon off;"