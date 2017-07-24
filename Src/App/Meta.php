<?php

namespace Elhardoum\MetaPHP;

/**
  * Meta class
  *
  * Makes it easier saving meta data into the database
  * inspired by WordPress meta (users, options, posts, etc.)
  * 
  * == Create the necessary database table ==
  *
  *  create table if not exists meta (
  *    `object_id` bigint(20) default 0,
  *    `meta_key` varchar(255) not null,
  *    `meta_value` LONGTEXT not null,
  *    primary key(meta_key, object_id)
  *  );
  *
  * == 
  *
  * @author Samuel Elh <samelh.com/contact>
  * @version 1.0
  * @link https://github.com/elhardoum/meta-php 
  *
  */

class Meta
{   
    /**
      * Table name
      */
    public $table = 'meta';
    
    /**
      * Meta data group
      */
    public $group;
    
    public static function instance($fresh=null) {
        static $instance = null;
        
        if ( $fresh ) {
            $fresh = new Meta;
            $fresh->setup();
            return $fresh;
        }

        if ( null === $instance ) {
            $instance = new Meta;
            $instance->setup();
        }

        return $instance;
    }

    private function setup()
    {}

    /**
      * Return an instance of database connection
      *
      * @return object \PDO instance
      */

    protected function PDO()
    {
        return Database::db();
    }

    /**
      * Create a custom meta group
      * e.g options, users, posts
      */
    
    public function group($group)
    {
        $this->group = $group;

        return $this;
    }

    protected function key($key)
    {
        return "_{$this->group}__{$key}";
    }

    public function get($key, $object_id=null, $default=null)
    {
        $meta_key = $this->key($key);

        if ( isset($GLOBALS[$meta_key . '_' . $object_id]) ) {
            return $GLOBALS[$meta_key . '_' . $object_id];
        }

        $sql = "select `object_id`,`meta_key`,`meta_value` from {$this->table} where `meta_key` = :key";
        $args = array('key' => $meta_key);

        if ( is_numeric($object_id) || $object_id ) {
            $sql .= " and `object_id` = :id";
            $args['id'] = intval($object_id);
        }

        $sql .= " order by `meta_key` desc limit 1";

        $q = $this->PDO()->prepare($sql);
        $q->execute($args);

        $meta = $q->fetch(\PDO::FETCH_OBJ);

        if ( isset($meta->meta_value) ) {
            $value = $this->preServeValue($meta->meta_value, $key);
        } else {
            $value = null;
        }

        $GLOBALS[$meta_key . '_' . $object_id] = $value;

        return $value ? $value : $default;
    }

    public function update($key, $value, $object_id=null)
    {
        $meta_key = $this->key($key);
        $value = $this->preStoreValue($value, $key);

        $fields = array(
            ':meta_key' => $meta_key, ':meta_value' => $value
        );

        if ( $object_id || is_numeric($object_id) ) {
            $fields[':object_id'] = $object_id;
        }

        $sql = sprintf(
            "insert into {$this->table} (%s) values (%s) on duplicate key update meta_value=:meta_value",
            implode(', ', array_map(function($f){
                return preg_replace('/^\:/si', '', $f);
            }, array_keys($fields))),
            implode(', ', array_keys($fields))
        );

        $q = $this->PDO()->prepare($sql);
        $q->execute($fields);

        $GLOBALS[$meta_key . '_' . $object_id] = $this->preServeValue($value, $key);

        return (bool) $q->rowCount();
    }

    public function delete($key, $object_id=null)
    {
        $meta_key = $this->key($key);

        $sql = "delete from {$this->table} where `meta_key` = :key";
        $args = array(':key' => $meta_key);

        if ( is_numeric($object_id) || $object_id ) {
            $sql .= " and `object_id` = :object_id";
            $args[':object_id'] = intval($object_id);
        }

        $sql .= " limit 1";

        $q = $this->PDO()->prepare($sql);
        $q->execute($args);

        if ( $q->rowCount() ) {
            unset($GLOBALS[$meta_key . '_' . $object_id]);
        }

        return $q->rowCount();
    }

