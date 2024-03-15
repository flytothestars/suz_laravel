# Suz-dev

This repository contains the source code for the Suz development files project.

## Technologies Used

````
- PHP 7.1
- Laravel 5.7.28
- MySQL 8.0
- Docker
- docker-compose
- Nginx
````

## Installation and Setup

~~~
To get started with the Suz development files project, follow these steps:

1) Clone the repository
2) Install Docker and docker-compose on your machine
3) Navigate to the root directory of the project in your terminal
4) Run docker-compose up -d  to start the containers
5) Run docker-compose exec app composer install  to install the dependencies
6) Run docker-compose exec app cp .env.example .env  to copy the environment file
7) Run docker-compose exec app php artisan key:generate  to generate the application key
8) Run docker-compose exec app php artisan storage:link  to create symlink for storage
9) Run docker-compose exec app php artisan migrate  to migrate the database, but I recommend you use dump firstly
10) Access the project at http://localhost
~~~~

## Folder Structure

````
- app/ - Laravel application code
- bootstrap/ - Laravel bootstrap files
- config/ - Laravel configuration files
- public/ - Laravel public files
- resources/ - Laravel resources
- routes/ - Laravel routes
- storage/ - Laravel storage files
- tests/ - Laravel tests
- database/ - Database migrations and seeds
- vendor/ - Laravel dependencies
- .env - Environment variables
- git-old - Old git files (probably trash)
- docker-compose.yml - Docker compose file
- Dockerfile - Docker file
- docker/ - Docker configuration files
- nginx/ - Nginx configuration files
````

## Contributing

````
To contribute to the Suz development files project, please follow these guidelines:
1) Fork the repository
2) Create a new branch for your changes
3) Make your changes and commit them
4) Push your changes to your forked repository
5) Open a pull request with a detailed description of your changes
````

## Notes
1) The project is still in development
2) Make this query on your db 
       ````UPDATE model_has_roles SET model_type = 'App\\Models\\User' WHERE model_type = 'App\\User';````
       because I moved models to their own folder
