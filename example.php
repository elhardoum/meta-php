<?php

defined ( 'DB_HOST' ) || define ( 'DB_HOST', 'localhost' );
defined ( 'DB_NAME' ) || define ( 'DB_NAME', 'test2' );
defined ( 'DB_USER' ) || define ( 'DB_USER', 'root' );
defined ( 'DB_PASS' ) || define ( 'DB_PASS', '2414211' );

include __DIR__ . '/Src/loader.php';

/**
  * Update an option to save custom data e.g setting into db
  */

update_option ( 'test', time() );

/**
  * Get this updated option
  */

print_r( get_option('test') ); // probably some future unix time

/**
  * Update the name of user #1
  */

update_user_meta ( 1, 'name', 'Samuel Elh' );

/**
  * Now retrieve the updated value
  */ 

print_r( get_user_meta( 1, 'name' ) ); // Samuel Elh

/**
  * Delete the test meta
  */ 

delete_option ( 'test' );
delete_user_meta ( 1, 'name' );