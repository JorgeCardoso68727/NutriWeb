FROM php:8.2-apache

# Instalar dependências
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    && docker-php-ext-install pdo pdo_mysql zip gd

# 🔥 REMOVER completamente outros MPM (fix definitivo)
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load \
           /etc/apache2/mods-enabled/mpm_event.conf \
           /etc/apache2/mods-enabled/mpm_worker.load \
           /etc/apache2/mods-enabled/mpm_worker.conf || true

# 🔥 Garantir prefork ativo
RUN a2enmod mpm_prefork

# Ativar rewrite
RUN a2enmod rewrite

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar projeto
COPY . .

# Instalar Yii2
RUN composer install --no-dev --optimize-autoloader

# Criar pastas necessárias
RUN mkdir -p runtime web/assets

# Permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 runtime web/assets

# Apache apontar para /web
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/web|g' /etc/apache2/sites-available/000-default.conf

# Porta Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 8080

# Start
CMD mkdir -p /var/www/html/runtime /var/www/html/web/assets && apache2-foreground