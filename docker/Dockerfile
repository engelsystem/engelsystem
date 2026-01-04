# Composer install
FROM composer:latest AS composer
COPY ./ /app/
RUN composer --no-ansi install --no-dev --ignore-platform-reqs
RUN composer --no-ansi dump-autoload --optimize

# Generate .mo files
FROM alpine AS translation
RUN apk add gettext
COPY resources/lang/ /data
RUN find /data -type f -name '*.po' -exec sh -c 'msgfmt "${1%.*}.po" -o"${1%.*}.mo"' shell {} \;

# Build the themes
FROM node:20-alpine AS themes
WORKDIR /app
COPY .babelrc .browserslistrc package.json webpack.config.js yarn.lock /app/
RUN yarn --frozen-lockfile
COPY resources/assets/ /app/resources/assets
RUN yarn build

# Generate application structure
FROM alpine AS data
COPY .babelrc .browserslistrc composer.json LICENSE package.json README.md webpack.config.js yarn.lock /app/
COPY bin/ /app/bin
COPY config/ /app/config
COPY db/ /app/db
COPY includes/ /app/includes
COPY public/ /app/public
COPY resources/api /app/resources/api
COPY resources/plugins /app/resources/plugins
COPY resources/views /app/resources/views
COPY src/ /app/src
COPY storage/ /app/storage

COPY --from=translation /data/ /app/resources/lang
COPY --from=composer /app/vendor/ /app/vendor
COPY --from=composer /app/composer.lock /app/
COPY --from=themes /app/public/assets /app/public/assets/

RUN find /app/storage/ -type f -not -name VERSION -exec rm {} \;

# Build the PHP/Nginx container
FROM php:8.5-fpm-alpine
WORKDIR /var/www
RUN apk add --no-cache icu-dev nginx && \
    docker-php-ext-install intl pdo_mysql && \
    sed -i 's/9000/127.0.0.1:9000/' /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/entrypoint.sh /
COPY docker/nginx.conf /etc/nginx/nginx.conf
ENTRYPOINT /entrypoint.sh
EXPOSE 80
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
