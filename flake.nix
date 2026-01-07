{
  description = "Engelsystem - Shift planning system for chaos events";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-25.11";
    flake-utils.url = "github:numtide/flake-utils";

    # For better PHP/Composer support
    phps.url = "github:fossar/nix-phps";
    phps.inputs.nixpkgs.follows = "nixpkgs";
  };

  outputs = { self, nixpkgs, flake-utils, phps }:
    flake-utils.lib.eachDefaultSystem (system:
      let
        pkgs = import nixpkgs {
          inherit system;
          overlays = [ phps.overlays.default ];
        };

        # Import modular components
        lib = import ./nix/lib.nix { inherit pkgs; };
        packages = import ./nix/packages { inherit pkgs lib self; };
        devshell = import ./nix/devshell.nix { inherit pkgs lib; };
        checks = import ./nix/checks.nix { inherit pkgs lib packages; };
        apps = import ./nix/apps.nix { inherit pkgs lib packages; };
        docker = import ./nix/docker.nix { inherit pkgs lib packages; };
        database = import ./nix/database.nix { inherit pkgs lib; };
      in
      {
        # Packages
        packages = packages // {
          docker-image = docker.image;
          default = packages.engelsystem;
        };

        # Development shell
        devShells.default = devshell;

        # Checks (tests, linters)
        checks = checks;

        # Runnable applications
        apps = apps // {
          # Database management (Docker)
          db-start = database.apps.start;
          db-stop = database.apps.stop;
          db-migrate = database.apps.migrate;
          db-shell = database.apps.shell;
          # Database management (Minikube/Kubernetes)
          db-start-minikube = database.apps.start-minikube;
          db-stop-minikube = database.apps.stop-minikube;
          db-shell-minikube = database.apps.shell-minikube;
        };

        # For CI/CD
        formatter = pkgs.nixpkgs-fmt;
      }
    ) // {
      # NixOS module for deployment
      nixosModules.default = import ./nix/nixos-module.nix;
    };
}
