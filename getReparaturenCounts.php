<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sql = 'SELECT 
            fluggebiet,
            level,
            COUNT(*) as count,
            GROUP_CONCAT(text SEPARATOR "|||") as texts
        FROM reparaturen 
        WHERE closed = 0 
        GROUP BY fluggebiet, level 
        ORDER BY fluggebiet, level';

$result = Database::query($sql, []);

$reparaturenData = [
    'NGL' => ['level0' => ['count' => 0, 'texts' => []], 'level1' => ['count' => 0, 'texts' => []]],
    'HRP' => ['level0' => ['count' => 0, 'texts' => []], 'level1' => ['count' => 0, 'texts' => []]],
    'AMD' => ['level0' => ['count' => 0, 'texts' => []], 'level1' => ['count' => 0, 'texts' => []]]
];

if ($result) {
    foreach ($result as $row) {
        $fluggebiet = $row['fluggebiet'];
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
