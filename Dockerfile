FROM php:8.2-fpm

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    mariadb-client \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Trabajar en el directorio del backend
WORKDIR /app/backend

# Copiar archivos
COPY backend/ /app/backend/

# Crear .env con SQLite
RUN echo 'APP_NAME=SGE\nAPP_ENV=production\nAPP_DEBUG=false\nAPP_URL=https://mockup-gestion-backend.onrender.com\nDB_CONNECTION=sqlite\nDB_DATABASE=/app/backend/database/database.sqlite\nCACHE_DRIVER=file\nSESSION_DRIVER=cookie\nQUEUE_CONNECTION=sync\nLOG_CHANNEL=single' > .env

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Generar key
RUN php artisan key:generate || true

# Crear directorio de BD
RUN mkdir -p database && touch database/database.sqlite

# Ejecutar migraciones y seeds
RUN php artisan migrate:fresh --seed --force 2>/dev/null || true

# Cambiar permisos
RUN mkdir -p storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache database

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]


