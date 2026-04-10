# Usa PHP 8.2 com Apache
FROM php:8.2-apache

# Instalar extensões necessárias
RUN docker-php-ext-install pdo pdo_mysql

# Ativar mod_rewrite do Apache (Yii2 precisa disto)
RUN a2enmod rewrite

# Copiar ficheiros do projeto
COPY . /var/www/html/

# Definir diretório de trabalho
WORKDIR /var/www/html/

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar dependências do Yii2
RUN composer install --no-dev --optimize-autoloader

# Configurar Apache para apontar para /web
RUN sed -i 's#/var/www/html#/var/www/html/web#g' /etc/apache2/sites-available/000-default.conf

# Expor porta 8080 (Railway usa 8080)
EXPOSE 8080

# Iniciar Apache
CMD ["apache2-foreground"]