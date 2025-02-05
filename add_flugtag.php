<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sql = 'INSERT INTO moegliche_flugtage (datum) VALUES (:datum)';
$result = Database::execute($sql, ['datum' => $_POST['datum']]);

header('Content-Type: application/json');
echo json_encode($result, JSON_THROW_ON_ERROR);
