FROM php:8.2-fpm

# Argumentos
ARG user=trimax
ARG uid=1000

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libfcgi-bin \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# COPIAR CONFIGURACIONES PHP
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Crear usuario del sistema para ejecutar Composer y Artisan
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Configurar directorio de trabajo
WORKDIR /var/www

# Dependencias primero (I6): solo se reinstalan si cambia composer.json o
# composer.lock, aprovechando el cache de capas de Docker. Con esto la
# imagen queda autonoma — no depende de un `composer install` manual en el
# host ni del bind mount de desarrollo para tener vendor/.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copiar el resto del proyecto
COPY --chown=$user:$user . /var/www

# Autoload optimizado ahora que ya está todo el código, y homogeneizar
# dueño de vendor/ (creado como root en el paso anterior) con el resto.
RUN composer dump-autoload --optimize && \
    chown -R $user:$user /var/www

# Cambiar al usuario creado
USER $user

# Exponer puerto 9000 para PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]