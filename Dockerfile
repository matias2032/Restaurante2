# Usar imagem oficial do PHP com Apache
FROM php:8.4-apache

# ----------------------------------------------------
# 1. INSTALAR DEPENDÊNCIAS DO SISTEMA
# Instala git, unzip, e bibliotecas DEV
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
# ----------------------------------------------------

# 4. COPIAR OS ARQUIVOS DO PROJETO PRIMEIRO! (Passo CRUCIAL)
# Copia todos os arquivos do projeto (incluindo composer.json) para o container
COPY . /var/www/html/
# Define o diretório de trabalho padrão
WORKDIR /var/www/html/

# 5. EXECUTAR COMPOSER INSTALL AGORA QUE O FICHEIRO EXISTE
RUN composer install --no-dev --optimize-autoloader

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/html

# --- CORREÇÃO DO RAILWAY ---
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 6. COMANDO PADRÃO FINAL
CMD ["apache2-foreground"]
