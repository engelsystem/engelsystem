with import <nixpkgs> {};

mkShell {
  name = "handtuchsystem-shell";
  buildInputs = [
    nodejs
    php
    phpPackages.composer
    yarn
  ];
}
