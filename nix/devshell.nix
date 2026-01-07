{ pkgs, lib }:

pkgs.mkShell {
  name = "engelsystem-dev";

  buildInputs = [
    # PHP with all extensions
    lib.php
    pkgs.php83Packages.composer

    # Node.js and Yarn
    lib.nodejs
    pkgs.yarn

    # Database tools
    pkgs.mariadb.client

    # Translation tools
    pkgs.gettext

    # Development tools
    pkgs.git
    pkgs.gnumake
    pkgs.jq
    pkgs.yq

    # Code quality tools (native alternatives)
    pkgs.nodePackages.prettier
    pkgs.nodePackages.eslint

    # Container tools
    pkgs.docker-client
    pkgs.minikube
    pkgs.kubectl

    # Useful utilities
    pkgs.curl
    pkgs.wget
    pkgs.httpie
  ];

  shellHook = ''
    # PHP environment is configured via withExtensions in lib.nix
    # No need to override PHP_INI_SCAN_DIR

    # Composer configuration
    export COMPOSER_HOME="$PWD/.composer"
    export PATH="$COMPOSER_HOME/vendor/bin:$PATH"

    # Node modules binaries
    export PATH="$PWD/node_modules/.bin:$PATH"

    # Database defaults (can be overridden)
    export DB_HOST="''${DB_HOST:-127.0.0.1}"
    export DB_PORT="''${DB_PORT:-3306}"
    export DB_DATABASE="''${DB_DATABASE:-engelsystem}"
    export DB_USERNAME="''${DB_USERNAME:-engelsystem}"
    export DB_PASSWORD="''${DB_PASSWORD:-engelsystem}"

    # Application settings
    export APP_ENV="''${APP_ENV:-development}"
    export APP_URL="''${APP_URL:-http://localhost:5080}"

    # Development server port
    export DEV_PORT="''${DEV_PORT:-5080}"

    # Helper functions
    function es-install() {
      echo "Installing dependencies..."
      composer install
      yarn install
    }

    function es-build() {
      echo "Building frontend assets..."
      yarn build
    }

    function es-watch() {
      echo "Starting frontend watch mode..."
      yarn build:watch
    }

    function es-migrate() {
      echo "Running database migrations..."
      ./bin/migrate
    }

    function es-serve() {
      local port="''${1:-$DEV_PORT}"
      echo "Starting PHP development server on port $port..."
      php -S "0.0.0.0:$port" -t public/
    }

    function es-test() {
      echo "Running tests..."
      composer phpunit
    }

    function es-test-unit() {
      echo "Running unit tests..."
      vendor/bin/phpunit --testsuite Unit
    }

    function es-test-feature() {
      echo "Running feature tests..."
      vendor/bin/phpunit --testsuite Feature
    }

    function es-test-coverage() {
      echo "Running tests with coverage..."
      php -d pcov.enabled=1 -d pcov.directory=. vendor/bin/phpunit --coverage-text --coverage-html ./public/coverage/
    }

    function es-lint() {
      echo "Running all linters..."
      composer run phpcs
      composer phpstan
      yarn lint
    }

    function es-lint-fix() {
      echo "Fixing linting issues..."
      composer run phpcbf || true
      yarn lint:fix
    }

    function es-phpcs() {
      echo "Running PHP CodeSniffer..."
      composer run phpcs
    }

    function es-phpcbf() {
      echo "Fixing PHP code style..."
      composer run phpcbf
    }

    function es-phpstan() {
      echo "Running PHPStan..."
      composer phpstan
    }

    function es-translations() {
      echo "Compiling translation files..."
      find resources/lang/ -type f -name '*.po' -exec sh -c 'msgfmt "''${1%.*}.po" -o"''${1%.*}.mo"' shell {} \;
    }

    function es-db-start() {
      echo "Starting local MySQL database with Docker..."
      docker run -d \
        --name engelsystem-db \
        -e MYSQL_ROOT_PASSWORD=root \
        -e MYSQL_DATABASE=$DB_DATABASE \
        -e MYSQL_USER=$DB_USERNAME \
        -e MYSQL_PASSWORD=$DB_PASSWORD \
        -p $DB_PORT:3306 \
        mariadb:10.7
      echo "Waiting for database to be ready..."
      sleep 10
      echo "Database started on port $DB_PORT"
    }

    function es-db-stop() {
      echo "Stopping local database..."
      docker stop engelsystem-db && docker rm engelsystem-db
    }

    function es-db-shell() {
      mysql -h $DB_HOST -P $DB_PORT -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE
    }

    # Display available commands
    echo ""
    echo "Engelsystem Development Environment"
    echo "===================================="
    echo ""
    echo "Available commands:"
    echo "  es-install      - Install all dependencies"
    echo "  es-build        - Build frontend assets"
    echo "  es-watch        - Watch mode for frontend"
    echo "  es-serve [port] - Start PHP dev server (default: $DEV_PORT)"
    echo "  es-migrate      - Run database migrations"
    echo ""
    echo "Testing:"
    echo "  es-test         - Run all tests"
    echo "  es-test-unit    - Run unit tests only"
    echo "  es-test-feature - Run feature tests only"
    echo "  es-test-coverage - Run tests with coverage report"
    echo ""
    echo "Linting:"
    echo "  es-lint         - Run all linters"
    echo "  es-lint-fix     - Auto-fix linting issues"
    echo "  es-phpcs        - PHP CodeSniffer only"
    echo "  es-phpcbf       - PHP code fixer only"
    echo "  es-phpstan      - PHPStan analysis only"
    echo ""
    echo "Database (Docker):"
    echo "  es-db-start     - Start local MySQL (Docker)"
    echo "  es-db-stop      - Stop local MySQL"
    echo "  es-db-shell     - MySQL shell"
    echo ""
    echo "Database (Minikube/Kubernetes):"
    echo "  nix run .#db-start-minikube  - Start MariaDB in Minikube"
    echo "  nix run .#db-stop-minikube   - Stop MariaDB in Minikube"
    echo "  nix run .#db-shell-minikube  - Connect to Minikube DB"
    echo ""
    echo "Other:"
    echo "  es-translations - Compile .po to .mo files"
    echo ""
  '';
}
