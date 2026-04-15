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
        self::updateFlightDaysTableName();
        self::migrateSitesToNormalized();
    }

    protected static function updateFlightDaysTableName(): void
    {
        // rename moegliche_flugtage -> flugtage (#16)
        $result = self::query(
            'SELECT COUNT(*) AS exists FROM information_schema.tables WHERE table_schema = database() AND table_name = :tableName',
            ['tableName' => 'moegliche_flugtage']
        );
        if (is_array($result) && current($result)['exists'] === 1) {
            self::$conn->exec('RENAME TABLE moegliche_flugtage TO flugtage;');
        }
    }

    private static function migrateSitesToNormalized(): void
    {
        // Check if migration is needed (old betrieb_ngl column still exists on flugtage)
        $columns = self::query(
            "SELECT COUNT(*) AS col_exists FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = database() AND TABLE_NAME = 'flugtage' AND COLUMN_NAME = 'betrieb_ngl'",
            []
        );
        if (!is_array($columns) || (int)current($columns)['col_exists'] === 0) {
            return; // Already migrated
        }

        $sites = Helper::$configuration['sites'];
        $shortToIndex = [];
        foreach ($sites as $index => $site) {
            // Support both new [{name, short}] and old ['Name', ...] config formats
            $short = is_array($site) ? $site['short'] : null;
            if ($short !== null) {
                $shortToIndex[strtoupper($short)] = $index;
            }
        }

        // Fallback: if config still uses old flat format, use hardcoded mapping
        // (these are the only shorthands that could exist in the old schema)
        if (empty($shortToIndex)) {
            $shortToIndex = ['NGL' => 0, 'HRP' => 1, 'AMD' => 2];
        }

        // 1. Create new tables
        self::$conn->exec('CREATE TABLE IF NOT EXISTS tagesplanung_sites (
            pilot_id int(11) NOT NULL,
            flugtag date NOT NULL,
            site_index tinyint NOT NULL,
            priority int(11) NOT NULL,
            PRIMARY KEY (pilot_id, flugtag, site_index)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        self::$conn->exec('CREATE TABLE IF NOT EXISTS flugtage_betrieb (
            datum date NOT NULL,
            site_index tinyint NOT NULL,
            betrieb tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (datum, site_index)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // 2. Migrate flugtage betrieb data
        $rows = self::query('SELECT datum, betrieb_ngl, betrieb_hrp, betrieb_amd FROM flugtage', []);
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $oldCols = ['NGL' => $row['betrieb_ngl'], 'HRP' => $row['betrieb_hrp'], 'AMD' => $row['betrieb_amd']];
                foreach ($oldCols as $short => $val) {
                    if (isset($shortToIndex[$short])) {
                        self::execute(
                            'INSERT IGNORE INTO flugtage_betrieb (datum, site_index, betrieb) VALUES (:d, :s, :b)',
                            ['d' => $row['datum'], 's' => $shortToIndex[$short], 'b' => $val]
                        );
                    }
                }
            }
        }

        // 3. Migrate tagesplanung site priorities
        $rows = self::query('SELECT pilot_id, flugtag, NGL, HRP, AMD FROM tagesplanung', []);
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $oldCols = ['NGL' => $row['NGL'], 'HRP' => $row['HRP'], 'AMD' => $row['AMD']];
                foreach ($oldCols as $short => $val) {
                    if (isset($shortToIndex[$short])) {
                        self::execute(
                            'INSERT IGNORE INTO tagesplanung_sites (pilot_id, flugtag, site_index, priority) VALUES (:p, :f, :s, :v)',
                            ['p' => $row['pilot_id'], 'f' => $row['flugtag'], 's' => $shortToIndex[$short], 'v' => $val]
                        );
                    }
                }
            }
        }

        // 4. Migrate reparaturen enum to site_index
        $hasFluggebiet = self::query(
            "SELECT COUNT(*) AS col_exists FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = database() AND TABLE_NAME = 'reparaturen' AND COLUMN_NAME = 'fluggebiet'",
            []
        );
        if (is_array($hasFluggebiet) && (int)current($hasFluggebiet)['col_exists'] === 1) {
            self::$conn->exec('ALTER TABLE reparaturen ADD COLUMN site_index tinyint NULL');
            foreach (['NGL', 'HRP', 'AMD'] as $short) {
                if (isset($shortToIndex[$short])) {
                    self::execute(
                        'UPDATE reparaturen SET site_index = :idx WHERE fluggebiet = :fg',
                        ['idx' => $shortToIndex[$short], 'fg' => $short]
                    );
                }
            }
            self::$conn->exec('ALTER TABLE reparaturen DROP COLUMN fluggebiet');
            self::$conn->exec('ALTER TABLE reparaturen MODIFY site_index tinyint NOT NULL');
        }

        // 5. Drop old columns
        self::$conn->exec('ALTER TABLE flugtage DROP COLUMN betrieb_ngl, DROP COLUMN betrieb_hrp, DROP COLUMN betrieb_amd');
        self::$conn->exec('ALTER TABLE tagesplanung DROP COLUMN NGL, DROP COLUMN HRP, DROP COLUMN AMD');
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
