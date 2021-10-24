# Flashcard Game
This is console based interactive flashcard game.

# Quick Installation
### Requirements
Composer, PHP 7.4 or greator

Download or clone the repository.

Switch to the repo folder
```sh
cd studocu
```
Install all the dependencies using composer
```sh
composer install
```
### Environment variables
Copy `.env.example` file to `.env` file and configure database.
```sh
cp .env.example .env
```
### Database
This project uses MySQL for production environment and sqlite for testing environment.

For production environment, you need to setup MySQL database credentials in .env file.

Create the database and run migrations using below command.
```sh
php artisan migrate
```
For tests, create a sqlite database with below command
```sh
touch database/database.sqlite
```

Once database is configured, we are ready to run our console application.
```sh
php artisan flashcard:interactive
```

* `.env` - Environment variables can be set in this file.

### Running Test Cases
Make sure you have already created database/database.sqlite before running test cases.

```sh
./vendor/bin/phpunit 
```

### Folders
* `app/Console/Commands` - Contains command for Flashcard console application
* `app/Models` - Contains all the Eloquent models
* `config` - Contains all the application configuration files
* `database/factories` - Contains the model factory for all the models
* `database/migrations` - Contains all the database migrations
* `database/seeds` - Contains the database seeder
* `tests` - Contains all the application tests
* `tests/Feature` - Contains all console command test
