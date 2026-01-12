{ pkgs }:

let
  # PHP version and extensions required by Engelsystem
  phpVersion = "php83";

  php = (pkgs.${phpVersion}.withExtensions ({ enabled, all }: enabled ++ (with all; [
    dom
    mbstring
    pdo
    pdo_mysql
    tokenizer
    # xml is already in enabled by default
    simplexml
    xmlwriter
    intl
    opcache
    curl
    fileinfo
    filter
    iconv
    openssl
    session
    zip
    # Development extensions
    pcov
    apcu  # Required for RateLimitMiddleware tests
  ])));

  # PHP for production (without dev extensions)
  phpProd = (pkgs.${phpVersion}.withExtensions ({ enabled, all }: enabled ++ (with all; [
    dom
    mbstring
    pdo
    pdo_mysql
    tokenizer
    # xml is already in enabled by default
    simplexml
    xmlwriter
    intl
    opcache
    curl
    fileinfo
    filter
    iconv
    openssl
    session
    zip
  ])));

  # Node.js version
  nodejs = pkgs.nodejs_20;

  # Common source filter to exclude unnecessary files
  sourceFilter = path: type:
    let
      baseName = baseNameOf (toString path);
      # Exclude patterns
      excludes = [
        ".git"
        ".github"
        ".gitlab-ci.yml"
        ".editorconfig"
        ".eslintrc.json"
        ".eslintignore"
        ".prettierrc.json"
        ".phpcs.xml"
        "phpstan.neon.dist"
        "phpunit.xml"
        ".dockerignore"
        "docker"
        "tests"
        "node_modules"
        ".serena"
        ".claude"
        "CONTRIBUTING.md"
        "DEVELOPMENT.md"
        "SECURITY.md"
        "flake.nix"
        "flake.lock"
        "nix"
      ];
    in
    !(builtins.elem baseName excludes);

  # Source for production (minimal)
  prodSource = pkgs.lib.cleanSourceWith {
    src = ../.;
    filter = sourceFilter;
  };

  # Full source for development
  fullSource = pkgs.lib.cleanSource ../.;

  # Version - extracted from composer.json or use "dev"
  # When used from flake.nix, this can be overridden with self.rev or self.dirtyRev
  version =
    let
      composerJson = builtins.fromJSON (builtins.readFile ../composer.json);
      rawVersion = composerJson.version or "dev";
    in
    builtins.replaceStrings [ "\n" " " ] [ "" "" ] rawVersion;

  # Database configuration defaults
  dbConfig = {
    host = "127.0.0.1";
    port = 3306;
    database = "engelsystem";
    username = "engelsystem";
    password = "engelsystem";
  };

  # Trusted proxies for production
  trustedProxies = [
    "10.0.0.0/8"
    "127.0.0.0/8"
    "172.16.0.0/12"
    "192.168.0.0/16"
  ];

in
{
  inherit php phpProd nodejs version dbConfig trustedProxies;
  inherit prodSource fullSource sourceFilter;
  inherit phpVersion;

  # Helper to create a PHP script runner
  mkPhpScript = { name, script, phpEnv ? php }: pkgs.writeShellScriptBin name ''
    exec ${phpEnv}/bin/php ${script} "$@"
  '';

  # Helper to create config.php content
  mkConfig = { appName ? "Engelsystem", appUrl ? "http://localhost:5080", db ? dbConfig, environment ? "production" }: ''
    <?php
    return [
        'app_name' => '${appName}',
        'environment' => '${environment}',
        'database' => [
            'host' => '${db.host}',
            'port' => ${toString db.port},
            'database' => '${db.database}',
            'username' => '${db.username}',
            'password' => '${db.password}',
        ],
        'api' => [
            'enabled' => true,
        ],
    ];
  '';
}
