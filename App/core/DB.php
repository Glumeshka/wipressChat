<?php

namespace App\core;

class DB
{
    const DB_USER = 'root';
    const DB_PASS = '';
    const DB_HOST = 'localhost';
    const DB_NAME = 'wipress';

    static private $conn;

    public static function connToDB()
    {
        $user = self::DB_USER;
        $pass = self::DB_PASS;
        $host = self::DB_HOST;
        $db   = self::DB_NAME;

        try
        {
            $conn = new \PDO("mysql:dbname=$db;host=$host", $user, $pass);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        } catch(\PDOException $e) {
            echo $e->getMessage();
        }

        if(self::$conn instanceof self){
            return self::$conn;
        }

        return $conn;
    }
}