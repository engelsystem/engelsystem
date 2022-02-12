#!/usr/bin/env sh
set -e

# If first arg starts with a `-` or is empty
if [[ "${1#-}" != "${1}" ]] || [[ -z "${1}" ]]; then
  set -- php-fpm "$@"
fi

# Configure app url
url=$(echo "$APP_URL" | sed -n 's~https*://[^/]\+/\(.*\)~\1~p')
url=${url%/}
if [[ -n "${url}" ]]; then
  echo "Url prefix: '${url}'"
  sed -i "s~location /~rewrite ^/${url}(/.*)?$ /\$1;\n    location /~" /etc/nginx/nginx.conf
fi

function get_name() {
    echo "$1" | cut -d: -f1
}

# Create users for user mapping from RUN_USER=[uid]:[gid]
if [[ -n "${RUN_USER}" ]]; then
  echo "Setting user to $RUN_USER"

  gid=${RUN_USER#*:}
  grp=$(getent group $gid || true)
  if [[ -z "$grp" ]]; then # Group not present
    addgroup -g $gid php
    grp=$(getent group $gid)
  fi
  group=$(get_name "$grp")

  uid=${RUN_USER%:*}
  usr=$(getent passwd $uid || true)
  if [[ -z "$usr" ]]; then # User not present
    adduser -D -h "$PWD" -u $uid -G "$group" php
    usr=$(getent passwd $uid)
  fi
  user=$(get_name "$usr")

  echo -e "user = $user\ngroup = $group" >> /usr/local/etc/php-fpm.d/zz-docker.conf

  echo "Running as $user:$group"
fi


nginx -g 'daemon off;'&
exec "$@"
