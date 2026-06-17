FROM php:8.2-apache

# Instalar extensiones y certificados SSL necesarios para Aiven
RUN apt-get update && apt-get install -y \
    libpng-dev \
    ca-certificates \
    && update-ca-certificates \
    && docker-php-ext-install pdo pdo_mysql

# Copiar todo el contenido de la carpeta actual al directorio web de Apache
COPY . /var/www/html/

# Asegurar permisos correctos
RUN chown -R www-data:www-data /var/www/html