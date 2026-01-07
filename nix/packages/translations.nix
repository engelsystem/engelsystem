{ pkgs, lib }:

# Build translation .mo files from .po files
pkgs.stdenv.mkDerivation {
  pname = "engelsystem-translations";
  version = lib.version;

  src = pkgs.lib.cleanSourceWith {
    src = ../..;
    filter = path: type:
      let
        baseName = baseNameOf (toString path);
      in
      type == "directory"
      || builtins.match ".*/resources/lang.*" (toString path) != null;
  };

  nativeBuildInputs = [ pkgs.gettext ];

  buildPhase = ''
    runHook preBuild

    find resources/lang -type f -name '*.po' -exec sh -c '
      msgfmt "''${1%.*}.po" -o "''${1%.*}.mo"
    ' shell {} \;

    runHook postBuild
  '';

  installPhase = ''
    runHook preInstall
    mkdir -p $out
    cp -r resources/lang/* $out/
    runHook postInstall
  '';
}
