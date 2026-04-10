FROM php:8.2-cli

# Instalar extensões necessárias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    && docker-php-ext-install pdo pdo_mysql zip gd

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Diretório
WORKDIR /app

# Copiar projeto
COPY . .

# Instalar Yii2
RUN composer install --no-dev --optimize-autoloader

# Criar pastas necessárias
RUN mkdir -p runtime web/assets \
    && chmod -R 777 runtime web/assets

# Porta Railway
EXPOSE 8080

# 🚀 Start server PHP (sem Apache)
CMD php -S 0.0.0.0:${PORT} -t web