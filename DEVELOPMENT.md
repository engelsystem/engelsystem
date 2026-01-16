# Development

Please also read the [CONTRIBUTING.md](CONTRIBUTING.md).

## Dev requirements
 * Node >= 14 (Development/Building only)
   * Including npm
 * Yarn (Development/Building only)
 * PHP Composer (Development/Building only)

## Code style
Please ensure that your pull requests follow the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style guide.
You can check that by running
```bash
composer run phpcs
# with docker
docker exec engelsystem_dev-es_workspace-1 composer run phpcs
```
You may auto fix reported issues by running
```bash
composer run phpcbf
# with docker
docker exec engelsystem_dev-es_workspace-1 composer run phpcbf
```

## Pre-commit hooks
You should set up the pre-commit hook to check the code style and run tests on commit:

Docker (recommended):

```sh
echo "docker exec engelsystem_dev-es_workspace-1 bin/pre-commit" > .git/hooks/pre-commit
chmod u+x .git/hooks/pre-commit
```

Host machine:

```sh
ln -s ../../bin/pre-commit .git/hooks/pre-commit
```

## Docker

> [!TIP]
> We suggest using Docker for the Development local build.  
> This repo [ships a docker setup](docker/dev) for a quick development start.  
> If you use another uid/gid than 1000 on your machine you have to adjust it in [docker/dev/.env](docker/dev/.env).


Make sure you're in the `docker/dev` subfolder: 

```bash
cd docker/dev
```

Then, run
```bash
docker compose up
```

Run these commands once initially and then as required after changes

```bash
# Install composer dependencies
docker compose exec es_workspace composer i

# Install node packages
docker compose exec es_workspace yarn install

# Run a full front-end build
docker compose exec es_workspace yarn build

# Or run a front-end build for specific themes only, e.g.
docker compose exec -e THEMES=0,1 es_workspace yarn build

# Update the translation files
docker compose exec es_workspace find /var/www/resources/lang -type f -name '*.po' -exec sh -c 'msgfmt "${1%.*}.po" -o"${1%.*}.mo"' shell {} \;

# Run the migrations
docker compose exec es_workspace bin/migrate
```

While developing you may use the watch mode to rebuild the system on changes

```bash
# Run a front-end build and update every time a JS or CSS file is changed (not translation files!)
docker compose exec es_workspace yarn build:watch

# Or build and update on change for specific themes only to save build time, e.g.
docker compose exec -e THEMES=0,1 es_workspace yarn build:watch
```

> [!NOTE]
> Wait some time (up to a few minutes) after running `yarn build:watch` – it may look like it's stalling, but it's not.


