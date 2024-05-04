# Helferinnensystem

This is our fork of the [engelsystem](https://engelsystem.de), including some
information about how to set it up. It might be somewhat FrOSCon-specific.
See [/README.md](/README.md) for the original readme file with more details.


## Installation

The basic installation steps are:

- install `git`, `docker` and `docker-compose`
- clone `https://github.com/froscon/engelsystem.git`
- change configuration to your needs
- start the containers via `docker-compose`

Check the following configurations and adapt as necessary:

- `docker/secrets/` holds all passwords as separate files. These are mounted
  into the containers via [`docker-compose` secrets](https://docs.docker.com/compose/use-secrets/)
  and consumed accordingly by the containers. Note that the database only uses
  the secrets when it is initially created. Changing the secrets later on will
  not make the database change the passwords!
- `docker/docker-compose.override.yml` holds all normal configuration. Some info
  on what they do is in `config/config.default.php`. Adapt as necessary.
- we use the FrOSCon frog logo as icon. Change if you want something else.

Starting `docker-compose` will first build and then start three containers:

- `es_database` is a standard mariadb container. To inspect it manually, use the
  [`phpmyadmin` container](#minor-stuff).
- `es_server` hosts the engelsystem itself. It takes some time to build...
- `backup` for database backups, [see here](#backups).

Once the containers are running, initialize or update the database using the
`bin/migrate` script within the `es_server` container.

```bash
$ cd docker
$ docker compose up -d
... containers are built, takes some time ...
... containers are started ...
$ docker compose exec es_server bin/migrate
... database is created and initialized ...
```

You should now find a working helferinnensystem on port 80!


## Updates

The general idea of this fork is to maintain a set of commits that can be
rebased easily onto new upstream versions. Hence, any changes to this fork
should be easy to rebase: try to avoid modifying files and instead add new
files (examples: `docker-compose.override.yml`, `.github/README.md`).

To propose a new change, open a PR against this fork and consider the following:

- if the change is rather a general patch for the [engelsystem](https://engelsystem.de),
  head [over there](https://github.com/engelsystem/engelsystem) and open your PR
  against the upstream
- try to make it as easy as possible to rebase in the future; try not to touch
  any files that exist in the upstream.

If our `main` changed, follow the instructions below but skip the `rebase` step.


### New upstream version

To update to a new upstream version, rebase the current `main` branch on the
upstream's `main` branch. If the rebase looks good and the whole setup boots
locally, then (and only then) force-push the result back to `main`. Finally,
go to your server, pull the changes, rebuild the docker container,
restart it and (most likely) run the migrations.

```bash
$ git remote -v
...
upstream	git@github.com:engelsystem/engelsystem.git (fetch)
upstream	git@github.com:engelsystem/engelsystem.git (push)
$ git rebase -i main upstream/main
...
$ git push -f
$ ssh <your server>
$ cd engelsystem/docker/
$ git pull
$ docker compose build
... es_server is rebuilt ...
$ docker compose up -d
... es_server is restarted ...
$ docker compose exec es_server bin/migrate
... new migrations are integrated ...
```


## Preparing for a new year

For FrOSCon, or other recurring events, we avoid recreating all shifts every
year. Instead we purge data that is specific to a particular year and carefully
move the shifts to the next year. To do this:

- update to the [latest upstream version](#new-upstream-version)
- run `new-year-cleanup.py <FrOSCon Saturday>` to purge the database

Be careful! The second step removes all shifts and locations that originate from
a schedule (i.e. from talks), moves all remaining shifts into the given
FrOSCon weekend and updates the event config. Furthermore it removes all
non-admin users as well as all messages, news, questions, schedules, shift
entries and log entries.
As this script is somewhat FrOSCon specific, it lives in our
[internal repository](https://gitlab.froscon.org/conference/helfen). We should
be happy to provide it on request, though.


## Differences compared to upstream

See [this diff](https://github.com/engelsystem/engelsystem/compare/main...froscon:engelsystem:main)
for an exhaustive diff of our `main` branch with the upstream `main` branch.


### Docker compose file

The normal way to configure the engelsystem is via environment variables passed
to the `es_server` docker container. Do make rebasing easy, we add a
`docker-compose.override.yml` file that sets a bunch of environment variables,
passwords and additional containers (`backup` and `phpmyadmin`).

These environment variables are used in `config/config.default.php`. Note that
the `MYSQL_PASSWORD_FILE` still requires a custom patch while we are waiting
this to be [upstreamed](https://github.com/engelsystem/engelsystem/pull/1367).


### Language files

We choose to avoid the whole angel terminology and refer to our volunteers as
"volunteers" (English) and "Helferinnen" (German). On top, we choose to use the
generic feminine form ("Helferinnen"). To implement this, we modify the language
files that live in `resources/lang/`.

The main `Dockerfile` already compiles the `.mo` files from the respective `.po`
files. We conveniently hook into this process and patch these `.po` files before
the compilation by executing a few custom scripts.

Whenever the upstream changes or adds new texts, these scripts might need to be
adapted! At the end of the scripts, they look for common words from the "angel
terminology" so you can run them locally to check whether they currently
miss any instances.

For the German language files, we run `sed -i` a bunch of times. While most
patterns are fairly generic, some are very specific to individual strings. We
tried to avoid patterns that are "sub-patterns" of others, but beware!

For the English language, not all strings are put into `.mo` files (yet?). Some
(still?) live as default values in the source code. We thus first create the
`additional.po` file that contains all such strings that are not yet part of
`default.po`. After that, we apply a similar script as for the German files.


### Backups

In the FrOSCon context, the `helferinnensystem` runs on a dedicated VM that gets
regular snapshots. We do regular backups because

- we want to have backups in place in case the FrOSCon setup changes or this is
  used in another context where the backup strategy is different
- we might not want to rely on getting a full-VM backup in an emergency
- we might want to be able to diff changes since the last backup in a meaningful
  and easy way
- the backups are simple and small

We add a `docker/backup.Dockerfile` that is built and started as another
container with (root) access to the database. This container runs `cron` with
two jobs:

- an hourly `mysqldump` of all databases to a file
- a daily removal of all files older than a week

The files are written to a `backup` volume living on the host. We retain
`7 * 24 = 168` files at most, and full dumps at the end of FrOSCon used to be
below 1MB.


#### Restore a backup

Due to the whole container setup, we recommend using the
[phpmyadmin container](#minor-stuff) for all database operations. If you really
want to use the command line, you might need to go into the database container
and connect from there. Note that piping files into mysql in this scenario
either requires copying this file into the container first, or some pretty
fragile piping magic through `docker exec`...

To first inspect a backup file and possibly compare to the current state:

- create a new database `helferinnensystem_backup`
- import the backup file

To replace the current database with a backup:

- rename the current database to `helferinnensystem_backup`
- create a new database `helferinnensystem`
- import the backup file

Note that these operations drop database-specific privileges. This is the
reason why we initially grant all privileges on `helferinnensystem%`. If you
fail to use the proper prefix for the database name, you may not have access to
the database, unless you use the `root` user.


### Minor stuff

Our fork does a few other rather simple changes:

- Add the FrOSCon logo as favicon and volunteer icon
- Grant database privileges on all `helferinnensystem%` to simplify typical
  backup operations where we create `helferinnensystem_<last year>` and such.
  This is automatically executed when the database container is first started.
- Add `phpmyadmin` container to allow for manual inspection and modification of
  the database. Only starts with `docker compose --profile dev up -d` on port
  `5081`.
