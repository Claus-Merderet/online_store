FROM php:8.2-fpm-alpine

RUN ls -la /var/www/html

RUN apk add --no-cache bash git curl libpq-dev && \
    docker-php-ext-install pdo pdo_mysql pdo_pgsql

WORKDIR /var/www/html

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    if [ -f composer.json ]; then composer install; fi

EXPOSE 9000