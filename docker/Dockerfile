# composer install
FROM composer:latest AS composer
COPY ./ /app/
RUN composer --no-ansi install --no-dev --ignore-platform-reqs
RUN composer --no-ansi dump-autoload --optimize

# Intermediate containers for less layers
FROM alpine as translation
RUN apk add gettext
COPY resources/lang/ /data
RUN find /data -type f -name '*.po' -exec sh -c 'file="{}"; msgfmt "${file%.*}.po" -o "${file%.*}.mo"' \;

FROM alpine as data
COPY .babelrc .browserslistrc composer.json LICENSE package.json README.md webpack.config.js yarn.lock /app/
COPY bin/ /app/bin
COPY config/ /app/config
COPY db/ /app/db
COPY includes/ /app/includes
COPY public/ /app/public
COPY resources/views /app/resources/views
COPY src/ /app/src
COPY storage/ /app/storage

COPY --from=translation /data/ /app/resources/lang
COPY --from=composer /app/vendor/ /app/vendor
COPY --from=composer /app/composer.lock /app/

RUN find /app/storage/ -type f -not -name VERSION -exec rm {} \;

# Build the PHP container
FROM php:8-fpm-alpine
WORKDIR /var/www
RUN apk add --no-cache icu-dev && \
    docker-php-ext-install intl pdo_mysql
COPY --from=data /app/ /var/www
RUN chown -R www-data:www-data /var/www/storage/ && \
    rm -r /var/www/html

ARG VERSION
RUN if [[ ! -f /var/www/storage/app/VERSION ]] && [[ ! -z "${VERSION}" ]]; then \
        echo -n "${VERSION}" > /var/www/storage/app/VERSION; fi

ENV TRUSTED_PROXIES 10.0.0.0/8,::ffff:10.0.0.0/8,\
                    127.0.0.0/8,::ffff:127.0.0.0/8,\
                    172.16.0.0/12,::ffff:172.16.0.0/12,\
                    192.168.0.0/16,::ffff:192.168.0.0/16,\
                    ::1/128,fc00::/7,fec0::/10
