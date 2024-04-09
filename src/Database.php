<?php

namespace JustinMueller\Flugplanung;

class Database
{
    static protected \PDO $conn;

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

        self::$conn = new \PDO(
            sprintf('mysql:host=%s;port=%s;dbname=%s', $servername, $port, $dbname),
            $username,
            $password
        );
    }

    public static function query(string $sql, array $parameters): bool|array
    {
        $statement = self::$conn->prepare($sql);
        $statement->execute($parameters);
        return $statement->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public static function insertSqlStatement(string $sql, array $parameters): array
    {
        $statement = self::$conn->prepare($sql);
        $success = $statement->execute($parameters);

        if ($success === TRUE) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => implode(' -- ', $statement->errorInfo())];
    }
}
