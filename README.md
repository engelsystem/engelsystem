[![pipeline status](https://chaos.expert/engelsystem/engelsystem/badges/main/pipeline.svg)](https://chaos.expert/engelsystem/engelsystem/commits/main)
[![coverage report](https://chaos.expert/engelsystem/engelsystem/badges/main/coverage.svg)](https://chaos.expert/engelsystem/engelsystem/commits/main)
[![GPL](https://img.shields.io/github/license/engelsystem/engelsystem.svg?maxAge=2592000)](LICENSE)

This is our fork of the [engelsystem](https://engelsystem.de).

# Helferinnensystem

## Installation
The fork of Engelsystem is meant to be installed by using the provided [docker setup](#docker).

### Docker
#### Build
To build the `helferinnen_server` container:
```bash
cd docker
docker-compose build
```

#### Configuration
Before running change the configuration enviroments parameters in `docker-compose.yml` as necessary, especially change the `MYSQL_PASSWORD`.

#### Run
Start the Engelsystem
```bash
cd docker
docker-compose up -d
```

#### Set Up / Migrate Database
Create the Database Schema (on a fresh install) or import database changes to migrate it to the newest version
```bash
cd docker
docker-compose exec helferinnen_server bin/migrate
```
