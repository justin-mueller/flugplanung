<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$query = "SELECT * FROM moegliche_flugtage WHERE datum = :flugtag";
$result = Database::query($query, ['flugtag' => $_GET['flugtag']]);
$row = current($result);

header('Content-Type: application/json');
if ($row) {
    echo json_encode($row, JSON_THROW_ON_ERROR);
} else {
    echo json_encode(['error' => 'No entries found in the "mitglieder" table'], JSON_THROW_ON_ERROR);
}
