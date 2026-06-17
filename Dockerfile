# Dockerfile
FROM php:8.2-apache

# Habilitar mod_rewrite para rutas amigables
RUN a2enmod rewrite

# Instalar extensiones de base de datos
RUN docker-php-ext-install pdo pdo_mysql

# Copiar el código fuente al directorio del servidor web
COPY . /var/www/html/

# Asegurar permisos correctos
RUN chown -R www-data:www-data /var/www/html