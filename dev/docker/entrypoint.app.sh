#!/bin/bash

set -e

env

if [[ -n "$1" ]]; then
    exec "$@"
else
    composer install
    php artisan migrate
    exec apache2-foreground
fi
