ARG PHP_VERSION

FROM php:${PHP_VERSION}-cli-alpine

COPY --from=composer:2.0 /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_CACHE_DIR /tmp
