# Installation of Engelsystem

## Requirements:
 * PHP 5.4.x (cgi-fcgi)
 * MySQL-Server 5.5.x
 * Webserver, i.e. lighttpd, nginx, or Apache

## Directions:
 * Clone the master branch with the submodules: `git clone --recursive https://github.com/engelsystem/engelsystem.git`
 * Webserver must have write access to the 'import' directory and read access for all other directories
 * Webserver must be public.

 * Recommended: Directory Listing should be disabled.
 * There must a be MySQL database created with a user who has full rights to that database.
 * It must be created by the db/install.sql and db/update.sql files.
 * If necessary, create a config/config.php to override values from config/config.default.php.
 * In the browser, login with credentials admin:asdfasdf and change the password.

Engelsystem can now be used.

## Session Settings:
 * Make sure the config allows for sessions.
 * Both Apache and Nginx allow for different VirtualHost configurations.

Report Bugs: https://github.com/engelsystem/engelsystem/issues
