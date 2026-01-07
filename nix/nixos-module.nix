{ config, lib, pkgs, ... }:

with lib;

let
  cfg = config.services.engelsystem;

  # Import our lib
  esLib = import ./lib.nix { inherit pkgs; };

  # Generate config.php content
  configPhp = pkgs.writeText "config.php" ''
    <?php
    return [
        'app_name' => '${cfg.appName}',
        'environment' => '${cfg.environment}',
        'database' => [
            'host' => '${cfg.database.host}',
            'port' => ${toString cfg.database.port},
            'database' => '${cfg.database.name}',
            'username' => '${cfg.database.user}',
            'password' => file_get_contents('${cfg.database.passwordFile}'),
        ],
        'api' => [
            'enabled' => ${boolToString cfg.api.enabled},
        ],
        ${optionalString (cfg.extraConfig != "") cfg.extraConfig}
    ];
  '';

in
{
  options.services.engelsystem = {
    enable = mkEnableOption "Engelsystem shift planning system";

    package = mkOption {
      type = types.package;
      default = pkgs.engelsystem or (import ./packages { inherit pkgs; lib = esLib; self = {}; }).engelsystem;
      description = "Engelsystem package to use";
    };

    appName = mkOption {
      type = types.str;
      default = "Engelsystem";
      description = "Application name displayed in the UI";
    };

    environment = mkOption {
      type = types.enum [ "production" "development" ];
      default = "production";
      description = "Application environment";
    };

    domain = mkOption {
      type = types.str;
      example = "engelsystem.example.com";
      description = "Domain name for the Engelsystem instance";
    };

    port = mkOption {
      type = types.port;
      default = 8080;
      description = "Port for the PHP-FPM socket (internal)";
    };

    user = mkOption {
      type = types.str;
      default = "engelsystem";
      description = "User under which Engelsystem runs";
    };

    group = mkOption {
      type = types.str;
      default = "engelsystem";
      description = "Group under which Engelsystem runs";
    };

    stateDir = mkOption {
      type = types.path;
      default = "/var/lib/engelsystem";
      description = "Directory for Engelsystem state (storage)";
    };

    database = {
      host = mkOption {
        type = types.str;
        default = "localhost";
        description = "Database host";
      };

      port = mkOption {
        type = types.port;
        default = 3306;
        description = "Database port";
      };

      name = mkOption {
        type = types.str;
        default = "engelsystem";
        description = "Database name";
      };

      user = mkOption {
        type = types.str;
        default = "engelsystem";
        description = "Database user";
      };

      passwordFile = mkOption {
        type = types.path;
        description = "Path to file containing database password";
        example = "/run/secrets/engelsystem-db-password";
      };

      createLocally = mkOption {
        type = types.bool;
        default = false;
        description = "Whether to create the database locally";
      };
    };

    api = {
      enabled = mkOption {
        type = types.bool;
        default = true;
        description = "Whether to enable the API";
      };
    };

    nginx = {
      enable = mkOption {
        type = types.bool;
        default = true;
        description = "Whether to configure nginx";
      };

      enableACME = mkOption {
        type = types.bool;
        default = false;
        description = "Whether to enable ACME/Let's Encrypt";
      };
    };

    extraConfig = mkOption {
      type = types.lines;
      default = "";
      description = "Extra PHP configuration to add to config.php";
      example = literalExpression ''
        'theme' => 1,
        'footer_items' => [
            'Contact' => 'mailto:support@example.com',
        ],
      '';
    };
  };

  config = mkIf cfg.enable {
    # Create user and group
    users.users.${cfg.user} = {
      isSystemUser = true;
      group = cfg.group;
      home = cfg.stateDir;
      createHome = true;
    };

    users.groups.${cfg.group} = { };

    # Set up the application directory
    systemd.tmpfiles.rules = [
      "d ${cfg.stateDir} 0750 ${cfg.user} ${cfg.group} -"
      "d ${cfg.stateDir}/storage 0750 ${cfg.user} ${cfg.group} -"
      "d ${cfg.stateDir}/storage/app 0750 ${cfg.user} ${cfg.group} -"
      "d ${cfg.stateDir}/storage/cache 0750 ${cfg.user} ${cfg.group} -"
      "d ${cfg.stateDir}/storage/logs 0750 ${cfg.user} ${cfg.group} -"
      "L+ ${cfg.stateDir}/config.php - - - - ${configPhp}"
    ];

    # PHP-FPM pool
    services.phpfpm.pools.engelsystem = {
      user = cfg.user;
      group = cfg.group;

      settings = {
        "listen.owner" = config.services.nginx.user;
        "listen.group" = config.services.nginx.group;
        "pm" = "dynamic";
        "pm.max_children" = 32;
        "pm.start_servers" = 2;
        "pm.min_spare_servers" = 2;
        "pm.max_spare_servers" = 8;
        "pm.max_requests" = 500;

        # Security
        "php_admin_value[expose_php]" = "Off";
        "php_admin_value[display_errors]" = "Off";
        "php_admin_value[log_errors]" = "On";
        "php_admin_value[error_log]" = "${cfg.stateDir}/storage/logs/php-error.log";

        # Performance
        "php_admin_value[opcache.enable]" = "1";
        "php_admin_value[opcache.memory_consumption]" = "256";
        "php_admin_value[opcache.max_accelerated_files]" = "20000";

        # Limits
        "php_admin_value[memory_limit]" = "256M";
        "php_admin_value[max_execution_time]" = "60";
        "php_admin_value[upload_max_filesize]" = "64M";
        "php_admin_value[post_max_size]" = "64M";
      };

      phpPackage = esLib.phpProd;
      phpEnv = {
        ENGELSYSTEM_CONFIG = "${cfg.stateDir}/config.php";
      };
    };

    # Nginx configuration
    services.nginx = mkIf cfg.nginx.enable {
      enable = true;

      virtualHosts.${cfg.domain} = {
        root = "${cfg.package}/share/engelsystem/public";

        forceSSL = cfg.nginx.enableACME;
        enableACME = cfg.nginx.enableACME;

        locations = {
          "/" = {
            tryFiles = "$uri $uri/ /index.php$is_args$args";
          };

          "~ \\.php$" = {
            extraConfig = ''
              fastcgi_pass unix:${config.services.phpfpm.pools.engelsystem.socket};
              fastcgi_index index.php;
              fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
              include ${pkgs.nginx}/conf/fastcgi_params;
              fastcgi_read_timeout 600s;
            '';
          };

          "~ /\\." = {
            extraConfig = "deny all;";
          };
        };

        extraConfig = ''
          index index.php;

          # Security headers
          add_header X-Content-Type-Options nosniff;
          add_header X-Frame-Options SAMEORIGIN;
          add_header Referrer-Policy strict-origin-when-cross-origin;
        '';
      };
    };

    # Local MySQL database
    services.mysql = mkIf cfg.database.createLocally {
      enable = true;
      package = pkgs.mariadb;

      ensureDatabases = [ cfg.database.name ];
      ensureUsers = [
        {
          name = cfg.database.user;
          ensurePermissions = {
            "${cfg.database.name}.*" = "ALL PRIVILEGES";
          };
        }
      ];
    };

    # Migration service (run once)
    systemd.services.engelsystem-migrate = {
      description = "Engelsystem Database Migration";
      after = [ "mysql.service" "network.target" ];
      wants = [ "mysql.service" ];

      serviceConfig = {
        Type = "oneshot";
        User = cfg.user;
        Group = cfg.group;
        WorkingDirectory = "${cfg.package}/share/engelsystem";
        ExecStart = "${esLib.phpProd}/bin/php bin/migrate";
        Environment = [
          "ENGELSYSTEM_CONFIG=${cfg.stateDir}/config.php"
        ];
      };
    };
  };
}
