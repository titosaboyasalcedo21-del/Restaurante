#!/bin/bash

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

# Iniciar Apache
apache2-foreground
