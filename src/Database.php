<?php

namespace JustinMueller\Flugplanung;

class Database
{
    static protected \PDO $conn;

    public static function connect(): void
    {
        $servername = Helper::$configuration['db']['servername'];
        $username = Helper::$configuration['db']['username'];
        $password = Helper::$configuration['db']['password'];
        $dbname = Helper::$configuration['db']['dbname'];
        $port = Helper::$configuration['db']['port'];

        self::$conn = new \PDO(
            sprintf('mysql:host=%s;port=%d;dbname=%s', $servername, $port, $dbname),
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

    public static function execute(string $sql, array $parameters): array
    {
        $statement = self::$conn->prepare($sql);
        $success = $statement->execute($parameters);

        if ($success === TRUE) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => implode(' -- ', $statement->errorInfo())];
    }
}
