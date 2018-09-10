FROM composer AS composer
COPY composer.json /app/
RUN composer --no-ansi install --no-dev --ignore-platform-reqs
RUN composer --no-ansi dump-autoload --optimize

FROM node:8-alpine as themes
WORKDIR /app
COPY resources/assets/ /app/resources/assets
COPY .babelrc package.json webpack.config.js /app/
RUN apk add --no-cache yarn
RUN yarn install
RUN yarn build

# Intermediate container for less layers
FROM alpine as data
COPY bin/ /app/bin
COPY config/ /app/config
COPY db/ /app/db
COPY includes/ /app/includes
COPY public/ /app/public
COPY resources/ /app/resources
COPY src/ /app/src

COPY composer.json LICENSE package.json README.md /app/

COPY --from=composer /app/vendor/ /app/vendor
COPY --from=composer /app/composer.lock /app/

COPY --from=themes /app/public/assets /app/public/assets
COPY --from=themes /app/yarn.lock /app/

RUN rm -f /app/config/config.php

FROM php:7-fpm-alpine
WORKDIR /var/www
COPY --from=data /app/ /var/www
RUN apk add --no-cache icu-dev gettext-dev && \
    docker-php-ext-install intl gettext pdo_mysql && \
    rm -r /var/www/html

ENV TRUSTED_PROXIES 10.0.0.0/8,::ffff:10.0.0.0/8,\
                    127.0.0.0/8,::ffff:127.0.0.0/8,\
                    172.16.0.0/12,::ffff:172.16.0.0/12,\
                    192.168.0.0/16,::ffff:192.168.0.0/16,\
                    ::1/128,fc00::/7,fec0::/10
