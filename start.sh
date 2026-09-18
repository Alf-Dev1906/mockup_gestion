#!/bin/bash
set -e

echo "🚀 Iniciando aplicación Laravel..."

# Ejecutar migraciones
echo "📦 Ejecutando migraciones..."
php artisan migrate --force

# NO ejecutar seeders en producción (requieren Faker que no está en --no-dev)
# Para datos de prueba, ejecutar manualmente: php artisan db:seed

# Limpiar cache
echo "🧹 Limpiando cache..."
php artisan config:cache
php artisan route:cache

# Iniciar servidor
echo "✅ Iniciando servidor en puerto 8000..."
php artisan serve --host=0.0.0.0 --port=8000
