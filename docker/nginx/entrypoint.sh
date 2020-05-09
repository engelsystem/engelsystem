#!/usr/bin/env sh
set -e

sed -i "s/es_php_fpm:/${PHP_FPM_HOST:-es_php_fpm}:/g" /etc/nginx/nginx.conf

# If first arg starts with a `-` or is empty
if [[ "${1#-}" != "${1}" ]] || [[ -z "${1}" ]]; then
  set -- nginx -g 'daemon off;' "$@"
fi

exec "$@"
