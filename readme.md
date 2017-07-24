# Meta data PHP

Makes it easier saving meta data into the database, inspired by WordPress meta (users, options, posts, etc.)

## About

This helper class allows you to save custom meta data into the database, and makes manipulating and retrieving these data quite easier, and the performance is a bonus.

By default, once you update a meta, or retrieve it for the first time, it is stored into a global variable to avoid multiple queries.

From my setup, I have Redis configured and it's easier to cache these meta for performance that rocks.


## Installation

1. Download the repo into your project

`git clone https://github.com/elhardoum/meta-php`

2. Define the database credentials, and include the `loader.php` file into your setup

```php
defined ( 'DB_HOST' ) || define ( 'DB_HOST', '<database-host>' );
defined ( 'DB_NAME' ) || define ( 'DB_NAME', '<database-name>' );
defined ( 'DB_USER' ) || define ( 'DB_USER', '<database-user>' );
defined ( 'DB_PASS' ) || define ( 'DB_PASS', '<database-password>' );

include '/path/to/meta-php/Src/loader.php';
```

3. Now enjoy the API!

## API

...