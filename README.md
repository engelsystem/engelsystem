[![pipeline status](https://chaos.expert/engelsystem/engelsystem/badges/main/pipeline.svg)](https://chaos.expert/engelsystem/engelsystem/commits/main)
[![coverage report](https://chaos.expert/engelsystem/engelsystem/badges/main/coverage.svg)](https://chaos.expert/engelsystem/engelsystem/commits/main)
[![GPL](https://img.shields.io/github/license/engelsystem/engelsystem.svg?maxAge=2592000)](LICENSE)

# Engelsystem
Please visit [engelsystem.de](https://engelsystem.de) for a feature list.

To report bugs use [engelsystem/issues](https://github.com/engelsystem/engelsystem/issues).

Since the Engelsystem is open source, you can help improving it.
We really love to get pull requests containing fixes or improvements.
Please read the [CONTRIBUTING.md](CONTRIBUTING.md) and [DEVELOPMENT.md](DEVELOPMENT.md) before you start.

## Installation
The Engelsystem may be installed manually or by using the provided [docker setup](#docker).

### Requirements
 * PHP >= 7.4
   * Required modules:
     * dom
     * json
     * mbstring
     * PDO
       * mysql
     * tokenizer
     * xml/libxml/SimpleXML
     * xmlwriter
 * MySQL-Server >= 5.7.8 or MariaDB-Server >= 10.2.2
 * Webserver, i.e. lighttpd, nginx, or Apache

### Download
 * Go to the [Releases](https://github.com/engelsystem/engelsystem/releases) page and download the latest stable release file.
 * Extract the files to your webroot and continue with the directions for configurations and setup.

### Configuration and Setup
 * The webserver must have write access to the ```storage``` directory and read access for all other directories
 * The webserver must point to the ```public``` directory.
 * The webserver must read the ```.htaccess``` file and ```mod_rewrite``` must be enabled

 * Recommended: Directory Listing should be disabled.
 * There must be a MySQL database set up with a user who has full rights to that database.
 * If necessary, create a ```config/config.php``` to override values from ```config/config.default.php```.
   * To edit values from the `footer_items`, `themes`, `locales`, `tshirt_sizes` or `headers` lists, directly modify the ```config/config.default.php``` file or rename it to ```config/config.php```.
 * To import the database, the ```bin/migrate``` script has to be run. If you can't execute scripts, you can use the `initial-install.sql` file from the release zip.
 * In the browser, login with credentials ```admin``` : ```asdfasdf``` and change the password.

The Engelsystem can now be used.

### Session Settings
 * Make sure the config allows for sessions.
 * Both Apache and Nginx allow for different VirtualHost configurations.

### Docker
#### Build
To build the `es_server` container:
```bash
cd docker
docker-compose build
```

or to build the container by its own:
```bash
docker build -f docker/Dockerfile . -t es_server
```

#### Run
Start the Engelsystem
```bash
cd docker
docker-compose up -d
```

#### Migrate
Import database changes to migrate it to the newest version
```bash
cd docker
docker-compose exec es_server bin/migrate
```

### Scripts
#### bin/deploy.sh
The `bin/deploy.sh` script can be used to deploy the Engelsystem. It uses rsync to deploy the application to a server over ssh.

For usage see `./bin/deploy.sh -h`

#### bin/migrate
The `bin/migrate` script can be used to import and update the database of the Engelsystem.

For more information on how to use it call `./bin/migrate help`

### Documentation

More documentation can be found at: https://engelsystem.de/doc/
