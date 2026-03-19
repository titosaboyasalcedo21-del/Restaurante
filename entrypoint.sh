#!/bin/bash

# Cambiar al directorio de la aplicación
cd /var/www/html

# Generar key si no existe
php artisan key:generate --force

# Ejecutar migraciones
php artisan migrate --force

# Iniciar Apache
apache2-foreground
