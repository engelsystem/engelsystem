FROM webdevops/php-nginx:debian-7
COPY . /app
COPY custom.conf /opt/docker/etc/nginx/vhost.common.d/custom.conf
EXPOSE 80
