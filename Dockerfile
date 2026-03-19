FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    git \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar Composer
ENV COMPOSER_HOME=/tmp/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Instalar extensiones necesarias de PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Copiar toda la aplicación
COPY restaurante-mvc /var/www/html

# Instalar dependencias de Composer con output detallado
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-interaction --verbose --prefer-dist || echo "COMPOSER FAILED"

# Verificar que vendor existe
RUN ls -la /var/www/html/vendor/ || echo "VENDOR NOT FOUND AFTER COMPOSER"

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Copiar script de entrada
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Puerto para Apache
EXPOSE 80

# Configurar Apache para servir desde public
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && echo "<Directory /var/www/html/public>\n    AllowOverride All\n    Options -Indexes +FollowSymLinks\n</Directory>" >> /etc/apache2/apache2.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

ENTRYPOINT ["/entrypoint.sh"]
