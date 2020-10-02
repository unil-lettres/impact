Master:
[![Build Status](https://travis-ci.com/unil-lettres/impact.svg?token=T7puWy4nJeaNtH7s3vRB&branch=master)](https://travis-ci.com/unil-lettres/impact)
[![CodeFactor](https://www.codefactor.io/repository/github/unil-lettres/impact/badge/master?s=dffd5ac63798e7b5abe4e58cf290ee52fbea6418)](https://www.codefactor.io/repository/github/unil-lettres/impact/overview/master)

Development:
[![Build Status](https://travis-ci.com/unil-lettres/impact.svg?token=T7puWy4nJeaNtH7s3vRB&branch=development)](https://travis-ci.com/unil-lettres/impact)
[![CodeFactor](https://www.codefactor.io/repository/github/unil-lettres/impact/badge/development?s=dffd5ac63798e7b5abe4e58cf290ee52fbea6418)](https://www.codefactor.io/repository/github/unil-lettres/impact/overview/development)

# Introduction

multIMedia interface: Presentation – Analysis – CommenT

A Laravel 8 app with react components.

# Development

## Docker installation

A working [Docker](https://docs.docker.com/engine/installation/) installation is mandatory.

## Docker environment variables file

Please make sure to copy & rename the **example.env** file to **.env**.

``cp dev/env.example dev/.env``

You can replace the values if needed, but the default ones should work.

## Edit hosts file

Edit hosts file to point **impact.lan** to your docker host.

## Environment installation & configuration

Run the following docker commands from the project root directory.

Build & run all the containers for this project.

``docker-compose up -d``

Run the setup script.

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

+ Server: database
+ Username: user
+ Password: password

### MailHog

To access mails please use the following link.

[http://impact.lan:8026](http://impact.lan:8026)

Or to get the messages in JSON format.

[http://impact.lan:8026/api/v2/messages](http://impact.lan:8026/api/v2/messages)

## Testing

Run the following commands from the framework root directory (/site). Check environment file for requirements.

### Launch local testing environment 

``php artisan serve --env=dusk.local``

### Install correct chrome driver if needed

``php artisan dusk:chrome-driver xx``

### Run functional tests

Run all tests: ``php artisan dusk``

Run specific tests: ``php artisan dusk --filter UserTest``

# Error tracker

[https://www.bugsnag.com](https://www.bugsnag.com)
