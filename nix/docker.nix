{ pkgs, lib, packages }:

let
  # Non-root user for the container
  nonRootUser = {
    uid = 65534;
    gid = 65534;
    name = "engelsystem";
    group = "engelsystem";
  };

  # Create passwd and group files for the container
  passwdFile = pkgs.writeText "passwd" ''
    root:x:0:0:root:/root:/sbin/nologin
    ${nonRootUser.name}:x:${toString nonRootUser.uid}:${toString nonRootUser.gid}:Engelsystem User:/var/www:/sbin/nologin
    nobody:x:65534:65534:Nobody:/nonexistent:/sbin/nologin
  '';

  groupFile = pkgs.writeText "group" ''
    root:x:0:
    ${nonRootUser.group}:x:${toString nonRootUser.gid}:
    nobody:x:65534:
  '';

  # Nginx configuration for production
  nginxConf = pkgs.writeText "nginx.conf" ''
    error_log stderr;
    pid /tmp/nginx.pid;
    worker_processes auto;

    events {
      worker_connections 1024;
    }

    http {
      include ${pkgs.nginx}/conf/mime.types;
      default_type application/octet-stream;

      # Temp paths in /tmp for rootless operation
      client_body_temp_path /tmp/client_body_temp;
      fastcgi_temp_path /tmp/fastcgi_temp;
      proxy_temp_path /tmp/proxy_temp;
      scgi_temp_path /tmp/scgi_temp;
      uwsgi_temp_path /tmp/uwsgi_temp;

      # Buffer settings
      fastcgi_buffers 16 16k;
      fastcgi_buffer_size 32k;

      # Logging
      access_log /dev/stdout;
      error_log stderr;

      # Gzip compression
      gzip on;
      gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

      # Forwarded proto handling
      map $http_x_forwarded_proto $forwarded_proto {
        default $http_x_forwarded_proto;
        https   https;
      }

      server {
        listen [::]:8080 ipv6only=off;
        root /var/www/public;
        index index.php;

        # Security headers
        add_header X-Content-Type-Options nosniff;
        add_header X-Frame-Options SAMEORIGIN;
        add_header Referrer-Policy strict-origin-when-cross-origin;

        # Proxy settings
        proxy_redirect off;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $forwarded_proto;

        location / {
          try_files $uri $uri/ /index.php$is_args$args;
        }

        location ~ \.php$ {
          fastcgi_pass unix:/tmp/php-fpm.sock;
          fastcgi_index index.php;
          fastcgi_read_timeout 600s;
          fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
          include ${pkgs.nginx}/conf/fastcgi_params;
        }

        # Deny access to hidden files
        location ~ /\. {
          deny all;
        }
      }
    }
  '';

  # PHP-FPM configuration for production
  phpFpmConf = pkgs.writeText "php-fpm.conf" ''
    [global]
    error_log = /dev/stderr
    daemonize = no
    log_level = warning

    [www]
    user = ${nonRootUser.name}
    group = ${nonRootUser.group}
    listen = /tmp/php-fpm.sock
    listen.owner = ${nonRootUser.name}
    listen.group = ${nonRootUser.group}
    listen.mode = 0660

    pm = dynamic
    pm.max_children = 50
    pm.start_servers = 5
    pm.min_spare_servers = 5
    pm.max_spare_servers = 35

    ; Security settings
    php_admin_value[expose_php] = Off
    php_admin_value[display_errors] = Off
    php_admin_value[log_errors] = On
    php_admin_value[error_log] = /dev/stderr

    ; Performance settings
    php_admin_value[opcache.enable] = 1
    php_admin_value[opcache.memory_consumption] = 256
    php_admin_value[opcache.max_accelerated_files] = 20000
    php_admin_value[opcache.validate_timestamps] = 0

    ; Limits
    php_admin_value[memory_limit] = 256M
    php_admin_value[max_execution_time] = 60
    php_admin_value[upload_max_filesize] = 64M
    php_admin_value[post_max_size] = 64M
  '';

  # Entrypoint script
  entrypoint = pkgs.writeShellScript "entrypoint" ''
    #!/bin/sh
    set -e

    # Create temp directories
    mkdir -p /tmp/client_body_temp /tmp/fastcgi_temp /tmp/proxy_temp /tmp/scgi_temp /tmp/uwsgi_temp

    # Ensure storage is writable
    mkdir -p /var/www/storage/app /var/www/storage/cache /var/www/storage/logs

    # Start PHP-FPM in background
    ${lib.phpProd}/bin/php-fpm -y ${phpFpmConf} &

    # Wait for PHP-FPM socket
    while [ ! -S /tmp/php-fpm.sock ]; do
      sleep 0.1
    done

    # Start nginx in foreground
    exec ${pkgs.nginx}/bin/nginx -c ${nginxConf} -g "daemon off;"
  '';

  # Minimal CA certificates
  caCertificates = pkgs.cacert;

  # Timezone data (minimal)
  tzdata = pkgs.tzdata;

