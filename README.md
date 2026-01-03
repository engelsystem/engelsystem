[![pipeline status](https://chaos.expert/engelsystem/engelsystem/badges/main/pipeline.svg)](https://chaos.expert/engelsystem/engelsystem/commits/main)
[![coverage report](https://chaos.expert/engelsystem/engelsystem/badges/main/coverage.svg)](https://chaos.expert/engelsystem/engelsystem/commits/main)
[![GPL](https://img.shields.io/github/license/engelsystem/engelsystem.svg?maxAge=2592000)](LICENSE)

# Engelsystem
Please visit [engelsystem.de](https://engelsystem.de) for a feature list.

To report bugs use [engelsystem/issues](https://github.com/engelsystem/engelsystem/issues).

Since the Engelsystem is open source, you can help improving it!
We really love to get pull requests containing fixes or improvements.
Please read the [CONTRIBUTING.md](CONTRIBUTING.md) and [DEVELOPMENT.md](DEVELOPMENT.md) before you start.

## Installation
The Engelsystem may be installed manually by uploading files to a web hoster or by using the provided [docker setup](#docker).

### Requirements
 * PHP >= 8.2
   * Required modules:
     * dom
     * json
     * mbstring
     * PDO
       * mysql
     * tokenizer
     * xml/libxml/SimpleXML
     * xmlwriter
 * MySQL-Server >= 5.7.8 or MariaDB-Server >= 10.7
 * Webserver, i.e. nginx, lighttpd, or Apache

From previous experience, 2 cores and 2GB ram are roughly enough for up to 1000 Helpers (~700 arrived + 500 arrived but not working) during an event.

### Download
 * Go to the [Releases](https://github.com/engelsystem/engelsystem/releases) page and download the latest stable release file.
 * Extract the files to your webroot and continue with the directions for configurations and setup.

### Configuration and Setup
 * The webserver must have write access to the `storage` directory and read access for all other directories
 * The webserver must point to the `public` directory.
 * The webserver must read the `.htaccess` file and `mod_rewrite` must be enabled

 * Recommended: Directory Listing should be disabled.
 * There must be a MySQL database set up with a user who has full rights to that database.
 * If necessary, create a `config/config.php` to override some configuration values.
    Using the web UI to change settings is recommended, you can find a documentation of possible settings in the
    [configuration documentation](https://engelsystem.de/doc/admin/configuration/index.html).
   * A minimal `config.php` to connect to your database will be:
      ```php
      <?php
      return [
        'database' => [
          'host' => 'localhost',
          'database' => 'engelsystem',
          'username' => 'engelsystem',
          'password' => '<your password here>',
        ],
      ];
      ```
   * If you want to change items from a list, all values must be present in your `config.php`, including new ones
 * To import the database, the `bin/migrate` script has to be run. If you can't execute scripts, you can import the `initial-install.sql` file from the release zip.
 * In the browser, login with credentials `admin` : `asdfasdf` and change the password.

The Engelsystem can now be used.

### Session Settings
 * Make sure the config allows for sessions.
 * Both Apache and Nginx allow for different VirtualHost configurations.

### Docker

For instructions on how to build the Docker container for development, please consult the [DEVELOPMENT.md](DEVELOPMENT.md).

#### Build
To build the `es_server` container:
```bash
cd docker
docker compose build
```

or to build the container by its own:
```bash
docker build -f docker/Dockerfile . -t es_server
```

#### Run
Start the Engelsystem
```bash
cd docker
docker compose up -d
```

#### Set Up / Migrate Database
Create the Database Schema (on a fresh install) or import database changes to migrate it to the newest version
```bash
cd docker
docker compose exec es_server bin/migrate
```

### Scripts

#### bin/config2docs
The `bin/config2docs` script is a helper to generate the engelsystem config documentation page.

#### bin/deploy.sh
The `bin/deploy.sh` script can be used to deploy the Engelsystem. It uses rsync to deploy the application to a server over ssh.

For usage see `./bin/deploy.sh -h`

#### bin/migrate
The `bin/migrate` script can be used to import and update the database of the Engelsystem.

For more information on how to use it call `./bin/migrate help`

#### bin/pre-commit
The `bin/pre-commit` script can be used during development to ensure the code quality matches the expected standard.

### Documentation

More documentation can be found at: https://engelsystem.de/doc/
