{ pkgs, lib }:

let
  # E2E test source files only
  e2eSrc = pkgs.lib.cleanSourceWith {
    src = ../..;
    filter = path: type:
      let
        baseName = baseNameOf (toString path);
        relativePath = pkgs.lib.removePrefix (toString ../..) (toString path);
      in
      # Include e2e directory contents
      (pkgs.lib.hasPrefix "/e2e" relativePath
        && baseName != "node_modules"
        && baseName != "test-results"
        && baseName != "e2e-report"
        && baseName != "e2e-results.xml"
        && baseName != ".playwright")
      # Include root-level e2e dir marker
      || baseName == "e2e" && type == "directory";
  };

in
pkgs.buildNpmPackage {
  pname = "engelsystem-e2e";
  version = lib.version;

  src = "${e2eSrc}/e2e";

  # Hash of npm dependencies - computed via:
  # nix build .#e2e 2>&1 | grep "got:"
  npmDepsHash = "sha256-MKx+1EgauMBAjOvKPnfyY8fADQHtb7HZ9uPo1xFJXMw=";

  # Playwright compiles TypeScript on the fly - no build step needed
  dontNpmBuild = true;

  # Don't run npm test during build
  doCheck = false;

  # Copy everything to output (node_modules will be populated by buildNpmPackage)
  installPhase = ''
    runHook preInstall

    mkdir -p $out
    cp -r . $out/

    runHook postInstall
  '';

  meta = with pkgs.lib; {
    description = "E2E tests for Engelsystem using Playwright";
    license = licenses.gpl2Plus;
  };
}