in
{
  # Hardened Docker image
  image = pkgs.dockerTools.buildLayeredImage {
    name = "engelsystem";
    tag = lib.version;

    # Layer configuration for better caching
    maxLayers = 100;

    contents = [
      # Application
      packages.engelsystem

      # Runtime dependencies (minimal)
      lib.phpProd
      pkgs.nginx

      # Required system files
      caCertificates
      tzdata
    ];

    # Extra commands to set up the image
    extraCommands = ''
      # Create directory structure
      mkdir -p var/www tmp etc run

      # Copy application files
      cp -r ${packages.engelsystem}/share/engelsystem/* var/www/

      # Create storage directories
      mkdir -p var/www/storage/app var/www/storage/cache var/www/storage/logs

      # Set up /etc files
      cp ${passwdFile} etc/passwd
      cp ${groupFile} etc/group

      # Create nsswitch.conf for user resolution
      echo "passwd: files" > etc/nsswitch.conf
      echo "group: files" >> etc/nsswitch.conf

      # Set up timezone
      mkdir -p etc
      ln -sf ${tzdata}/share/zoneinfo/UTC etc/localtime
      echo "UTC" > etc/timezone
    '';

    config = {
      # Run as non-root user
      User = "${toString nonRootUser.uid}:${toString nonRootUser.gid}";

      # Working directory
      WorkingDir = "/var/www";

      # Entrypoint
      Entrypoint = [ entrypoint ];

      # Expose port 8080 (non-privileged)
      ExposedPorts = {
        "8080/tcp" = { };
      };

      # Environment variables
      Env = [
        "APP_ENV=production"
        "TZ=UTC"
        "TRUSTED_PROXIES=10.0.0.0/8,127.0.0.0/8,172.16.0.0/12,192.168.0.0/16"
      ];

      # Volume for persistent storage
      Volumes = {
        "/var/www/storage" = { };
      };

      # Labels
      Labels = {
        "org.opencontainers.image.title" = "Engelsystem";
        "org.opencontainers.image.description" = "Shift planning system for chaos events";
        "org.opencontainers.image.version" = lib.version;
        "org.opencontainers.image.source" = "https://github.com/engelsystem/engelsystem";
        "org.opencontainers.image.licenses" = "GPL-2.0-or-later";
      };

      # Health check
      Healthcheck = {
        Test = [ "CMD" "${pkgs.curl}/bin/curl" "-f" "http://localhost:8080/" ];
        Interval = 30000000000; # 30s in nanoseconds
        Timeout = 10000000000;  # 10s in nanoseconds
        Retries = 3;
      };
    };
  };

  # Stream the image for efficient loading
  imageStream = pkgs.dockerTools.streamLayeredImage {
    name = "engelsystem";
    tag = lib.version;
    maxLayers = 100;
    contents = [
      packages.engelsystem
      lib.phpProd
      pkgs.nginx
      caCertificates
      tzdata
    ];
    config = {
      User = "${toString nonRootUser.uid}:${toString nonRootUser.gid}";
      WorkingDir = "/var/www";
      Entrypoint = [ entrypoint ];
      ExposedPorts = { "8080/tcp" = { }; };
      Env = [
        "APP_ENV=production"
        "TZ=UTC"
      ];
    };
  };
}
