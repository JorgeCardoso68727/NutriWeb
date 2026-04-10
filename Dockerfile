# Usa imagem oficial PHP com Apache
FROM php:8.2-apache

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    && docker-php-ext-install pdo pdo_mysql zip gd

# Ativar mod_rewrite (necessário para Yii2)
RUN a2enmod rewrite

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar ficheiros do projeto
COPY . .

# Instalar dependências do Yii2
RUN composer install --no-dev --optimize-autoloader

# Permissões (importante para Yii2)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/runtime \
    && chmod -R 755 /var/www/html/web/assets

# Configurar Apache para usar /web como root
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/web|g' /etc/apache2/sites-available/000-default.conf

# Porta (Railway usa 8080 normalmente)
EXPOSE 8080

# Ajustar Apache para Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

CMD ["apache2-foreground"]