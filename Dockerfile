# Use a imagem oficial do PHP 8.2 com Apache
FROM php:8.2-apache

# Argumentos (podem ser passados via --build-arg)
ARG user=www-data
ARG uid=1000

# Instala dependências do sistema e extensões do PHP
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    nodejs \
    npm

# Limpa o cache apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala extensões do PHP necessárias para o Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip pdo_sqlite
RUN pecl install redis && docker-php-ext-enable redis

# Configura e habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Instala o Composer latest
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos do projeto para o container
COPY . /var/www/html

# Ajusta o DocumentRoot do Apache para a pasta public do Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Instala dependências do PHP (ignora platform reqs caso falte algo na imagem base que o sail usaria)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Instala dependências do Node e compila os assets (Vite)
RUN npm install
RUN npm run build

# Ajusta as permissões gerais
RUN chown -R www-data:www-data /var/www/html

# Garante que as pastas cruciais do Laravel tenham permissão de escrita
RUN chmod -R 775 /var/www/html/storage
RUN chmod -R 775 /var/www/html/bootstrap/cache

# Cria pasta storage persistente
RUN mkdir -p /var/www/html/storage/app/public

EXPOSE 80

# Script de entrypoint embutido para rodar chown na montagem do volume
COPY --chmod=755 <<-"EOF" /usr/local/bin/entrypoint.sh
#!/bin/sh
# Ajusta permissões do storage
chown -R www-data:www-data /var/www/html/storage
exec apache2-foreground
EOF

CMD ["/usr/local/bin/entrypoint.sh"]
