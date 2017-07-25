<?php

namespace MetaPHP;

class Database
{
    public static function instance()
    {
        static $instance = null;
        
        if ( null === $instance ) {
            $instance = new Database;
            $instance->setup();
        }

        return $instance;
    }

    private function setup()
    {}

    public static function db($close=null)
    {
        static $db = null;

        if ( $close ) {
            $db = null;
        }

        else if ( null === $db ) {
            $db = new \PDO(
                sprintf('mysql:host=%s;dbname=%s', DB_HOST, DB_NAME),
                DB_USER,
                DB_PASS
            );
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        return $db;
    }

    public function disconnect()
    {
        $this->db(true);

        return $this;
    }
}