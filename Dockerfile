FROM php:8.2-apache

# Garantir que só um MPM está ativo
RUN a2dismod mpm_prefork mpm_worker || true
RUN a2enmod mpm_event rewrite

# Extensões PHP
RUN docker-php-ext-install pdo pdo_mysql

# Copiar código
COPY . /var/www/html/

WORKDIR /var/www/html/

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --prefer-dist --no-scripts --optimize-autoloader || true

# DocumentRoot
RUN sed -i 's#/var/www/html#/var/www/html/web#g' /etc/apache2/sites-available/000-default.conf

# Railway usa porta 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:8080>/' /etc/apache2/sites-available/000-default.conf

EXPOSE 8080

CMD ["apache2-foreground"]