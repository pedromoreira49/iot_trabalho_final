FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
  libssl-dev \
  && docker-php-ext-install sockets

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install mysqli

WORKDIR /var/www/html/iot-galpao
COPY . /var/www/html/iot-galpao/
RUN composer install --no-dev --optimize-autoloader

RUN a2enmod rewrite

CMD ["php", "execute.php"]

EXPOSE 80