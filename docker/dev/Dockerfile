# Engelsystem PHP FPM/Nginx development image including Xdebug
FROM php:8-fpm-alpine AS es_base
WORKDIR /var/www
RUN apk add --no-cache icu-dev $PHPIZE_DEPS && \
    pecl install pcov xdebug && \
    docker-php-ext-install intl pdo_mysql && \
    docker-php-ext-enable pcov xdebug
RUN echo -e "xdebug.mode=debug\nxdebug.discover_client_host=1\n" >> /usr/local/etc/php/conf.d/xdebug.ini

FROM es_base AS es_webserver
RUN apk add --no-cache nginx && \
    sed -i 's/9000/127.0.0.1:9000/' /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/entrypoint.sh /
COPY docker/nginx.conf /etc/nginx/nginx.conf
ENTRYPOINT /entrypoint.sh
EXPOSE 80

ENV TRUSTED_PROXIES 10.0.0.0/8,::ffff:10.0.0.0/8,\
                    127.0.0.0/8,::ffff:127.0.0.0/8,\
                    172.16.0.0/12,::ffff:172.16.0.0/12,\
                    192.168.0.0/16,::ffff:192.168.0.0/16,\
                    ::1/128,fc00::/7,fec0::/10

# Engelsystem development workspace
# Contains all tools required to build / manage the system
FROM es_base AS es_workspace
RUN echo 'memory_limit = 512M' > /usr/local/etc/php/conf.d/docker-php.ini
RUN apk add --no-cache gettext nodejs npm yarn
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENTRYPOINT php -r 'sleep(PHP_INT_MAX);'
