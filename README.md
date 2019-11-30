[![pipeline status](https://chaos.expert/engelsystem/engelsystem/badges/master/pipeline.svg)](https://chaos.expert/engelsystem/engelsystem/commits/master)
[![coverage report](https://chaos.expert/engelsystem/engelsystem/badges/master/coverage.svg)](https://chaos.expert/engelsystem/engelsystem/commits/master)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/20b3b0b4e93344a29da6bec77f329e7a)](https://www.codacy.com/app/engelsystem/engelsystem)
[![GPL](https://img.shields.io/github/license/engelsystem/engelsystem.svg?maxAge=2592000)]()

# Engelsystem
Please visit https://engelsystem.de for a feature list.

To report bugs use [engelsystem/issues](https://github.com/engelsystem/engelsystem/issues)

## Installation

### Requirements
 * PHP >= 7.2
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

### Additional requirements if you want to build the project by yourself
 * Node >= 8 (Development/Building only)
 * Yarn (Development/Building only)
 * PHP Composer (Development/Building only)

#### This should be included in your node install
 * npm (Development/Building only)

### Download

#### Stable
 * Go to the [Releases](https://github.com/engelsystem/engelsystem/releases) page and download the latest stable release file.
 * Extract the files to your webroot and continue with the directions for configurations and setup.

#### Latest unstable
The following instructions explain how to get, build and run the latest engelsystem version directly from the git master branch (may be unstable!).

 * Clone the master branch: `git clone https://github.com/engelsystem/engelsystem.git`
 * Install [Composer](https://getcomposer.org/download/) and [Yarn](https://yarnpkg.com/en/docs/install) (which requires [Node.js](https://nodejs.org/en/download/package-manager/))
 * Install project dependencies:
     ```bash
     composer install
     yarn
     ```
    On production systems it is recommended to use
    ```bash
    composer install --no-dev
    composer dump-autoload --optimize
    ```
    to install the engelsystem
 * Build the frontend assets
    ```bash
    yarn build
    ```
 * Optionally (for better performance)
   * Generate translation files
      ```bash
      find resources/lang/ -type f -name '*.po' -exec sh -c 'file="{}"; msgfmt "${file%.*}.po" -o "${file%.*}.mo"' \;
      ```

### Configuration and Setup
 * The webserver must have write access to the ```import``` and ```storage``` directories and read access for all other directories
 * The webserver must point to the ```public``` directory.
 * The webserver must read the ```.htaccess``` file and ```mod_rewrite``` must be enabled

 * Recommended: Directory Listing should be disabled.
 * There must a be MySQL database created with a user who has full rights to that database.
 * If necessary, create a ```config/config.php``` to override values from ```config/config.default.php```.
   * To remove values from the `footer_items`, `available_themes`, `locales`, `tshirt_sizes` or `headers` lists the config file has to be renamed.
 * To import the database the ```bin/migrate``` script has to be called. If you are not allowed to execute scripts, then execute the ```install-<version>.sql``` script. Download at [Releases](https://github.com/engelsystem/engelsystem/releases) page.
 * In the browser, login with credentials ```admin``` : ```asdfasdf``` and change the password.

Engelsystem can now be used.

### Session Settings:
 * Make sure the config allows for sessions.
 * Both Apache and Nginx allow for different VirtualHost configurations.

## Development
Since the engelsystem is open source, you can help to improve the system. We really love to get pull requests containing fixes or implementations of our Github issues.

Please create single pull requests for every feature instead of creating one big monster of pull request containing a complete rewrite.

### Testing
To run the unit tests use
```bash
vendor/bin/phpunit --testsuite Unit
``` 

If a database is configured and the engelsystem is allowed to mess around with some files, you can run feature tests.
The tests can potentially delete some database entries, so they should never be run on a production system!
```bash
vendor/bin/phpunit --testsuite Feature
# or for unit- and feature tests:
vendor/bin/phpunit
``` 

To run code coverage reports its highly recommended to use [`pcov`](https://github.com/krakjoe/pcov) or at least `phpdbg -qrr`(which has problems with switch case statements) as using Xdebug slows down execution.
```bash
php -d pcov.enabled=1 vendor/bin/phpunit --testsuite Unit --coverage-text
```

### CI & Build Pipeline
The engelsystem can be tested and automatically deployed to a testing/staging/production environment.
This functionality requires a [GitLab](https://about.gitlab.com/) server with a working docker runner.

To use the deployment features the following secret variables need to be defined (if undefined the step will be skipped):
```bash
SSH_PRIVATE_KEY         # The ssh private key
STAGING_REMOTE          # The staging server, e.g. user@remote.host
STAGING_REMOTE_PATH     # The path on the remote server, e.g. /var/www/engelsystem
PRODUCTION_REMOTE       # Same as STAGING_REMOTE but for the production environment
PRODUCTION_REMOTE_PATH  # Same as STAGING_REMOTE_PATH but for the production environment
```

### Docker

#### Production

To build the `es_nginx` and the `es_php_fpm` containers:
```bash
cd docker
docker-compose build
```

or to build the containers separately
```bash
docker build -f docker/nginx/Dockerfile . -t es_nginx
docker build -f docker/Dockerfile . -t es_php_fpm
```

Import database
```bash
docker exec -it engelsystem_es_php_fpm_1 bin/migrate
```

#### Development

This repo [ships a docker setup](docker/dev) for a quick development start.

If you use another uid/gid than 1000 on your machine you have to adjust it in [docker/dev/.env](docker/dev/.env).

Run this once

```bash
cd docker/dev
docker-compose up
```

Run these commands once initially and then as required after changes

```bash
# Install composer dependencies
docker exec -it engelsystem_dev_es_workspace_1 composer i

# Install node packages
docker exec -it engelsystem_dev_es_workspace_1 yarn install

# Run a front-end build
docker exec -it engelsystem_dev_es_workspace_1 yarn build

# Update the translation files
docker exec -it engelsystem_dev_es_workspace_1 find /var/www/resources/lang -type f -name '*.po' -exec sh -c 'file="{}"; msgfmt "${file%.*}.po" -o "${file%.*}.mo"' \;

# Run the migrations
docker exec -it engelsystem_dev_es_workspace_1 bin/migrate
```

While developing you may use the watch mode to rebuild the system on changes

```bash
# Run a front-end build
docker exec -it engelsystem_dev_es_workspace_1 yarn build:watch
```

##### Hint for using Xdebug with *PhpStorm*

For some reason *PhpStorm* is unable to detect the server name.
But without a server name it's impossible to set up path mappings.
Because of that the docker setup sets the server name *engelsystem*.
To get Xdebug working you have to create a server with the name *engelsystem* manually. 

#### Scripts

##### bin/deploy.sh
The `bin/deploy.sh` script can be used to deploy the engelsystem. It uses rsync to deploy the application to a server over ssh.

For usage see `./bin/deploy.sh -h`

##### bin/migrate
The `bin/migrate` script can be used to import and update the database of the engelsystem.

For more information on how to use it call `./bin/migrate help`

### Translation
We use gettext. You may use POEdit to extract new texts from the sourcecode. Please config POEdit to extract also the twig template files using the following settings: https://gist.github.com/jlambe/a868d9b63d70902a12254ce47069d0e6

### Code style
Please ensure that your pull requests follow the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style guide.
You can check that by running
```bash
composer run phpcs
```
You may auto fix reported issues by running
```bash
composer run phpcbf
```
