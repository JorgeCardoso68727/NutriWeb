# Imagem base com PHP + Apache
FROM php:8.2-apache

# Instalar dependências necessárias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    && docker-php-ext-install pdo pdo_mysql zip gd

# Ativar mod_rewrite (Yii precisa disto)
RUN a2enmod rewrite

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Diretório da app
WORKDIR /var/www/html

# Copiar projeto
COPY . .

# Instalar dependências do Yii2
RUN composer install --no-dev --optimize-autoloader

# 🔥 Criar pastas obrigatórias do Yii2
RUN mkdir -p runtime web/assets

# Permissões corretas
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 runtime web/assets

# Configurar Apache para apontar para /web
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/web|g' /etc/apache2/sites-available/000-default.conf

# Ajustar porta dinâmica do Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Expor porta
EXPOSE 8080

# 🚀 Garantir que as pastas existem em runtime também
CMD mkdir -p /var/www/html/runtime /var/www/html/web/assets && apache2-foreground