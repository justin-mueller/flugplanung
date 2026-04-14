<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$siteCount = Helper::getSiteCount();

$flugtag = Database::query(
    'SELECT abgesagt, aufbau FROM flugtage WHERE datum = :flugtag',
    ['flugtag' => $_GET['flugtag']]
);

$betriebRows = Database::query(
    'SELECT site_index, betrieb FROM flugtage_betrieb WHERE datum = :flugtag',
    ['flugtag' => $_GET['flugtag']]
);

header('Content-Type: application/json');
if ($flugtag !== false && $flugtag !== []) {
    $row = current($flugtag);
    $betrieb = array_fill(0, $siteCount, '0');
    if (is_array($betriebRows)) {
        foreach ($betriebRows as $b) {
            $betrieb[(int)$b['site_index']] = $b['betrieb'];
        }
    }
    $row['betrieb'] = $betrieb;
    echo json_encode($row, JSON_THROW_ON_ERROR);
} else {
    echo json_encode(['error' => 'No entry found for this date'], JSON_THROW_ON_ERROR);
}
