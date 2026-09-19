FROM php:8.2-fpm

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    mariadb-client \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Trabajar en el directorio del backend
WORKDIR /app/backend

# Copiar archivos
COPY backend/ /app/backend/

# Copiar .env.example desde backend
RUN cp .env.example .env || true

# Instalar dependencias de PHP (con dev para Faker)
RUN composer install --optimize-autoloader --ignore-platform-reqs

# Generar key
RUN php artisan key:generate || true

# Cambiar permisos
RUN mkdir -p storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Copiar script de inicio
COPY start.sh /app/start.sh
RUN chmod +x /app/start.sh

EXPOSE 8000

CMD ["/app/start.sh"]


