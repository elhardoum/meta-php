create table if not exists meta (
  `object_id` bigint(20) default 0,
  `meta_key` varchar(255) not null,
  `meta_value` LONGTEXT not null,
  primary key(meta_key, object_id)
);