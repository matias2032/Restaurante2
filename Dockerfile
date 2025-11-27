# Usar imagem oficial do PHP com Apache
FROM php:8.4-apache

# 1. INSTALAR DEPENDÊNCIAS DO SISTEMA
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libssl-dev \
    default-libmysqlclient-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. INSTALAR EXTENSÕES PHP
RUN docker-php-ext-install pdo_mysql zip

# 3. INSTALAR O COMPOSER
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 4. COPIAR OS ARQUIVOS DO PROJETO
COPY . /var/www/html/

WORKDIR /var/www/html/

# 5. EXECUTAR COMPOSER INSTALL
RUN composer install --no-dev --optimize-autoloader

# 6. AJUSTAR PERMISSÕES
RUN chown -R www-data:www-data /var/www/html

# 7. HABILITAR mod_rewrite
RUN a2enmod rewrite

# 8. CONFIGURAR APACHE PARA PORTA 8080 (Railway padrão)
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 9. EXPOR A PORTA
EXPOSE 8080

# 10. COMANDO PADRÃO
CMD ["apache2-foreground"]