    public function deleteAll($key, $object_id=null)
    {
        $meta_key = $this->key($key);

        $sql = "delete from {$this->table} where `meta_key` LIKE :key";
        $args = array(':key' => "{$meta_key}%");
        $gpattern = "/^{$meta_key}";

        if ( is_numeric($object_id) || $object_id ) {
            $sql .= " and `object_id` = :object_id";
            $args[':object_id'] = intval($object_id);

            $gpattern .= "(.*?)_{$object_id}$";
        }

        $gpattern .= "/si";

        $q = $this->PDO()->prepare($sql);
        $q->execute($args);

        $deleted = $q->rowCount();

        if ( $deleted ) {
            foreach ( $GLOBALS as $k=>$v ) {
                if ( !preg_match($gpattern, $k) )
                    continue;

                unset($GLOBALS[$k]);
            }

        }

        return $deleted;
    }

    private function preStoreValue($value, $key=null)
    {
        return maybe_serialize($value);
    }

    private function preServeValue($value, $key=null)
    {
        return maybe_unserialize($value);
    }

    public function deleteByObjectId($object_id)
    {
        $meta_key = $this->key(null);

        $sql = "delete from {$this->table} where `object_id` = :object_id and `meta_key` like :key";
        $args = array(':object_id' => intval($object_id), ':key' => "{$meta_key}%");

        $q = $this->PDO()->prepare($sql);
        $q->execute($args);

        if ( $q->rowCount() ) {
            unset($GLOBALS[$meta_key . '_' . $object_id]);
        }

        return $q->rowCount();
    }

    public function autoload($group, $object_id=null)
    {
        $this->group($group);
        $meta_key = $this->key(null);

        $sql = "select * from {$this->table} where `meta_key` like :key";
        $args = array(':key' => "{$meta_key}%");

        if ( is_numeric($object_id) ) {
            $sql .= " and `object_id` = :object_id";
            $args[':object_id'] = (int) $object_id;
        }

        $q = $this->PDO()->prepare($sql);
        $q->execute($args);

        $meta = $q->fetchAll(\PDO::FETCH_OBJ);

        if ( $meta ) {
            foreach ( (array) $meta as $data ) {
                if ( isset($data->meta_value) ) {
                    $data->meta_value = $this->preServeValue($data->meta_value, preg_replace(
                        '/^' . $this->key(null) . '/si',
                        '',
                        $data->meta_key
                    ));
                } else {
                    $data->meta_value = null;
                }
                $data->object_id = $data->object_id;
                $GLOBALS[$data->meta_key . '_' . $data->object_id] = $data->meta_value;
            }
        }

        return count($meta);
    }

    public static function autoloadGet($group, $object_id=null)
    {
        return self::instance()->_autoloadGet($group, $object_id);
    }

    public function _autoloadGet($group, $object_id=null)
    {
        $this->group($group);
        $meta_key = $this->key(null);

        $sql = "select * from {$this->table} where `meta_key` like :key";
        $args = array(':key' => "{$meta_key}%");

        if ( is_numeric($object_id) ) {
            $sql .= " and `object_id` = :object_id";
            $args[':object_id'] = (int) $object_id;
        }

        $q = $this->PDO()->prepare($sql);
        $q->execute($args);

        $meta = $q->fetchAll(\PDO::FETCH_OBJ);
        $ret = array();

        if ( $meta ) {
            foreach ( (array) $meta as $data ) {
                if ( isset($data->meta_value) ) {
                    $data->meta_value = $this->preServeValue($data->meta_value, preg_replace(
                        '/^' . $this->key(null) . '/si',
                        '',
                        $data->meta_key
                    ));
                } else {
                    $data->meta_value = null;
                }
                $ret[$data->meta_key] = $data->meta_value;
            }
        }

        return $ret;
    }
}