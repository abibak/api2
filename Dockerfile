FROM php:7.4-fpm-alpine

RUN set -ex \
  && apk --no-cache add \
    postgresql-dev

WORKDIR /var/www

RUN docker-php-ext-install pdo pdo_pgsql
