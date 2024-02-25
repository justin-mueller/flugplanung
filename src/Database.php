<?php

namespace JustinMueller\Flugplanung;

class Database
{
    static protected $conn;

    public static function connect(): void
    {
        if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "flugplanung";
            $port = 3306;
        } else {
            $servername = "localhost";
            $username = "-";
            $password = "-";
            $dbname = "-";
            $port = 3306;
        }

        self::$conn = new \mysqli($servername, $username, $password, $dbname, $port);
        self::$conn->set_charset("utf8");

        if (self::$conn->connect_error) {
            die("Connection failed: " . self::$conn->connect_error);
        }
    }

    public static function close(): void
    {
        self::$conn->close();
    }

    public static function query(string $sql)
    {
        return self::$conn->query($sql);
    }

    public static function insertSqlStatement(string $sql): array
    {
        if (self::$conn->query($sql) === TRUE) {
            // Successful insertion
            return ['success' => true];
        } else {
            // Error in insertion
            return ['success' => false, 'error' => self::$conn->error];
        }
    }
}
