FROM php:8.2-fpm-alpine

# Устанавливаем необходимые зависимости
RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpq-dev \
    librdkafka-dev \
    autoconf \
    g++ \
    make

# Устанавливаем расширения PHP
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Устанавливаем расширение rdkafka
RUN pecl install rdkafka && \
    docker-php-ext-enable rdkafka

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Копируем исходный код приложения
COPY . /var/www/html

# Устанавливаем зависимости Composer (если есть composer.json)
RUN if [ -f composer.json ]; then composer install; fi

# Открываем порт 9000 для PHP-FPM
EXPOSE 9000

# Указываем рабочую директорию
WORKDIR /var/www/html

# Запускаем PHP-FPM
CMD ["php-fpm"]