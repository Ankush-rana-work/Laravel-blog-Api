
![Logo](https://github.com/Ankush-WD/Laravel-blog-Api/blob/master/banner.png?raw=true)

## About Laravel Blog API

Laravel is a web application framework with expressive, elegant syntax. This repo contain blog api along with Swagger ui documentation. We have integrated 3rd part package to make api code more clean and standardize and it's build on laravel 8 version.


## 3rd packages used

Below are the package used to build api.

 - [Passport](https://laravel.com/docs/8.x/passport)
 - [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary/v9/introduction)
 - [Spatie sluggable](https://github.com/spatie/laravel-sluggable)
 - [Swagger UI](https://github.com/DarkaOnLine/L5-Swagger)


## Requirment 

PHP 7.4+ or above.


## Installation

Run below command 

Clone repo

```bash
  git clone https://github.com/Ankush-WD/Laravel-blog-Api.git
 ``` 
Install dependencey packages 

```bash
  composer install
```
Create a .env file on root of the project then create the new database after that add the credentials on .env file as given below. Below we have used local database you can change it put to your self.

```bash
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=laravel_blog_api
    DB_USERNAME=root
    DB_PASSWORD=Tech@123
```
Given read, write permission to a storage folder to avoid permission error

```bash
  sudo chmod -R 777 storage/
```
This command will create tables in a database that you mentioned on you .env file.

```bash
  php artisan migrate
```
Generate larval key 

```bash
  php artisan key:generate
```

Below command will generate dummy user
```bash
  php artisan db:seed --class=UserSeeder
```

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
## Upcoming Integration

- Role and Permission
- Social Login
- Php unit testing

