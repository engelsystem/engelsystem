FROM nginx:alpine as es_nginx
COPY docker/nginx/entrypoint.sh /
ENTRYPOINT /entrypoint.sh
RUN mkdir -p /var/www/public/ && touch /var/www/public/index.php
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

FROM node:14-alpine as themes
WORKDIR /app
COPY .babelrc .browserslistrc package.json webpack.config.js yarn.lock /app/
RUN yarn --frozen-lockfile
COPY resources/assets/ /app/resources/assets
RUN yarn build

FROM es_nginx
COPY --from=themes /app/public/assets /var/www/public/assets/
