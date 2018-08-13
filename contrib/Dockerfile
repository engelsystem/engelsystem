FROM composer AS composer
COPY composer.json /app/
RUN composer install --no-dev
RUN composer dump-autoload --optimize

FROM node:8-alpine as themes
WORKDIR /app
COPY frontend/ /app/frontend
COPY .babelrc package.json webpack.config.js /app/
RUN apk add --no-cache yarn
RUN yarn install
RUN yarn build

# Intermediate container for less layers
FROM alpine as data
COPY config/ /app/config
COPY db/ /app/db
COPY includes/ /app/includes
COPY locale/ /app/locale
COPY public/ /app/html
COPY src/ /app/src
COPY templates/ /app/templates

COPY --from=composer /app/vendor/ /app/vendor
COPY --from=composer /app/composer.lock /app/

COPY --from=themes /app/public/assets /app/html/assets
COPY --from=themes /app/yarn.lock /app/

RUN rm -f /app/config/config.php

FROM php:7-fpm-alpine
COPY --from=data /app/ /var/www
RUN apk add --no-cache icu-dev gettext-dev && \
    docker-php-ext-install intl gettext pdo_mysql

ENV TRUSTED_PROXIES 10.0.0.0/8,::ffff:10.0.0.0/8,\
                    127.0.0.0/8,::ffff:127.0.0.0/8,\
                    172.16.0.0/12,::ffff:172.16.0.0/12,\
                    192.168.0.0/16,::ffff:192.168.0.0/16,\
                    ::1/128,fc00::/7,fec0::/10
