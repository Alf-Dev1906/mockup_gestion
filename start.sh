#!/bin/bash
set -e

echo "🚀 Iniciando aplicación Laravel..."

# Ejecutar migraciones
echo "📦 Ejecutando migraciones..."
php artisan migrate --force

# Ejecutar seeders
echo "🌱 Ejecutando seeders..."
php artisan db:seed --force

# Limpiar cache
echo "🧹 Limpiando cache..."
php artisan config:cache
php artisan route:cache

# Iniciar servidor
echo "✅ Iniciando servidor en puerto 8000..."
php artisan serve --host=0.0.0.0 --port=8000
