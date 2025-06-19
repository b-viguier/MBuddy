FROM php:8.0-fpm

ENV DEBIAN_FRONTEND=noninteractive
RUN apt update && apt install -y \
    libzip-dev \
    wget \
    less \
    nano \
    libffi-dev \
    unzip \
    procps \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install zip ffi pcntl

RUN pecl install xdebug
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
