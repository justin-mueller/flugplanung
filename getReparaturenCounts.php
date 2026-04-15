<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sql = 'SELECT
            site_index,
            level,
            COUNT(*) as count,
            GROUP_CONCAT(text SEPARATOR "|||") as texts
        FROM reparaturen
        WHERE closed = 0
        GROUP BY site_index, level
        ORDER BY site_index, level';

$result = Database::query($sql, []);

$sites = Helper::$configuration['sites'];
$reparaturenData = array_map(fn($site) => [
    'short' => $site['short'],
    'name' => $site['name'],
    'level0' => ['count' => 0, 'texts' => []],
    'level1' => ['count' => 0, 'texts' => []]
], $sites);

if ($result) {
    foreach ($result as $row) {
        $fluggebiet = (int)$row['site_index'];
        $level = $row['level'];
        $count = (int)$row['count'];
        $texts = $row['texts'] ? explode('|||', $row['texts']) : [];

        $levelKey = $level == 1 ? 'level1' : 'level0';
        $reparaturenData[$fluggebiet][$levelKey]['count'] = $count;
        $reparaturenData[$fluggebiet][$levelKey]['texts'] = $texts;
    }
}

header('Content-Type: application/json');
echo json_encode($reparaturenData, JSON_THROW_ON_ERROR);
