FROM composer AS composer
COPY composer.json /app/
RUN composer install


FROM node:8-alpine as themes
WORKDIR /app
RUN npm install -g less
COPY --from=composer /app/vendor /app/vendor
COPY public/ /app/public
COPY themes/ /app/themes
RUN sh /app/themes/build-themes.sh

FROM php:7-fpm-alpine
RUN apk add --no-cache icu-dev
RUN docker-php-ext-install intl
RUN apk add --no-cache gettext-dev
RUN docker-php-ext-install gettext
RUN docker-php-ext-install pdo_mysql

COPY --from=composer /app/vendor /var/www/vendor
COPY --from=themes /app/public/ /var/www/html
COPY src/ /var/www/src/
COPY includes/ /var/www/includes/
COPY config/ /var/www/config/
COPY locale/ /var/www/locale
COPY templates/ /var/www/templates

# Symlink gets copied so we delete the symlink.
RUN rm /var/www/html/vendor/bootstrap
COPY vendor/twbs/bootstrap/dist/ /var/www/html/vendor/bootstrap/
