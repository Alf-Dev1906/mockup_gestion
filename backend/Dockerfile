# Dockerfile para Laravel en Render
FROM php:8.2-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configurar permisos
RUN chmod -R 775 storage bootstrap/cache

# Exponer puerto
EXPOSE 8080

# Variables de entorno para PHP
ENV MAX_EXECUTION_TIME=900
ENV MEMORY_LIMIT=512M

# Comando de inicio
CMD php -d max_execution_time=900 -d memory_limit=512M artisan serve --host=0.0.0.0 --port=8080
