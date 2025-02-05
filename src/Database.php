<?php

namespace JustinMueller\Flugplanung;

class Database
{
    protected static \PDO $conn;

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
        try {
            $statement = self::$conn->prepare($sql);
            $statement->execute($parameters);
        } catch (\PDOException) {
            return false;
        }
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function execute(string $sql, array $parameters): array
    {
        try {
            $statement = self::$conn->prepare($sql);
            $success = $statement->execute($parameters);
            return ['success' => $success];
        } catch (\PDOException) {
            return ['success' => false, 'error' => implode(' -- ', $statement->errorInfo())];
        }
    }
}
