FROM php:8.2-apache

# Instalar dependencias y activar el módulo de reescritura de Apache
RUN apt-get update && apt-get install -y ca-certificates \
    && update-ca-certificates \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

# Copiar archivos
COPY . /var/www/html/

# Asegurar permisos
RUN chown -R www-data:www-data /var/www/html