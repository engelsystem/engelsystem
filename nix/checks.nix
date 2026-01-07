{ pkgs, lib, packages }:

{
  # PHP CodeSniffer check
  phpcs = pkgs.stdenv.mkDerivation {
    name = "engelsystem-phpcs-check";
    src = lib.fullSource;

    nativeBuildInputs = [
      lib.php
      pkgs.php83Packages.composer
      pkgs.cacert
      pkgs.git
    ];

    buildPhase = ''
      export HOME=$(mktemp -d)
      export COMPOSER_HOME=$HOME/.composer

      # Install dev dependencies
      composer install --no-interaction --prefer-dist

      # Run PHP CodeSniffer
      echo "Running PHP CodeSniffer..."
      composer run phpcs
    '';

    installPhase = ''
      mkdir -p $out
      echo "PHP CodeSniffer passed" > $out/result
    '';
  };

  # PHPStan static analysis
  phpstan = pkgs.stdenv.mkDerivation {
    name = "engelsystem-phpstan-check";
    src = lib.fullSource;

    nativeBuildInputs = [
      lib.php
      pkgs.php83Packages.composer
      pkgs.cacert
      pkgs.git
    ];

    buildPhase = ''
      export HOME=$(mktemp -d)
      export COMPOSER_HOME=$HOME/.composer

      # Install dev dependencies
      composer install --no-interaction --prefer-dist

      # Run PHPStan
      echo "Running PHPStan..."
      composer phpstan
    '';

    installPhase = ''
      mkdir -p $out
      echo "PHPStan passed" > $out/result
    '';
  };

  # PHPUnit tests (unit tests only, no DB required)
  phpunit-unit = pkgs.stdenv.mkDerivation {
    name = "engelsystem-phpunit-unit-check";
    src = lib.fullSource;

    nativeBuildInputs = [
      lib.php
      pkgs.php83Packages.composer
      pkgs.cacert
      pkgs.git
    ];

    buildPhase = ''
      export HOME=$(mktemp -d)
      export COMPOSER_HOME=$HOME/.composer

      # Install dev dependencies
      composer install --no-interaction --prefer-dist

      # Run unit tests only
      echo "Running PHPUnit unit tests..."
      vendor/bin/phpunit --testsuite Unit
    '';

    installPhase = ''
      mkdir -p $out
      echo "PHPUnit unit tests passed" > $out/result
    '';
  };

  # ESLint check
  eslint = pkgs.stdenv.mkDerivation {
    name = "engelsystem-eslint-check";
    src = lib.fullSource;

    nativeBuildInputs = [
      lib.nodejs
      pkgs.yarn
      pkgs.cacert
    ];

    buildPhase = ''
      export HOME=$(mktemp -d)

      # Install dependencies
      yarn install --frozen-lockfile

      # Run ESLint
      echo "Running ESLint..."
      yarn lint:eslint
    '';

    installPhase = ''
      mkdir -p $out
      echo "ESLint passed" > $out/result
    '';
  };

  # Prettier check
  prettier = pkgs.stdenv.mkDerivation {
    name = "engelsystem-prettier-check";
    src = lib.fullSource;

    nativeBuildInputs = [
      lib.nodejs
      pkgs.yarn
      pkgs.cacert
    ];

    buildPhase = ''
      export HOME=$(mktemp -d)

      # Install dependencies
      yarn install --frozen-lockfile

      # Run Prettier check
      echo "Running Prettier..."
      yarn lint:prettier
    '';

    installPhase = ''
      mkdir -p $out
      echo "Prettier passed" > $out/result
    '';
  };

  # EditorConfig check
  editorconfig = pkgs.stdenv.mkDerivation {
    name = "engelsystem-editorconfig-check";
    src = lib.fullSource;

    nativeBuildInputs = [
      lib.nodejs
      pkgs.yarn
      pkgs.cacert
    ];

    buildPhase = ''
      export HOME=$(mktemp -d)

      # Install dependencies
      yarn install --frozen-lockfile

      # Run EditorConfig checker
      echo "Running EditorConfig checker..."
      yarn lint:ec
    '';

    installPhase = ''
      mkdir -p $out
      echo "EditorConfig passed" > $out/result
    '';
  };

  # Frontend build check (ensures assets build successfully)
  frontend-build = pkgs.stdenv.mkDerivation {
    name = "engelsystem-frontend-build-check";
    src = lib.fullSource;

    nativeBuildInputs = [
      lib.nodejs
      pkgs.yarn
      pkgs.cacert
    ];

    buildPhase = ''
      export HOME=$(mktemp -d)
      export NODE_ENV=production

      # Install dependencies
      yarn install --frozen-lockfile

      # Build frontend
      echo "Building frontend assets..."
      yarn build
    '';

    installPhase = ''
      mkdir -p $out
      cp -r public/assets $out/
      echo "Frontend build passed" > $out/result
    '';
  };

  # Composer validate check
  composer-validate = pkgs.stdenv.mkDerivation {
    name = "engelsystem-composer-validate-check";
    src = lib.fullSource;

    nativeBuildInputs = [
      lib.php
      pkgs.php83Packages.composer
    ];

    buildPhase = ''
      echo "Validating composer.json..."
      composer validate --strict
    '';

    installPhase = ''
      mkdir -p $out
      echo "Composer validation passed" > $out/result
    '';
  };
}
