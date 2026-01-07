{ pkgs, lib, packages }:

let
  # Helper to create a flake app
  mkApp = drv: {
    type = "app";
    program = "${drv}/bin/${drv.pname or drv.name}";
  };

  # Development server script
  devServer = pkgs.writeShellScriptBin "engelsystem-dev" ''
    set -euo pipefail

    PORT="''${1:-5080}"
    DIR="''${ENGELSYSTEM_DIR:-$(pwd)}"

    if [ ! -f "$DIR/public/index.php" ]; then
      echo "Error: Not in engelsystem directory. Set ENGELSYSTEM_DIR or run from project root."
      exit 1
    fi

    echo "Starting Engelsystem development server on http://localhost:$PORT"
    echo "Press Ctrl+C to stop"
    echo ""

    cd "$DIR"
    exec ${lib.php}/bin/php -S "0.0.0.0:$PORT" -t public/
  '';

  # Watch mode (frontend + PHP server)
  watchMode = pkgs.writeShellScriptBin "engelsystem-watch" ''
    set -euo pipefail

    PORT="''${1:-5080}"
    DIR="''${ENGELSYSTEM_DIR:-$(pwd)}"

    if [ ! -f "$DIR/public/index.php" ]; then
      echo "Error: Not in engelsystem directory. Set ENGELSYSTEM_DIR or run from project root."
      exit 1
    fi

    cd "$DIR"

    # Install dependencies if needed
    if [ ! -d "vendor" ]; then
      echo "Installing composer dependencies..."
      ${pkgs.php83Packages.composer}/bin/composer install
    fi

    if [ ! -d "node_modules" ]; then
      echo "Installing node dependencies..."
      ${pkgs.yarn}/bin/yarn install
    fi

    # Start PHP server in background
    echo "Starting PHP development server on http://localhost:$PORT"
    ${lib.php}/bin/php -S "0.0.0.0:$PORT" -t public/ &
    PHP_PID=$!

    # Cleanup on exit
    trap "kill $PHP_PID 2>/dev/null; exit" INT TERM EXIT

    # Start webpack watch
    echo "Starting frontend watch mode..."
    ${pkgs.yarn}/bin/yarn build:watch
  '';

  # Production server using PHP-FPM + nginx
  prodServer = pkgs.writeShellScriptBin "engelsystem-prod" ''
    set -euo pipefail

    PORT="''${PORT:-80}"
    APP_DIR="${packages.engelsystem}/share/engelsystem"
    STORAGE_DIR="''${STORAGE_DIR:-/var/lib/engelsystem/storage}"
    CONFIG_FILE="''${CONFIG_FILE:-}"

    # Create runtime directories
    mkdir -p "$STORAGE_DIR"/{app,cache,logs}
    chmod -R 755 "$STORAGE_DIR"

    # Create temporary nginx config
    NGINX_CONF=$(mktemp)
    cat > "$NGINX_CONF" << EOF
    error_log stderr;
    pid /tmp/nginx.pid;
    worker_processes auto;

    events {
      worker_connections 1024;
    }

    http {
      include ${pkgs.nginx}/conf/mime.types;
      client_body_temp_path /tmp/client_body_temp;
      fastcgi_temp_path /tmp/fastcgi_temp;
      proxy_temp_path /tmp/proxy_temp;
      scgi_temp_path /tmp/scgi_temp;
      uwsgi_temp_path /tmp/uwsgi_temp;

      access_log /dev/stdout;

      server {
        listen $PORT;
        root $APP_DIR/public;
        index index.php;

        location / {
          try_files \$uri \$uri/ /index.php\$is_args\$args;
        }

        location ~ \.php$ {
          fastcgi_pass unix:/tmp/php-fpm.sock;
          fastcgi_index index.php;
          fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
          include ${pkgs.nginx}/conf/fastcgi_params;
        }
      }
    }
    EOF

    # Create PHP-FPM config
    FPM_CONF=$(mktemp)
    cat > "$FPM_CONF" << EOF
    [global]
    error_log = /dev/stderr
    daemonize = no

    [www]
    user = nobody
    group = nogroup
    listen = /tmp/php-fpm.sock
    listen.owner = nobody
    listen.group = nogroup
    pm = dynamic
    pm.max_children = 50
    pm.start_servers = 5
    pm.min_spare_servers = 5
    pm.max_spare_servers = 35
    EOF

    echo "Starting Engelsystem production server on port $PORT"

    # Start PHP-FPM in background
    ${lib.phpProd}/bin/php-fpm -y "$FPM_CONF" &
    FPM_PID=$!

    # Cleanup on exit
    trap "kill $FPM_PID 2>/dev/null; rm -f $NGINX_CONF $FPM_CONF; exit" INT TERM EXIT

    # Start nginx
    exec ${pkgs.nginx}/bin/nginx -c "$NGINX_CONF" -g "daemon off;"
  '';

  # Migration script
  migrate = pkgs.writeShellScriptBin "engelsystem-migrate" ''
    set -euo pipefail

    DIR="''${ENGELSYSTEM_DIR:-$(pwd)}"

    if [ -f "$DIR/bin/migrate" ]; then
      exec ${lib.php}/bin/php "$DIR/bin/migrate" "$@"
    elif [ -f "${packages.engelsystem}/share/engelsystem/bin/migrate" ]; then
      exec ${lib.phpProd}/bin/php "${packages.engelsystem}/share/engelsystem/bin/migrate" "$@"
    else
      echo "Error: migrate script not found"
      exit 1
    fi
  '';

  # Test runner
  testRunner = pkgs.writeShellScriptBin "engelsystem-test" ''
    set -euo pipefail

    DIR="''${ENGELSYSTEM_DIR:-$(pwd)}"
    cd "$DIR"

    TESTSUITE="''${1:-}"

    if [ -n "$TESTSUITE" ]; then
      exec ${lib.php}/bin/php -d memory_limit=-1 vendor/bin/phpunit --testsuite "$TESTSUITE" "''${@:2}"
    else
      exec ${lib.php}/bin/php -d memory_limit=-1 vendor/bin/phpunit "$@"
    fi
  '';

  # Lint runner
  lintRunner = pkgs.writeShellScriptBin "engelsystem-lint" ''
    set -euo pipefail

    DIR="''${ENGELSYSTEM_DIR:-$(pwd)}"
    cd "$DIR"

    echo "=== PHP CodeSniffer ==="
    ${pkgs.php83Packages.composer}/bin/composer run phpcs || FAILED=1

    echo ""
    echo "=== PHPStan ==="
    ${pkgs.php83Packages.composer}/bin/composer phpstan || FAILED=1

    echo ""
    echo "=== ESLint & Prettier ==="
    ${pkgs.yarn}/bin/yarn lint || FAILED=1

    if [ "''${FAILED:-0}" = "1" ]; then
      echo ""
      echo "Some checks failed!"
      exit 1
    fi

    echo ""
    echo "All checks passed!"
  '';

in
{
  # Default app - development server
  default = mkApp devServer;

  # Development server
  dev = mkApp devServer;

  # Watch mode (frontend watching + PHP server)
  watch = mkApp watchMode;

  # Production server
  prod = mkApp prodServer;

  # Database migration
  migrate = mkApp migrate;

  # Test runner
  test = mkApp testRunner;

  # Lint runner
  lint = mkApp lintRunner;
}
