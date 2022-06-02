# Development

Please also read the [CONTRIBUTING.md](CONTRIBUTING.md).

## Dev requirements
 * Node >= 14 (Development/Building only)
   * Including npm
 * Yarn (Development/Building only)
 * PHP Composer (Development/Building only)

## Local build
The following instructions explain how to get, build and run the latest Engelsystem version directly from the git main branch (may be unstable!).

* Clone the main branch: `git clone https://github.com/engelsystem/engelsystem.git`
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
  to install the Engelsystem
* Build the frontend assets
  * All
    ```bash
    yarn build
    ```
  * Specific themes only by providing the `THEMES` environment variable, e.g.
    ```bash
    THEMES=0,1 yarn build
    ```
* Generate translation files
  ```bash
  find resources/lang/ -type f -name '*.po' -exec sh -c 'file="{}"; msgfmt "${file%.*}.po" -o "${file%.*}.mo"' \;
  ```

## Testing
To run the unit tests use
```bash
vendor/bin/phpunit --testsuite Unit
``` 

If a database is configured and the Engelsystem is allowed to mess around with some files, you can run feature tests.
The tests can potentially delete some database entries, so they should never be run on a production system!
```bash
vendor/bin/phpunit --testsuite Feature
# or for unit- and feature tests:
vendor/bin/phpunit
``` 

To run code coverage reports its highly recommended to use [`pcov`](https://github.com/krakjoe/pcov) or
at least `phpdbg -qrr`(which has problems with switch case statements) as using Xdebug slows down execution.
```bash
php -d pcov.enabled=1 -d pcov.directory=. vendor/bin/phpunit --testsuite Unit --coverage-text
```

### Var Dump server
Symfony Var Dump server is configured to allow for easier debugging. It is not meant as a replacement for xdebug but can actually be used together with xdebug.
This Var Dump Server is especially useful for when you want to debug a request without messing up the output e.g API calls ot HTML layout.

To use simply call the method `dump` and pass the arguments in exactly the same way you would when using `var_dump`.

This will send the output to the Var Dump server which can be viewed in the terminal. 
This does however require that you start the var-dump-server otherwise the output will be printed in your browser

You can also `dump` and `die` if you wish to not let your code continue any further by calling the `dd` method

To view the output of `dump` call the following commands:

```bash
vendor/bin/var-dump-server
# or for running in docker
docker-compose exec es_server vendor/bin/var-dump-server
```

For more information check out the Var Dump Server documentation: [Symfony VarDumper](https://symfony.com/components/VarDumper)

## Translation
We use gettext. You may use POEdit to extract new texts from the sourcecode.
Please config POEdit to extract also the twig template files using the following settings: https://gist.github.com/jlambe/a868d9b63d70902a12254ce47069d0e6

## Code style
Please ensure that your pull requests follow the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style guide.
You can check that by running
```bash
composer run phpcs
```
You may auto fix reported issues by running
```bash
composer run phpcbf
```

## CI & Build Pipeline

The Engelsystem can be tested and automatically deployed to a testing/staging/production environment.
This functionality requires a [GitLab](https://about.gitlab.com/) server with a working docker runner.

To use the deployment features the following secret variables need to be defined (if undefined the step will be skipped):
```bash
SSH_PRIVATE_KEY         # The ssh private key
STAGING_REMOTE          # The staging server, e.g. user@remote.host
STAGING_REMOTE_PATH     # The path on the remote server, e.g. /var/www/engelsystem
PRODUCTION_REMOTE       # Same as STAGING_REMOTE but for the production environment
PRODUCTION_REMOTE_PATH  # Same as STAGING_REMOTE_PATH but for the production environment
```

## Docker

If unspecific issues appear try using Docker version >= 20.10.14.

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
docker-compose exec es_workspace composer i

# Install node packages
docker-compose exec es_workspace yarn install

# Run a full front-end build
docker-compose exec es_workspace yarn build

# Or run a front-end build for specific themes only, e.g.
docker-compose exec -e THEMES=0,1 es_workspace yarn build

# Update the translation files
docker-compose exec es_workspace find /var/www/resources/lang -type f -name '*.po' -exec sh -c 'file="{}"; msgfmt "${file%.*}.po" -o "${file%.*}.mo"' \;

# Run the migrations
docker-compose exec es_workspace bin/migrate
```

While developing you may use the watch mode to rebuild the system on changes

```bash
# Run a front-end build and update on change
docker-compose exec es_workspace yarn build:watch

# Or run a front-end build and update on change for specific themes only, e.g.
docker-compose exec -e THEMES=0,1 es_workspace yarn build:watch
```

**Hint for using Xdebug with *PhpStorm***

For some reason *PhpStorm* is unable to detect the server name.
But without a server name it's impossible to set up path mappings.
Because of that the docker setup sets the server name *engelsystem*.
To get Xdebug working you have to create a server with the name *engelsystem* manually.
