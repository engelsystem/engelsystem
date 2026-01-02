{ pkgs, lib, packages }:

let
  # Helper to create a flake app
  mkApp = drv: {
    type = "app";
    program = "${drv}/bin/${drv.pname or drv.name}";
  };

  # Helper to auto-detect minikube database
  dbEnvSetup = ''
    # Auto-detect minikube database if not configured
    if [ -z "''${MYSQL_HOST:-}" ]; then
      if ${pkgs.kubectl}/bin/kubectl -n engelsystem get svc mariadb >/dev/null 2>&1; then
        export MYSQL_HOST="$(${pkgs.minikube}/bin/minikube ip 2>/dev/null)"
        export MYSQL_PORT="$(${pkgs.kubectl}/bin/kubectl -n engelsystem get svc mariadb -o jsonpath='{.spec.ports[0].nodePort}' 2>/dev/null)"
        export MYSQL_DATABASE="''${MYSQL_DATABASE:-engelsystem}"
        export MYSQL_USER="''${MYSQL_USER:-engelsystem}"
        export MYSQL_PASSWORD="''${MYSQL_PASSWORD:-engelsystem}"
      fi
    fi
  '';

  # Development server script
  devServer = pkgs.writeShellScriptBin "engelsystem-dev" ''
    set -euo pipefail

    PORT="''${1:-5080}"
    DIR="''${ENGELSYSTEM_DIR:-$(pwd)}"

    if [ ! -f "$DIR/public/index.php" ]; then
      echo "Error: Not in engelsystem directory. Set ENGELSYSTEM_DIR or run from project root."
      exit 1
    fi

    ${dbEnvSetup}

    echo "Starting Engelsystem development server on http://localhost:$PORT"
    [ -n "''${MYSQL_HOST:-}" ] && echo "Database: $MYSQL_HOST:$MYSQL_PORT"
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

  # Migration script (local dev only - doesn't trigger package build)
  migrate = pkgs.writeShellScriptBin "engelsystem-migrate" ''
    set -euo pipefail

    DIR="''${ENGELSYSTEM_DIR:-$(pwd)}"

    if [ ! -f "$DIR/bin/migrate" ]; then
      echo "Error: bin/migrate not found. Run from engelsystem project root."
      exit 1
    fi

    ${dbEnvSetup}

    exec ${lib.php}/bin/php "$DIR/bin/migrate" "$@"
  '';

  # Migration script for production (uses packaged version)
  migrateProd = pkgs.writeShellScriptBin "engelsystem-migrate-prod" ''
    set -euo pipefail

    if [ -f "${packages.engelsystem}/share/engelsystem/bin/migrate" ]; then
      exec ${lib.phpProd}/bin/php "${packages.engelsystem}/share/engelsystem/bin/migrate" "$@"
    else
      echo "Error: packaged migrate script not found"
      exit 1
    fi
  '';

  # Test runner
  testRunner = pkgs.writeShellScriptBin "engelsystem-test" ''
    set -euo pipefail

    DIR="''${ENGELSYSTEM_DIR:-$(pwd)}"
    cd "$DIR"

    ${dbEnvSetup}

    TESTSUITE="''${1:-}"

    if [ -n "$TESTSUITE" ]; then
      exec ${lib.php}/bin/php -d memory_limit=-1 -d apc.enable_cli=1 vendor/bin/phpunit --testsuite "$TESTSUITE" "''${@:2}"
    else
      exec ${lib.php}/bin/php -d memory_limit=-1 -d apc.enable_cli=1 vendor/bin/phpunit "$@"
    fi
  '';

  # Test coverage runner - generates coverage reports with summary
  testCoverageRunner = pkgs.writeShellScriptBin "engelsystem-test-coverage" ''
    set -euo pipefail

    DIR="''${ENGELSYSTEM_DIR:-$(pwd)}"
    cd "$DIR"

    ${dbEnvSetup}

    COVERAGE_DIR="coverage"
    FILTER="''${1:-}"

    # Create coverage directory
    mkdir -p "$COVERAGE_DIR"

    echo "=== Running Tests with Coverage ==="
    echo ""

    # Build phpunit command
    CMD="${lib.php}/bin/php -d memory_limit=-1 -d apc.enable_cli=1 vendor/bin/phpunit"
    CMD="$CMD --coverage-text"
    CMD="$CMD --coverage-html $COVERAGE_DIR/html"
    CMD="$CMD --coverage-clover $COVERAGE_DIR/clover.xml"

    if [ -n "$FILTER" ]; then
      CMD="$CMD --filter $FILTER"
      echo "Filter: $FILTER"
      echo ""
    fi

    # Run tests and capture output
    COVERAGE_OUTPUT=$($CMD 2>&1) || {
      echo "$COVERAGE_OUTPUT"
      echo ""
      echo "=== TESTS FAILED ==="
      exit 1
    }

    echo "$COVERAGE_OUTPUT"
    echo ""

    # Parse and highlight coverage issues
    echo "=== Coverage Summary ==="
    echo ""

    # Extract classes with less than 100% coverage
    echo "Classes with incomplete coverage:"
    echo "$COVERAGE_OUTPUT" | grep -E "^\s+[A-Za-z\\\\]+" | grep -v "100.00%" | head -20 || echo "  None found (good!)"

    echo ""
    echo "Coverage reports generated:"
    echo "  - Text: console output above"
    echo "  - HTML: $COVERAGE_DIR/html/index.html"
    echo "  - Clover: $COVERAGE_DIR/clover.xml"
    echo ""

    # Check for 100% coverage requirement
    if echo "$COVERAGE_OUTPUT" | grep -qE "^\s+[A-Za-z\\\\]+" | grep -v "100.00%"; then
      echo "WARNING: Some classes do not have 100% coverage!"
      echo "Review the coverage report and add missing tests."
    else
      echo "All tested classes have 100% coverage."
    fi
  '';

  # Dev setup - starts everything needed for development
  devSetup = pkgs.writeShellScriptBin "engelsystem-dev-setup" ''
    set -euo pipefail

    DIR="''${ENGELSYSTEM_DIR:-$(pwd)}"
    cd "$DIR"

    echo "=== Engelsystem Development Setup ==="
    echo ""

    # Check/start minikube database
    echo "Step 1: Checking database..."
    if ! ${pkgs.kubectl}/bin/kubectl -n engelsystem get deployment mariadb >/dev/null 2>&1; then
      echo "Starting database in Minikube..."
      ${pkgs.bash}/bin/bash -c "$(nix-build --no-out-link -A apps.db-start-minikube.program ${../flake.nix} 2>/dev/null || echo 'nix run .#db-start-minikube')" || {
        echo "Please run: nix run .#db-start-minikube"
        exit 1
      }
    else
      echo "Database already running."
    fi

    # Get database connection info
    NODE_PORT=$(${pkgs.kubectl}/bin/kubectl -n engelsystem get svc mariadb -o jsonpath='{.spec.ports[0].nodePort}' 2>/dev/null || echo "31402")
    MINIKUBE_IP=$(${pkgs.minikube}/bin/minikube ip 2>/dev/null || echo "192.168.105.2")

    export MYSQL_HOST="$MINIKUBE_IP"
    export MYSQL_PORT="$NODE_PORT"
    export MYSQL_DATABASE="''${MYSQL_DATABASE:-engelsystem}"
    export MYSQL_USER="''${MYSQL_USER:-engelsystem}"
    export MYSQL_PASSWORD="''${MYSQL_PASSWORD:-engelsystem}"

    echo ""
    echo "Database: $MYSQL_HOST:$MYSQL_PORT"
    echo ""

    # Run migrations
    echo "Step 2: Running migrations..."
    ${lib.php}/bin/php bin/migrate || echo "Migrations failed or already up to date"
    echo ""

    # Check dependencies
    echo "Step 3: Checking dependencies..."
    if [ ! -d "vendor" ]; then
      echo "Installing composer dependencies..."
      ${pkgs.php83Packages.composer}/bin/composer install
    fi

    if [ ! -d "node_modules" ]; then
      echo "Installing node dependencies..."
      ${pkgs.yarn}/bin/yarn install
    fi

    # Build assets if needed
    if [ ! -d "public/assets" ]; then
      echo "Building frontend assets..."
      ${pkgs.yarn}/bin/yarn build
    fi

    echo ""
    echo "=== Development Environment Ready ==="
    echo ""
    echo "Database:"
    echo "  export MYSQL_HOST=$MYSQL_HOST"
    echo "  export MYSQL_PORT=$MYSQL_PORT"
    echo ""
    echo "Commands:"
    echo "  nix run .#dev         - Start dev server"
    echo "  nix run .#test        - Run tests"
    echo "  nix run .#migrate     - Run migrations"
    echo "  nix run .#lint        - Run linters"
    echo ""
  '';

  # Test data seeder script
  seedTestData = pkgs.writeShellScriptBin "engelsystem-seed-test-data" ''
    set -euo pipefail

    DIR="''${ENGELSYSTEM_DIR:-$(pwd)}"

    if [ ! -f "$DIR/bin/seed-test-data" ]; then
      echo "Error: bin/seed-test-data not found. Run from engelsystem project root."
      exit 1
    fi

    ${dbEnvSetup}

    exec ${lib.php}/bin/php "$DIR/bin/seed-test-data" "$@"
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

  # E2E test runner (pure nix - pre-built package with all dependencies)
  testE2e = pkgs.writeShellScriptBin "engelsystem-test-e2e" ''
    set -euo pipefail

    DIR="''${ENGELSYSTEM_DIR:-$(pwd)}"

    # Get git shortref for unique database name
    SHORTREF=$(${pkgs.git}/bin/git -C "$DIR" rev-parse --short HEAD 2>/dev/null || echo "local")
    DB_NAME="engelsystem_e2e_''${SHORTREF}"

    # Database connection (support minikube auto-detection)
    ${dbEnvSetup}
    DB_HOST="''${MYSQL_HOST:-127.0.0.1}"
    DB_PORT="''${MYSQL_PORT:-3306}"
    DB_USER="''${MYSQL_USER:-engelsystem}"
    DB_PASS="''${MYSQL_PASSWORD:-engelsystem}"

    # Point Playwright to nix-provided browsers
    export PLAYWRIGHT_BROWSERS_PATH="${pkgs.playwright-driver.browsers}"
    export PLAYWRIGHT_SKIP_BROWSER_DOWNLOAD=1

    # Server PID tracking
    SERVER_PID=""

    # Cleanup function
    cleanup() {
      local EXIT_CODE=$?
      echo ""
      echo "=== Cleaning up ==="

      # Kill PHP server if running
      if [ -n "$SERVER_PID" ] && kill -0 "$SERVER_PID" 2>/dev/null; then
        echo "Stopping PHP server (PID $SERVER_PID)..."
        kill "$SERVER_PID" 2>/dev/null || true
        wait "$SERVER_PID" 2>/dev/null || true
      fi

      # Also kill any other PHP servers on port 5080
      ${pkgs.lsof}/bin/lsof -ti:5080 2>/dev/null | xargs -r kill 2>/dev/null || true

      # Only drop database if it was an isolated test database
      if [ "$ISOLATED_DB" = "true" ]; then
        echo "Dropping isolated test database $DB_NAME..."
        ${pkgs.mariadb.client}/bin/mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" \
          --skip-ssl -e "DROP DATABASE IF EXISTS \`$DB_NAME\`;" 2>/dev/null || true
      else
        echo "Keeping shared 'engelsystem' database."
      fi

      echo "Cleanup complete."
      exit $EXIT_CODE
    }

    trap cleanup EXIT INT TERM

    echo "=== E2E Test Runner (Pure Nix) ==="
    echo ""
    echo "Working directory: $DIR"
    echo "Database: $DB_NAME @ $DB_HOST:$DB_PORT"
    echo "Browsers: $PLAYWRIGHT_BROWSERS_PATH"
    echo "E2E Package: ${packages.e2e}"
    echo ""

    # Try to create isolated test database (requires CREATE privilege)
    echo "=== Setting up test database ==="
    ISOLATED_DB=false
    if ${pkgs.mariadb.client}/bin/mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" \
        --skip-ssl -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null; then
      echo "Created isolated database: $DB_NAME"
      ISOLATED_DB=true
    else
      echo "WARNING: Cannot create isolated database (user lacks CREATE privilege)."
      echo "         Falling back to existing 'engelsystem' database."
      echo "         For CI, grant CREATE privilege to the database user."
      DB_NAME="engelsystem"
    fi
    echo ""

    # Export for PHP
    export MYSQL_HOST="$DB_HOST"
    export MYSQL_PORT="$DB_PORT"
    export MYSQL_DATABASE="$DB_NAME"
    export MYSQL_USER="$DB_USER"
    export MYSQL_PASSWORD="$DB_PASS"
    export E2E_ISOLATED_DB="$ISOLATED_DB"

    # Run migrations
    echo "=== Running migrations ==="
    cd "$DIR"
    ${lib.php}/bin/php bin/migrate
    echo ""

    # Seed test data
    echo "=== Seeding test data ==="
    ${lib.php}/bin/php bin/seed-test-data
    echo ""

    # Start PHP server
    echo "=== Starting PHP dev server ==="
    ${lib.php}/bin/php -S 0.0.0.0:5080 -t public/ &
    SERVER_PID=$!
    echo "Server started with PID $SERVER_PID"

    # Wait for server to be ready
    echo "Waiting for server to be ready..."
    for i in $(seq 1 30); do
      if ${pkgs.curl}/bin/curl -s -o /dev/null -w "" http://localhost:5080/ 2>/dev/null; then
        echo "Server is ready!"
        break
      fi
      if [ $i -eq 30 ]; then
        echo "ERROR: Server failed to start after 30 seconds"
        exit 1
      fi
      sleep 1
    done
    echo ""

    # Run E2E tests from pre-built e2e package
    echo "=== Running E2E tests on all browsers ==="
    export APP_URL="http://localhost:5080"
    export CI=true

    # Create writable copy of e2e package (nix store is read-only)
    E2E_WORKDIR=$(mktemp -d)
    echo "Copying E2E tests to writable directory: $E2E_WORKDIR"
    cp -r "${packages.e2e}"/* "$E2E_WORKDIR/"
    chmod -R u+w "$E2E_WORKDIR"

    # Run playwright from writable copy
    cd "$E2E_WORKDIR"
    ${lib.nodejs}/bin/node node_modules/.bin/playwright test "$@"
    TEST_EXIT_CODE=$?

    # Copy results back to project if tests ran
    if [ -d "$E2E_WORKDIR/test-results" ]; then
      echo ""
      echo "Test results saved to: $E2E_WORKDIR/test-results"
    fi
    if [ -d "$E2E_WORKDIR/e2e-report" ]; then
      echo "HTML report: $E2E_WORKDIR/e2e-report/index.html"
    fi

    # Exit with test exit code (cleanup will still run via trap)
    if [ $TEST_EXIT_CODE -ne 0 ]; then
      exit $TEST_EXIT_CODE
    fi

    echo ""
    echo "=== E2E Tests Complete ==="
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

  # Database migration (local dev)
  migrate = mkApp migrate;

  # Database migration (production package)
  migrate-prod = mkApp migrateProd;

  # Test runner
  test = mkApp testRunner;

  # Test coverage runner
  test-coverage = mkApp testCoverageRunner;

  # Lint runner
  lint = mkApp lintRunner;

  # Dev setup (one-command development environment)
  setup = mkApp devSetup;

  # Test data seeder
  seed-test-data = mkApp seedTestData;

  # E2E test runner (pure nix - all browsers)
  test-e2e = mkApp testE2e;
}
