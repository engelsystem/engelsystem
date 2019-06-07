FROM node:8-alpine as themes
WORKDIR /app
RUN apk add --no-cache yarn
COPY .babelrc package.json webpack.config.js /app/
RUN yarn install
COPY resources/assets/ /app/resources/assets
RUN yarn build

FROM nginx:alpine
COPY contrib/nginx/nginx.conf /etc/nginx/nginx.conf
COPY --from=themes /app/public/assets /var/www/public/assets/
RUN touch /var/www/public/index.php
