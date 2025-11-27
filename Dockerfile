# Usar imagem oficial do PHP com Apache
FROM php:8.4-apache

# ----------------------------------------------------
# 1. INSTALAR DEPENDÊNCIAS DO SISTEMA
# Instala git, unzip, e as bibliotecas DEV necessárias para compilar PDO, MySQLi e Zip.
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
# Agora o instalador do PHP encontra as bibliotecas de sistema necessárias.
RUN docker-php-ext-install pdo_mysql zip

# 3. INSTALAR O COMPOSER
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# ----------------------------------------------------

# Copiar todos os arquivos do projeto para o container
COPY . /var/www/html/

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/html

# --- CORREÇÃO DO RAILWAY ---
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 4. COMANDO PADRÃO FINAL
CMD composer install && apache2-foreground
