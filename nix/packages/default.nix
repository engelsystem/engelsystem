{ pkgs, lib, self }:

let
  frontend = import ./frontend.nix { inherit pkgs lib; };
  composer = import ./composer.nix { inherit pkgs lib; };
  translations = import ./translations.nix { inherit pkgs lib; };
  e2e = import ./e2e.nix { inherit pkgs lib; };

in
{
  # Main Engelsystem package (production-ready)
  engelsystem = pkgs.stdenv.mkDerivation {
    pname = "engelsystem";
    version = lib.version;

    src = lib.prodSource;

    nativeBuildInputs = [ pkgs.makeWrapper ];

    # Don't try to build - we're assembling pre-built components
    dontBuild = true;

    installPhase = ''
      runHook preInstall

      mkdir -p $out/share/engelsystem
      mkdir -p $out/bin

      # Copy application files
      cp -r bin $out/share/engelsystem/
      cp -r config $out/share/engelsystem/
      cp -r db $out/share/engelsystem/
      cp -r includes $out/share/engelsystem/
      cp -r public $out/share/engelsystem/
      cp -r src $out/share/engelsystem/
      cp -r resources/api $out/share/engelsystem/resources/api
      cp -r resources/views $out/share/engelsystem/resources/views
      cp composer.json composer.lock $out/share/engelsystem/

      # Create storage directory structure
      mkdir -p $out/share/engelsystem/storage/{app,cache,logs}

      # Copy pre-built vendor directory
      cp -r ${composer.vendorDir}/* $out/share/engelsystem/vendor/

      # Copy pre-built frontend assets
      cp -r ${frontend.assets}/assets $out/share/engelsystem/public/

      # Copy pre-built translations
      cp -r ${translations}/* $out/share/engelsystem/resources/lang/

      # Write version file
      echo -n "${lib.version}" > $out/share/engelsystem/storage/app/VERSION

      # Create wrapper scripts
      makeWrapper ${lib.phpProd}/bin/php $out/bin/engelsystem-migrate \
        --add-flags "$out/share/engelsystem/bin/migrate"

      makeWrapper ${lib.phpProd}/bin/php $out/bin/engelsystem-config2docs \
        --add-flags "$out/share/engelsystem/bin/config2docs"

      runHook postInstall
    '';

    meta = with pkgs.lib; {
      description = "Shift planning system for chaos events";
      homepage = "https://engelsystem.de";
      license = licenses.gpl2Plus;
      maintainers = [ ];
      platforms = platforms.all;
    };
  };

  # Export sub-packages for use in other modules
  inherit (frontend) assets;
  inherit (composer) vendorDir vendorDev;
  inherit translations;

  # E2E test package (pre-built with dependencies)
  inherit e2e;

  # Development package with dev dependencies
  engelsystem-dev = pkgs.stdenv.mkDerivation {
    pname = "engelsystem-dev";
    version = lib.version;

    src = lib.fullSource;

    nativeBuildInputs = [ pkgs.makeWrapper ];

    dontBuild = true;

    installPhase = ''
      runHook preInstall

      mkdir -p $out/share/engelsystem
      cp -r . $out/share/engelsystem/

      # Use dev vendor directory
      rm -rf $out/share/engelsystem/vendor
      cp -r ${composer.vendorDev}/* $out/share/engelsystem/vendor/

      # Copy pre-built frontend assets
      mkdir -p $out/share/engelsystem/public/assets
      cp -r ${frontend.assets}/assets/* $out/share/engelsystem/public/assets/

      # Copy pre-built translations
      cp -r ${translations}/* $out/share/engelsystem/resources/lang/

      runHook postInstall
    '';
  };
}
