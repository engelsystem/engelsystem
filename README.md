[![Codacy Badge](https://api.codacy.com/project/badge/Grade/20b3b0b4e93344a29da6bec77f329e7a)](https://www.codacy.com/app/engelsystem/engelsystem)
[![GPL](https://img.shields.io/github/license/engelsystem/engelsystem.svg?maxAge=2592000)]()

# Engelsystem

Please visit https://engelsystem.de for a feature list.

## Installation

### Requirements:
 * PHP >= 5.6.4, PHP >= 7.0.0 recommended
 * MySQL-Server >= 5.5.x
 * Webserver, i.e. lighttpd, nginx, or Apache

### Directions:
 * Clone the master branch: `git clone https://github.com/engelsystem/engelsystem.git`
 * Install [Composer](https://getcomposer.org/download/)
 * Install project dependencies: `composer install`
 * Webserver must have write access to the 'import' directory and read access for all other directories
 * Webserver must be public.

 * Recommended: Directory Listing should be disabled.
 * There must a be MySQL database created with a user who has full rights to that database.
 * It must be created by the db/install.sql and db/update.sql files.
 * If necessary, create a config/config.php to override values from config/config.default.php.
 * In the browser, login with credentials admin:asdfasdf and change the password.

Engelsystem can now be used.

### Session Settings:
 * Make sure the config allows for sessions.
 * Both Apache and Nginx allow for different VirtualHost configurations.

Report Bugs: https://github.com/engelsystem/engelsystem/issues

## Development
Since the engelsystem is open source, you can help to improve the system. We really love to get pull requests containing fixes or implementations of our Github issues.

Please create single pull requests for every feature instead of creating one big monster of pull request containing a complete rewrite.

### Codestyle
Please ensure that your pull requests follow [PSR-2](http://www.php-fig.org/psr/psr-2/) and [PSR-4](http://www.php-fig.org/psr/psr-4/).
