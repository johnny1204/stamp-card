FROM php:8.4-fpm

# 必要な拡張をインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    ffmpeg

# PHP拡張をインストール
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Xdebugをインストール
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Xdebug設定
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.discover_client_host=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www