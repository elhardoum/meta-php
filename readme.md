# Meta data PHP

Makes it easier saving meta data into the database, inspired by WordPress meta (users, options, posts, etc.)

## About

This helper class allows you to save custom meta data into the database, and makes manipulating and retrieving these data quite easier, and the performance is a bonus.

By default, once you update a meta, or retrieve it for the first time, it is stored into a global variable to avoid multiple queries.

From my setup, I have Redis configured and it's easier to cache these meta for performance that rocks.


## Installation

1. Download the repo into your project

`git clone https://github.com/elhardoum/meta-php`

2. Create the `meta` table:

```sql
create table if not exists meta (
  `object_id` bigint(20) default 0,
  `meta_key` varchar(255) not null,
  `meta_value` LONGTEXT not null,
  primary key(meta_key, object_id)
);
```

3. Define the database credentials, and include the `loader.php` file into your setup

```php
defined ( 'DB_HOST' ) || define ( 'DB_HOST', '<database-host>' );
defined ( 'DB_NAME' ) || define ( 'DB_NAME', '<database-name>' );
defined ( 'DB_USER' ) || define ( 'DB_USER', '<database-user>' );
defined ( 'DB_PASS' ) || define ( 'DB_PASS', '<database-password>' );

include '/path/to/meta-php/Src/loader.php';
```

4. Now enjoy the API!

## API

You can basically store any type of object using follows API. The data is serialized before saving and unserialized after retrieving. No data sanitization or escaping required, PDO handles that for you.

This repository ships with the default metadata functions on the global namespace. Here's some use:

1. Options

```php
// get
get_option( $option_name, $default_value=null );

// update
update_option( $option_name, $option_value );

// delete
delete_option( $option_name );

// delete all
delete_all_options();
```

2. User Meta

```php
// get
get_user_meta( $user_id, $meta_key, $default_value=null );

// update
update_user_meta( $user_id, $meta_key, $meta_value );

// delete
delete_user_meta( $user_id, $meta_key );

// delete all
delete_all_user_meta( $user_id );
```

3. Post Meta

```php
// get
get_post_meta( $post_id, $meta_key, $default_value=null );

// update
update_post_meta( $post_id, $meta_key, $meta_value );

// delete
delete_post_meta( $post_id, $meta_key );

// delete all
delete_all_post_meta( $post_id );
```

You are free to create a custom group and use it.

To use the class `Meta` (`Elhardoum\MetaPHP\Meta`), make sure also the database class `Database` (same namespace) is included as well.

```php
$Meta = Meta::instance(true);
$Meta->group('custom'); // set a custom group for the meta

$Meta->get(
  $key, // meta key
  $object_id, // if required, like in users, posts (the ID) defaults to null,
  $default_value=null // a default value to be returned, not required
);

$Meta->update(
  $key, // meta key
  $value, // a value
  $object_id=null // if required, like in users, posts (the ID) defaults to null,
);

$Meta->delete(
  $key, // meta key
  $object_id=null // if required, like in users, posts (the ID) defaults to null,
);
```

... more to come.