Master:
![ci](https://github.com/unil-lettres/impact/workflows/ci/badge.svg?branch=master)
[![CodeFactor](https://www.codefactor.io/repository/github/unil-lettres/impact/badge/master)](https://www.codefactor.io/repository/github/unil-lettres/impact/overview/master)

Development:
![ci](https://github.com/unil-lettres/impact/workflows/ci/badge.svg?branch=development)
[![CodeFactor](https://www.codefactor.io/repository/github/unil-lettres/impact/badge/development)](https://www.codefactor.io/repository/github/unil-lettres/impact/overview/development)

# Introduction

multIMedia interface: Presentation – Analysis – CommenT

A Laravel 11 app with react components.

# Development

## Docker installation

A working [Docker](https://docs.docker.com/engine/install/) installation is mandatory.

## Docker environment file

Please make sure to copy & rename the **example.env** file to **.env**.

``cp dev/example.env dev/.env``

You can replace the values if needed, but the default ones should work.

## Edit hosts file

Edit hosts file to point **impact.lan** to your docker host.

## Environment installation & configuration

Run the following docker commands from the project root directory.

Build & run all the containers for this project.

``docker-compose up``

Run the setup script separately. The laravel-worker process will not work until the setup script is applied.

``docker exec impact-app ./setup.sh``

This is only needed when you launch the project for the first time. After that you can simply use the following command from the project root directory.

``docker-compose up -d``

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

[http://impact.lan:8026](http://impact.lan:8026)

Or to get the messages in JSON format.

[http://impact.lan:8026/api/v2/messages](http://impact.lan:8026/api/v2/messages)

## Assets compiling

In this project the assets are pre-compiled before deployment. 

During development ``docker exec impact-app npm run dev`` or ``docker exec impact-app npm run watch`` should be used. When everything is ready to be pushed to the repository ``docker exec impact-app npm run prod`` should be used to compile assets for production.

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

To run the full suite:

`docker exec -it impact-app php artisan dusk --env=testing`

To run a specific test class:

`docker exec -it impact-app php artisan dusk tests/Browser/MyTest.php --env=testing`

To view the integration tests running in the browser, go to [http://impact.lan:4444](http://impact.lan:4444), click on Sessions, you should see a line corresponding to the running tests and a camera icon next to it, click on it to open a VNC viewer.

# Error tracker

[https://www.bugsnag.com](https://www.bugsnag.com)
