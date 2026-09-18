#!/bin/bash

# Build script para Render.com - Backend Laravel

echo "==> Instalando dependencias de Composer..."
cd backend

# Instalar composer si no existe
if ! command -v composer &> /dev/null; then
    echo "==> Descargando Composer..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"
fi

echo "==> Ejecutando composer install..."
composer install --no-dev --optimize-autoloader

echo "==> Copiando .env.example a .env..."
cp ../.env.example .env

echo "==> Generando APP_KEY..."
php artisan key:generate

echo "==> Cacheando configuración..."
php artisan config:cache
php artisan route:cache

echo "==> Build completado exitosamente!"
