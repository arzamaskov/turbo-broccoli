ARG PHP_VERSION=8.3

FROM php:${PHP_VERSION}-fpm-alpine AS base

ARG APP_UID=1000
ARG APP_GID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    PATH="/app/bin:${PATH}"

WORKDIR /app

RUN set -eux; \
    apk add --no-cache \
        acl \
        ca-certificates \
        icu-dev \
        libzip-dev \
        sqlite-dev \
        su-exec \
        unzip; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS; \
    docker-php-ext-install -j"$(nproc)" intl opcache pdo_sqlite zip; \
    apk del .build-deps; \
    php -r 'exit(defined("PASSWORD_ARGON2ID") ? 0 : 1);'; \
    addgroup -g "${APP_GID}" app; \
    adduser -D -u "${APP_UID}" -G app -s /bin/sh app; \
    mkdir -p /app /tmp/composer; \
    chown -R app:app /app /tmp/composer

FROM base AS dev

ENV APP_ENV=dev \
    APP_DEBUG=1 \
    XDEBUG_MODE=off \
    XDEBUG_CONFIG="client_host=host.docker.internal client_port=9003"

RUN set -eux; \
    apk add --no-cache \
        git \
        linux-headers; \
    apk add --no-cache --virtual .xdebug-build-deps \
        $PHPIZE_DEPS; \
    pecl install xdebug; \
    docker-php-ext-enable xdebug; \
    apk del .xdebug-build-deps

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY docker/php/conf.d/common.ini /usr/local/etc/php/conf.d/zz-common.ini
COPY docker/php/php-fpm.d/app.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/php/docker-entrypoint.sh /usr/local/bin/app-entrypoint
COPY docker/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

RUN chmod +x /usr/local/bin/app-entrypoint

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]

FROM base AS prod_deps

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    APP_SECRET=build-time-secret

RUN apk add --no-cache git

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY docker/php/conf.d/common.ini /usr/local/etc/php/conf.d/zz-common.ini
COPY docker/php/php-fpm.d/app.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/php/docker-entrypoint.sh /usr/local/bin/app-entrypoint
COPY --chown=app:app composer.* ./

RUN chmod +x /usr/local/bin/app-entrypoint

RUN set -eux; \
    su-exec app composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --no-scripts \
        --no-autoloader

COPY --chown=app:app . .

RUN set -eux; \
    mkdir -p var/cache var/log var/data; \
    chown -R app:app var; \
    su-exec app composer dump-autoload --classmap-authoritative --no-dev; \
    su-exec app php bin/console cache:clear --env=prod --no-debug

FROM base AS prod

ENV APP_ENV=prod \
    APP_DEBUG=0

COPY docker/php/conf.d/common.ini /usr/local/etc/php/conf.d/zz-common.ini
COPY docker/php/conf.d/prod.ini /usr/local/etc/php/conf.d/zz-prod.ini
COPY docker/php/php-fpm.d/app.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/php/docker-entrypoint.sh /usr/local/bin/app-entrypoint
COPY --from=prod_deps --chown=app:app /app /app

RUN chmod +x /usr/local/bin/app-entrypoint

ENTRYPOINT ["app-entrypoint"]
CMD ["php-fpm"]
