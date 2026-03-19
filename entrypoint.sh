#!/bin/bash

# Puerto para Render (variable $PORT o 10000 por defecto)
PORT=${PORT:-10000}

# Cambiar al directorio de la aplicación
cd /var/www/html

# Instalar dependencias de Composer si composer.json existe
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Generar key si no existe
php artisan key:generate --force

# Ejecutar migraciones
php artisan migrate --force

# Iniciar servidor Laravel en el puerto de Render
php artisan serve --host=0.0.0.0 --port=$PORT
