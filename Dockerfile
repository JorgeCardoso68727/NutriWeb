# Imagem base: PHP 8.2 com Apache
FROM php:8.2-apache

# Instalar extensões necessárias para Yii2 + MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Ativar mod_rewrite (URLs amigáveis do Yii2)
RUN a2enmod rewrite

# Copiar código para o container
COPY . /var/www/html/

# Definir diretório de trabalho
WORKDIR /var/www/html/

# Instalar Composer (a partir da imagem oficial)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar dependências do Yii2
RUN composer install --no-dev --prefer-dist --no-scripts --optimize-autoloader || true

# Apontar o DocumentRoot para a pasta web/
RUN sed -i 's#/var/www/html#/var/www/html/web#g' /etc/apache2/sites-available/000-default.conf

# Railway expõe a porta 8080, mas Apache usa 80
EXPOSE 80

# Iniciar Apache
CMD ["apache2-foreground"]
