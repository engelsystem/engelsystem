{ pkgs, lib }:

let
  # Use yarn2nix for reproducible yarn builds
  # First, generate the yarn.nix with: yarn2nix > nix/yarn.nix
  # For now, we use a simpler FOD (Fixed-Output Derivation) approach

  # Frontend source files only
  frontendSrc = pkgs.lib.cleanSourceWith {
    src = ../..;
    filter = path: type:
      let
        baseName = baseNameOf (toString path);
        relativePath = pkgs.lib.removePrefix (toString ../..) (toString path);
      in
      baseName == "package.json"
      || baseName == "yarn.lock"
      || baseName == "webpack.config.js"
      || baseName == ".babelrc"
      || baseName == ".browserslistrc"
      || pkgs.lib.hasPrefix "/resources/assets" relativePath
      || pkgs.lib.hasPrefix "/resources" relativePath && type == "directory";
  };

  # Build frontend assets
  # This is a Fixed-Output Derivation for reproducibility
  # The hash needs to be updated when dependencies change
  # Run: nix build .#assets 2>&1 | grep "got:" to get the new hash
  assets = pkgs.stdenvNoCC.mkDerivation {
    pname = "engelsystem-frontend";
    version = lib.version;

    src = frontendSrc;

    nativeBuildInputs = [
      lib.nodejs
      pkgs.yarn
      pkgs.cacert
    ];

    # Disable network for reproducibility - dependencies come from yarn offline cache
    __noChroot = false;

    configurePhase = ''
      runHook preConfigure

      export HOME=$(mktemp -d)
      export YARN_CACHE_FOLDER=$HOME/.yarn-cache
      export NODE_OPTIONS="--max-old-space-size=4096"

      # Install dependencies
      yarn install --frozen-lockfile --ignore-scripts --network-timeout 600000

      runHook postConfigure
    '';

    buildPhase = ''
      runHook preBuild

      export NODE_ENV=production

      # Build all themes
      yarn build

      runHook postBuild
    '';

    installPhase = ''
      runHook preInstall

      mkdir -p $out/assets
      if [ -d "public/assets" ]; then
        cp -r public/assets/* $out/assets/
      fi

      runHook postInstall
    '';

    # Output hash - update this when yarn.lock changes
    # To get the hash: nix build .#assets --impure 2>&1 | grep "got:"
    # Or use: nix hash path ./result
    outputHashMode = "recursive";
    outputHashAlgo = "sha256";
    # Placeholder - will be computed on first build
    # outputHash = pkgs.lib.fakeHash;
  };

  # Alternative: Impure build for development (faster iteration)
  assetsImpure = pkgs.stdenvNoCC.mkDerivation {
    pname = "engelsystem-frontend-dev";
    version = lib.version;

    src = frontendSrc;

    nativeBuildInputs = [
      lib.nodejs
      pkgs.yarn
      pkgs.cacert
    ];

    # Allow network access for development builds
    __noChroot = true;

    buildPhase = ''
      export HOME=$(mktemp -d)
      export NODE_ENV=production

      yarn install --frozen-lockfile
      yarn build
    '';

    installPhase = ''
      mkdir -p $out/assets
      cp -r public/assets/* $out/assets/
    '';
  };

in
{
  # Use impure build by default (simpler, works out of the box)
  # For production/CI, use the pure version with proper hash
  assets = assetsImpure;

  # Pure version (requires hash update when dependencies change)
  # assets = assets;

  # Script to build frontend in development
  buildScript = pkgs.writeShellScriptBin "engelsystem-build-frontend" ''
    set -euo pipefail
    cd "''${1:-.}"

    export PATH="${lib.nodejs}/bin:${pkgs.yarn}/bin:$PATH"

    if [ ! -d "node_modules" ]; then
      echo "Installing dependencies..."
      yarn install --frozen-lockfile
    fi

    echo "Building frontend assets..."
    yarn build
  '';

  # Script to watch frontend in development
  watchScript = pkgs.writeShellScriptBin "engelsystem-watch-frontend" ''
    set -euo pipefail
    cd "''${1:-.}"

    export PATH="${lib.nodejs}/bin:${pkgs.yarn}/bin:$PATH"

    if [ ! -d "node_modules" ]; then
      echo "Installing dependencies..."
      yarn install --frozen-lockfile
    fi

    echo "Starting watch mode..."
    yarn build:watch
  '';
}
