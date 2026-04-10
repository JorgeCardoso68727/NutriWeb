FROM php:8.0-apache

# Resolver conflito de MPMs
RUN a2dismod mpm_prefork || true
RUN a2dismod mpm_worker || true
RUN a2dismod mpm_event || true
RUN a2enmod mpm_event

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