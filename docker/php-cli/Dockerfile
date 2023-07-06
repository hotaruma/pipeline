ARG PHP_VERSION=$PHP_VERSION

FROM php:${PHP_VERSION}-cli-alpine

ARG XDEBUG_VERSION=3.2.1

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apk add --no-cache $PHPIZE_DEPS linux-headers git \
    && pecl install xdebug-${XDEBUG_VERSION} \
	&& docker-php-ext-enable xdebug

COPY docker-php-ext-xdebug.ini "${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini"

WORKDIR /app