It might also be useful to have an interactive database interface for which a phpMyAdmin instance can be startet at [http://localhost:8888](http://localhost:8888).
```bash
docker compose --profile dev up
```

## Localhost
You can find your local Engelsystem on [http://localhost:5080](http://localhost:5080).

## Local build without Docker
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
  find resources/lang/ -type f -name '*.po' -exec sh -c 'msgfmt "${1%.*}.po" -o"${1%.*}.mo"' shell {} \;
  ```

## Testing
To run only unit tests (tests that should not change the Engelsystem state) use
```bash
vendor/bin/phpunit --testsuite Unit
```

If a database is configured and the Engelsystem is allowed to mess around with some files, you can run feature tests.
The tests can potentially delete some database entries, so they should never be run on a production system!
```bash
vendor/bin/phpunit --testsuite Feature
```

When you want to run unit and feature tests at once:
```bash
vendor/bin/phpunit
```

To generate code coverage reports it's highly recommended to use [`pcov`](https://github.com/krakjoe/pcov) or
at least `phpdbg -qrr`(which has problems with switch case statements) as using Xdebug slows down execution.
```bash
php -d pcov.enabled=1 -d pcov.directory=. vendor/bin/phpunit --coverage-text
```

For better debug output, adding `-vvv` might be helpful.
Adding `--coverage-html public/coverage/` exports the coverage reports to the `public/` dir which then can be viewed at [localhost:5080/coverage/index.html](http://localhost:5080/coverage/index.html).

### Docker
If using the Docker-based development environment  you can run the following script to retrieve a coverage report.
```sh
docker compose exec es_workspace composer phpunit:coverage
```

A browsable HTML version is available at http://localhost:5080/coverage/index.html .

## E2E Testing

End-to-end tests use [Playwright](https://playwright.dev/) to test the application in real browsers.

### Requirements

* Node.js >= 18
* npm
* A running MySQL/MariaDB database
* PHP development server or equivalent

### Installation

```bash
cd e2e
npm install
npx playwright install --with-deps
```

This installs the Playwright test framework and downloads browser binaries (Chromium, Firefox, WebKit).

### Running E2E Tests

1. Ensure the database is running and configured
2. Run migrations: `php bin/migrate`
3. Seed test data: `php bin/seed-test-data`
4. Start the PHP server: `php -S 0.0.0.0:5080 -t public/`
5. Run the tests:

```bash
cd e2e

# Run all tests on all browsers
npx playwright test

# Run on a specific browser
npx playwright test --project=chromium-desktop
npx playwright test --project=firefox-desktop
npx playwright test --project=webkit-desktop

# Run specific test file
npx playwright test tests/smoke/
npx playwright test tests/minor-volunteer/

# Run in headed mode (see the browser)
npx playwright test --headed

# Run in debug mode
npx playwright test --debug

# Run with UI mode
npx playwright test --ui
```

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_URL` | `http://localhost:5080` | Base URL for tests |
| `MYSQL_HOST` | `127.0.0.1` | Database host |
| `MYSQL_PORT` | `3306` | Database port |
| `MYSQL_DATABASE` | `engelsystem` | Database name |
| `MYSQL_USER` | `engelsystem` | Database user |
| `MYSQL_PASSWORD` | `engelsystem` | Database password |

### Test Data

The test data seeder (`bin/seed-test-data`) creates:
- 11 test users with various roles (guardians, minors, supervisors)
- Angel types with different work categories
- Locations
- Shifts spanning multiple days
- Guardian relationships

All test data uses the `test_` prefix. See `doc/test-data.md` for details.

### Var Dump server
Symfony Var Dump server is configured to allow for easier debugging. It is not meant as a replacement for xdebug but can actually be used together with xdebug.
The Var Dump Server is especially useful if you want to debug a request without messing up the output e.g. of API calls or the HTML layout.

To use simply call the method `dump` and pass the arguments in exactly the same way you would when using `var_dump`.

This will send the output to the Var Dump server which can be viewed in the terminal.
This does however require that you start the var-dump-server otherwise the output will be printed in your browser

You can also `dump` and `die` if you wish to not let your code continue any further by calling the `dd` method

To view the output of `dump` call the following commands:

```bash
vendor/bin/var-dump-server
# or for running in docker
docker compose exec es_server vendor/bin/var-dump-server
```

For more information check out the Var Dump Server documentation: [Symfony VarDumper](https://symfony.com/components/VarDumper)

## Translation
We use gettext. You may use POEdit to extract new texts from the sourcecode.
Please config POEdit to extract also the twig template files using the following settings: https://gist.github.com/jlambe/a868d9b63d70902a12254ce47069d0e6


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

## Static code analysis

You can run a static code analysis with this command:

```bash
composer phpstan
```

**Hint for using Xdebug with *PhpStorm***

For some reason *PhpStorm* is unable to detect the server name.
But without a server name it's impossible to set up path mappings.
Because of that the docker setup sets the server name *engelsystem*.
To get Xdebug working you have to create a server with the name *engelsystem* manually.

## Troubleshooting

### Docker version
If unspecific issues appear try using Docker version >= 20.10.14.

### `service "es_workspace" is not running`
Make sure you're running your docker commands from the `docker/dev` directory, not from `docker`

### `main` is broken after pulling the latest commits from upstream
Try running
```bash
composer install
```
from this repository's root directory.
If dependencies have been updated in `composer.json` since you last synced `main`, this should fix it.
