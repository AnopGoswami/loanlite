<p align="center"></p>

## About LoanLite

Loanlite is a simple app written in php laravel 8 framework. It provides api endpoints
which can be used by customers to register, login and apply for loans. Admin user can view loan requests and can approve/decline. If loan is approved then customer can pay installments.

## Api resources:

Api Documentation : https://documenter.getpostman.com/view/8736410/2s7YYoBmR3

Postman Collection : https://www.getpostman.com/collections/13bc7c2ed5fc1818dd0d


## Steps to setup project:

#### Method 1

Install packages
```
composer install
```

Change database config in .env file. For example:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=loanlite
DB_USERNAME=root
DB_PASSWORD=root
```
Migrate database
```
php artisan migrate
```
Start server on default port 8000
```
php artisan serve
```

#### Method 2

If you have make utility installed then you just need to change .env file as per your database configuration and just run below command.

```
composer install && make run
```

## Run unit/fetaure test:

```
make test
```

## Step to test:

+ Register admin user
+ Register customer
+ Login customer
+ Customer apply for loan
+ Admin login
+ Admin view loan
+ Admin approve loan
+ Customer pay loan payments
+ Customer view loan