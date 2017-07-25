<?php

use MetaPHP\Meta;

require_once __DIR__ . '/../vendor/autoload.php';

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