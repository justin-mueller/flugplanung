<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::checkLogin();
Database::connect();

$sql = 'DELETE FROM moegliche_flugtage WHERE datum = :datum';
$result = Database::insertSqlStatement($sql, ['datum' => $_POST['datum']]);

header('Content-Type: application/json');
echo json_encode($result, JSON_THROW_ON_ERROR);
