Master:
[![ci](https://github.com/unil-lettres/impact/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/unil-lettres/impact/actions/workflows/ci.yml)
[![CodeFactor](https://www.codefactor.io/repository/github/unil-lettres/impact/badge/master)](https://www.codefactor.io/repository/github/unil-lettres/impact/overview/master)

Development:
[![ci](https://github.com/unil-lettres/impact/actions/workflows/ci.yml/badge.svg?branch=development)](https://github.com/unil-lettres/impact/actions/workflows/ci.yml)
[![CodeFactor](https://www.codefactor.io/repository/github/unil-lettres/impact/badge/development)](https://www.codefactor.io/repository/github/unil-lettres/impact/overview/development)

# Introduction

multIMedia interface: Presentation – Analysis – CommenT

A Laravel 12 app with react components.

# Development with Docker

## Docker installation

A working [Docker](https://docs.docker.com/engine/install/) installation is mandatory.

## Environment files

Please make sure to copy & rename the **example.env** file to **.env**.

``cp docker/example.env docker/.env``

You can replace the values if needed, but the default ones should work for local development.

Please also make sure to copy & rename the docker-compose.override.yml.dev file to docker-compose.override.yml.

``cp docker-compose.override.yml.dev docker-compose.override.yml``

You can replace the values if needed, but the default ones should work for local development.

## Edit hosts file

Edit hosts file to point **impact.lan** to your docker host.

## Installation & configuration

Build & run all the containers for this project.

``docker-compose up`` (add -d if you want to run in the background and silence the logs)

## Populate the database

The first time you run the application you'll need to populate your database with initial data.

``docker exec impact-app php artisan db:seed``

If you want completely wipe your database and populate it with fresh data, you can use the following command.

``docker exec impact-app php artisan migrate:fresh --seed``

## Assets

Assets are compiled when the container is built, but if you want to recompile them, you can use the following command.

``docker exec impact-app npm run dev``

or if you want to watch for changes.

``docker exec impact-app npm run watch``

## Frontends

To access the main application please use the following link.

[http://impact.lan:8787](http://impact.lan:8787)

+ admin-user@example.com / password

### Telescope

To access the debug tool please use the following link.

[http://impact.lan:8787/telescope](http://impact.lan:8787/telescope)

### phpMyAdmin

To access the database please use the following link.

[http://impact.lan:9898](http://impact.lan:9898)

+ Server: impact-mysql
+ Username: user
+ Password: password

### MailHog

To access mails please use the following link.

[http://impact.lan:8025](http://impact.lan:8025)

Or to get the messages in JSON format.

[http://impact.lan:8025/api/v2/messages](http://impact.lan:8025/api/v2/messages)

## PHP code style

All PHP files will be inspected during CI for code style issues. If you want to make a dry run beforehand, use the following command.

``docker exec impact-app ./vendor/bin/pint --test``

And if you want to automatically fix the issues.

``docker exec impact-app ./vendor/bin/pint``

## Tests

### Unit/Feature tests

To run the full suite:

`docker exec -it impact-app php artisan test`

### Browser tests

You need to install Chrom Driver first:

`docker exec -it impact-app php artisan dusk:chrome-driver`

To run the full suite:

`docker exec -it impact-app php artisan dusk --env=testing`

To run a specific class:

`docker exec -it impact-app php artisan dusk tests/Browser/MyTest.php --env=testing`

To view the integration tests running in the browser, go to [http://impact.lan:4444](http://impact.lan:4444), click on Sessions, you should see a line corresponding to the running tests and a camera icon next to it, click on it to open a VNC viewer ("secret" as password).

# Deployment with Docker

## Environment files

Copy and rename the following environment files.

```
cp docker/example.env docker/.env
cp site/.env.example site/.env
```

You should replace the values since the default ones are not ready for production.

To authenticate with Shibboleth, don't forget to uncomment and set the `SHIB_HOSTNAME` and `SHIB_CONTACT` variables in `site/.env`, otherwise you only be abel to use the local authentication.

Please also make sure to copy & rename the **docker-compose.override.yml.prod** file to **docker-compose.override.yml**.

`cp docker-compose.override.yml.prod docker-compose.override.yml`

You can replace the values if needed, but the default ones should work for production.

## Installation & configuration

Build & run all the containers for this project.

`docker compose up -d`

## Reverse proxy

Use a reverse proxy configuration to map the url to port `8787`.

# Docker images

Changes in the `development` branch will create new images tagged `(worker-)latest-dev` & `(worker-)latest-stage`, while changes in the `master` branch will create an image tagged `(worker-)latest`. And finally, when a new tag is created, an image with the matching tag will be automatically built.

# Error tracker

[https://www.bugsnag.com](https://www.bugsnag.com)

# Helm

The Helm charts for this project are available at [https://github.com/unil-lettres/k8s](https://github.com/unil-lettres/k8s), in the ``impact`` directory.
