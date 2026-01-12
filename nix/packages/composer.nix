{ pkgs, lib }:

let
  # Composer dependencies for production
  vendorDir = pkgs.stdenv.mkDerivation {
    pname = "engelsystem-vendor";
    version = lib.version;

    src = pkgs.lib.cleanSourceWith {
      src = ../..;
      filter = path: type:
        let
          baseName = baseNameOf (toString path);
        in
        baseName == "composer.json" || baseName == "composer.lock";
    };

    nativeBuildInputs = [
      lib.php
      pkgs.php83Packages.composer
      pkgs.cacert
      pkgs.git
      pkgs.unzip
    ];

    buildPhase = ''
      runHook preBuild

      export HOME=$(mktemp -d)
      export COMPOSER_HOME=$HOME/.composer
      export COMPOSER_CACHE_DIR=$HOME/.composer/cache

      # Install production dependencies only
      composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --optimize-autoloader \
        --ignore-platform-reqs

      runHook postBuild
    '';

    installPhase = ''
      runHook preInstall
      mkdir -p $out
      cp -r vendor/* $out/
      runHook postInstall
    '';

    # For reproducibility, we could use outputHash but composer isn't perfectly deterministic
    # In production, consider using composer2nix or nix-phps tools
  };

  # Composer dependencies with dev packages (for testing)
  vendorDev = pkgs.stdenv.mkDerivation {
    pname = "engelsystem-vendor-dev";
    version = lib.version;

    src = pkgs.lib.cleanSourceWith {
      src = ../..;
      filter = path: type:
        let
          baseName = baseNameOf (toString path);
        in
        baseName == "composer.json" || baseName == "composer.lock";
    };

    nativeBuildInputs = [
      lib.php
      pkgs.php83Packages.composer
      pkgs.cacert
      pkgs.git
      pkgs.unzip
    ];

    buildPhase = ''
      runHook preBuild

      export HOME=$(mktemp -d)
      export COMPOSER_HOME=$HOME/.composer
      export COMPOSER_CACHE_DIR=$HOME/.composer/cache

      # Install all dependencies including dev
      composer install \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --ignore-platform-reqs

      runHook postBuild
    '';

    installPhase = ''
      runHook preInstall
      mkdir -p $out
      cp -r vendor/* $out/
      runHook postInstall
    '';
  };

in
{
  inherit vendorDir vendorDev;

  # Script to install composer dependencies in development
  installScript = pkgs.writeShellScriptBin "engelsystem-composer-install" ''
    set -euo pipefail
    cd "''${1:-.}"
    ${pkgs.php83Packages.composer}/bin/composer install --no-interaction
  '';
}
