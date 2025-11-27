# Usar imagem oficial do PHP com Apache
FROM php:8.4-apache

# ----------------------------------------------------
# 1. INSTALAR DEPENDÊNCIAS DO SISTEMA (git e unzip)
# O Composer precisa destas ferramentas para baixar pacotes se o zip PHP falhar.
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# 2. INSTALAR EXTENSÕES PHP (incluindo zip)
RUN docker-php-ext-install mysqli pdo pdo_mysql zip
# ----------------------------------------------------

# 3. INSTALAR O COMPOSER
# Este comando baixa o Composer e o move para um diretório PATH.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar todos os arquivos do projeto para o container
COPY . /var/www/html/

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/html

# --- CORREÇÃO DO RAILWAY ---
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 4. COMANDO PADRÃO FINAL
# Executa 'composer install' (que agora funcionará) e depois inicia o Apache.
CMD composer install && apache2-foreground
