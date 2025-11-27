# Usar imagem oficial do PHP com Apache
FROM php:8.4-apache

# Instalar extensões necessárias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# ----------------------------------------------------
# 1. INSTALAR O COMPOSER
# Este comando baixa o Composer e o move para um diretório PATH.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# ----------------------------------------------------

# Copiar todos os arquivos do projeto para o container
COPY . /var/www/html/

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/html

# --- CORREÇÃO DO RAILWAY ---
# O Railway atribui uma porta aleatória na variável $PORT.
# O Apache por padrão ouve na 80. Este comando altera a configuração do Apache
# para ouvir na porta definida pelo Railway.
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# ----------------------------------------------------
# 2. NOVO COMANDO PADRÃO (CMD)
# Executa 'composer install' para gerar a pasta 'vendor' e depois inicia o Apache.
# Isto garante que o 'autoload.php' está sempre presente.
CMD composer install && apache2-foreground
# ----------------------------------------------------
