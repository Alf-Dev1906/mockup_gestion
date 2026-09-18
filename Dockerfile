FROM php:8.2-fpm

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    mysql-client \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Trabajar en el directorio del backend
WORKDIR /app/backend

# Copiar archivos
COPY backend /app/backend
COPY .env.example /app/backend/.env

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Generar key
RUN php artisan key:generate

# Cambiar permisos
RUN mkdir -p storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Ejecutar migraciones y seeds (esto se puede comentar si hay errores)
# RUN php artisan migrate:fresh --seed --force 2>/dev/null || true

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

