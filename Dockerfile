FROM php:8.2-apache

# Instalar certificados CA, activar rewrite y extensiones PDO
RUN apt-get update && apt-get install -y \
    ca-certificates \
    && update-ca-certificates \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

# Copiar todo a la raíz de Apache
COPY . /var/www/html/

# Asegurar permisos
RUN chown -R www-data:www-data /var/www/html