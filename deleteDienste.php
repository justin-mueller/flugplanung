<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sqlDelete = 'DELETE FROM dienste WHERE YEAR(flugtag) = :year';

header('Content-Type: application/json');
if (Database::query($sqlDelete, ['year' => $_GET['year']]) !== false) {
    echo json_encode(['success' => true], JSON_THROW_ON_ERROR);
} else {
    echo json_encode(['success' => false], JSON_THROW_ON_ERROR);
}
