# Usar imagem oficial do PHP com Apache
FROM php:8.4-apache

# Instalar extensões necessárias (mysqli, pdo_mysql)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar todos os arquivos do projeto para o container
COPY . /var/www/html/

# Dar permissões corretas (opcional, mas recomendado)
RUN chown -R www-data:www-data /var/www/html

# Configurar Apache para ouvir na porta definida pelo Railway
ENV PORT 8080
EXPOSE 8080

# Ajustar DocumentRoot se necessário (opcional)
# WORKDIR /var/www/html

# Comando padrão para rodar o Apache em foreground
CMD ["apache2-foreground"]
