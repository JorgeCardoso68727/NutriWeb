FROM php:8.0-apache

# Ativar rewrite
RUN a2enmod rewrite

# Extensões PHP
RUN docker-php-ext-install pdo pdo_mysql

# Copiar código
COPY . /var/www/html/
WORKDIR /var/www/html/

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --prefer-dist --no-scripts --optimize-autoloader || true

CMD ["apache2-foreground"]