Master:
// TODO

Development:
// TODO

# Introduction

multIMedia interface: Presentation – Analysis – CommenT

A Laravel 6 app with react components.

# Development

## Docker installation

A working [Docker](https://docs.docker.com/engine/installation/) installation is mandatory.

## Docker environment variables file

Please make sure to copy & rename the **example.env** file to **.env**.

``cp dev/env.example dev/.env``

You can replace the values if needed, but the default ones should work.

## Edit hosts file

Edit hosts file to point **impact.lan** to you docker host.

## Environment installation & configuration

Run the following docker commands from the project root directory.

Build & run all the containers for this project.

``docker-compose up -d``

Install php dependencies.

``docker exec impact-app composer install``

Update the Laravel .env file.

``docker exec impact-app cp .env.example .env``

Update the application key.

``docker exec impact-app php artisan key:generate``

Install js dependencies.

``docker exec impact-app npm install``

``docker exec impact-app npm run dev``

Run migrations.

``docker exec impact-app php artisan migrate --no-interaction --force``

Seeding first user.

``docker exec impact-app php artisan db:seed`` 

This is only needed when you launch the project for the first time. After that you can simply use the following command from the project root directory.

``docker-compose up -d``

## Frontends

To access the main application please use the following link.

[http://impact.lan:8787](http://impact.lan:8787)

### Telescope

To access the debug tool please use the following link.

[http://impact.lan:8787/telescope](http://impact.lan:8787/telescope)

### phpMyAdmin

To access the database please use the following link.

[http://impact.lan:9898](http://impact.lan:9898)

Username: user
Password: password

### MailHog

To access the mail server please use the following link.

[http://impact.lan:8026](http://impact.lan:8026)

Or to get the messages in JSON format.

[http://impact.lan:8026/api/v2/messages](http://impact.lan:8026/api/v2/messages)

## Testing

Run the following commands from the framework root directory (/site).

### Unit testing
``./vendor/bin/phpunit``

### Functional testing
``php artisan dusk``
