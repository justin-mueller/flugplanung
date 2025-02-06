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

        self::setupSchema();
    }

    private static function setupSchema(): void
    {
        // rename moegliche_flugtage -> flugtage (#16)
        $result =  self::query(
            'SELECT COUNT(*) AS exists FROM information_schema.tables WHERE table_schema = database() AND table_name = :tableName',
            ['tableName' => 'moegliche_flugtage']
        );
        if (is_array($result) && current($result)['exists'] === 1) {
            self::$conn->exec('RENAME TABLE moegliche_flugtage TO flugtage;');
        }
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
