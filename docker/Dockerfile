FROM php:8.0-cli

ENV DEBIAN_FRONTEND=noninteractive
RUN apt update && apt install -y firefox-esr libzip-dev wget less nano && rm -rf /var/lib/apt/lists/*

ARG GECKODRIVER_VERSION=0.34.0
RUN wget -q https://github.com/mozilla/geckodriver/releases/download/v$GECKODRIVER_VERSION/geckodriver-v$GECKODRIVER_VERSION-linux64.tar.gz; \
    tar -zxf geckodriver-v$GECKODRIVER_VERSION-linux64.tar.gz -C /usr/bin; \
    rm geckodriver-v$GECKODRIVER_VERSION-linux64.tar.gz

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install zip

RUN pecl install xdebug
COPY docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
