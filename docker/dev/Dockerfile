# Engelsystem PHP FPM development image including Xdebug
FROM php:8-fpm-alpine AS es_php_fpm
WORKDIR /var/www
RUN apk add --no-cache icu-dev $PHPIZE_DEPS && \
    pecl install pcov xdebug && \
    docker-php-ext-install intl pdo_mysql && \
    docker-php-ext-enable pcov xdebug
RUN echo -e "xdebug.mode=debug\nxdebug.discover_client_host=1\n" >> /usr/local/etc/php/conf.d/xdebug.ini

ENV TRUSTED_PROXIES 10.0.0.0/8,::ffff:10.0.0.0/8,\
                    127.0.0.0/8,::ffff:127.0.0.0/8,\
                    172.16.0.0/12,::ffff:172.16.0.0/12,\
                    192.168.0.0/16,::ffff:192.168.0.0/16,\
                    ::1/128,fc00::/7,fec0::/10

# Engelsystem development workspace
# Contains all tools required to build / manage the system
FROM es_php_fpm AS es_workspace
RUN apk add --no-cache composer gettext nodejs npm yarn
