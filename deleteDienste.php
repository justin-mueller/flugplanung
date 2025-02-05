<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sqlDelete = 'DELETE FROM dienste WHERE YEAR(flugtag) = :year';

$result = Database::query($sqlDelete, ['year' => $_GET['year']]);

header('Content-Type: application/json');
echo json_encode($result, JSON_THROW_ON_ERROR);
