
![Logo](https://github.com/Ankush-WD/Laravel-blog-Api/blob/master/banner.png?raw=true)

## About Laravel Blog API

Laravel is a web application framework with expressive, elegant syntax. This repo contain blog api along with Swagger ui documentation. We have integrated 3rd part package to make api code more clean and standardize and it's build on laravel 8 version.


## 3rd packages used

Below are the package used to build api.

 - [Sanctum](https://laravel.com/docs/8.x/sanctum)
 - [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary/v9/introduction)
 - [Spatie sluggable](https://github.com/spatie/laravel-sluggable)
 - [Swagger UI](https://github.com/DarkaOnLine/L5-Swagger)


## Requirment 

PHP 7.4+ or above.


## Installation

Run below command 

Install dependencey packages 

```bash
  npm run deploy
```
Given read, write permission to a storage folder to avoid permission error

```bash
  sudo chmod -R 777 storage/
```
Generate larval key 

```bash
  php artisan key: generate
```
This command will create tables in a database that you mentioned on you .env file.

```bash
  php artisan migrates
```
Below command will generate dummy user
```bash
  php artisan migrates
```
php artisan deb: seed --class=UserSeeder

Once you run the user seeder, it will create some user. Below is a login credential of one of a user.
```bash
  Login credentials 
  Email   : addy@xyz.com
  Password: password
```
After running above command, add below key in your .env file to generate api documentation. URL must be your API base URL.
```bash
  Example:
  L5_SWAGGER_CONST_HOST=http://localhost/Laravel-blog-Api
```
After adding key run below command to reinitialize Swagger UI documentation.
```bash
  php artisan l5-swagger:generate
```
To get the API documentation visit below URL 

```bash
  Example:
  http://localhost/Laravel-blog-Api/api/docs
```
api/docs is the route after API base URL you can change by visit on l5-swagger.php file inside config folder.
