ARG PHP_VERSION=7.4
FROM php:${PHP_VERSION}-cli as base
COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt update \
    && apt upgrade -y \
    && apt install -y apt-utils libxml2-dev libzip-dev

FROM base as PHP74
RUN docker-php-ext-install dom json xml zip

FROM base as PHP8
RUN docker-php-ext-install dom xml zip