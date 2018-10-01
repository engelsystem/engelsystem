[![Codacy Badge](https://api.codacy.com/project/badge/Grade/20b3b0b4e93344a29da6bec77f329e7a)](https://www.codacy.com/app/engelsystem/engelsystem)
[![GPL](https://img.shields.io/github/license/engelsystem/engelsystem.svg?maxAge=2592000)]()

# Engelsystem
Please visit https://engelsystem.de for a feature list.

To report bugs use [engelsystem/issues](https://github.com/engelsystem/engelsystem/issues)

## Installation
### Requirements:
 * PHP >= 7.1
 * MySQL-Server >= 5.7.8 or MariaDB-Server >= 10.2.2
 * Webserver, i.e. lighttpd, nginx, or Apache
 * Node >= 8 (Development/Building only)
 * Yarn (Development/Building only)

### Directions:
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

 * The webserver must have write access to the ```import``` directory and read access for all other directories
 * The webserver must point to the ```public``` directory.
 * The webserver must read the ```.htaccess``` file and ```mod_rewrite``` must be enabled

 * Recommended: Directory Listing should be disabled.
 * There must a be MySQL database created with a user who has full rights to that database.
 * If necessary, create a ```config/config.php``` to override values from ```config/config.default.php```.
 * To import the database the ```bin/migrate``` script has to be called.
 * In the browser, login with credentials ```admin```:```asdfasdf``` and change the password.

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

### CI & Build Pipeline
The engelsystem can be tested and automatically deployed to a testing/staging/production environment.
This functionality requires a [GitLab](https://about.gitlab.com/) server with a running docker minion.

To use the deployment features the following secret variables need to be defined (if undefined the step will be skipped):
```bash
SSH_PRIVATE_KEY         # The ssh private key
STAGING_REMOTE          # The staging server, e.g. user@remote.host
STAGING_REMOTE_PATH     # The path on the remote server, e.g. /var/www/engelsystem
PRODUCTION_REMOTE       # Same as STAGING_REMOTE but for the production environment
PRODUCTION_REMOTE_PATH  # Same as STAGING_REMOTE_PATH but for the production environment
```

### Docker container
To build the `engelsystem` and the `engelsystem-nginx` container:
```bash
cd contrib
docker-compose build
```

or to build the containers separately
```bash
docker build -f contrib/nginx/Dockerfile . -t engelsystem-nginx
docker build -f contrib/Dockerfile . -t engelsystem
```

Import database
```bash
docker exec -it engelsystem bin/migrate
```

#### Scripts
##### bin/deploy.sh
The `bin/deploy.sh` script can be used to deploy the engelsystem. It uses rsync to deploy the application to a server over ssh.

For usage see `./bin/deploy.sh -h`

##### bin/migrate
The `bin/migrate` script can be used to import and update the database of the engelsystem.

For more information on how to use it call `./bin/migrate help`

### Codestyle
Please ensure that your pull requests follow [PSR-2](http://www.php-fig.org/psr/psr-2/) and [PSR-4](http://www.php-fig.org/psr/psr-4/).
