# ============================================================
# Dockerfile — VetNova para Railway
# PHP 8.2 + Node 22 + Apache
# Coloca en la RAÍZ del proyecto (junto a composer.json)
# ============================================================

# ── Stage 1: Build de assets con Node 22 ───────────────────
FROM node:22-alpine AS node_builder

WORKDIR /app

# Copiar solo lo necesario para npm install
COPY package*.json ./
RUN npm ci

# Copiar fuentes y compilar
COPY resources/ ./resources/
COPY vite.config.js ./
COPY public/ ./public/

RUN npm run build

# ── Stage 2: App PHP con Apache ────────────────────────────
FROM php:8.2-apache

# Extensiones PHP requeridas por Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configurar Apache para Laravel (DocumentRoot → /var/www/html/public)
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite headers

# Configurar PHP para producción
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "upload_max_filesize=20M" >> "$PHP_INI_DIR/php.ini" \
    && echo "post_max_size=20M" >> "$PHP_INI_DIR/php.ini" \
    && echo "memory_limit=256M" >> "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

# Copiar proyecto
COPY . .

# Copiar assets compilados desde Stage 1
COPY --from=node_builder /app/public/build ./public/build

# Instalar dependencias PHP (sin dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Permisos de Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Script de arranque: migra y levanta Apache
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Railway asigna el puerto dinámicamente via $PORT
# Apache escucha en $PORT
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
