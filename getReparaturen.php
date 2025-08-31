<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sql = 'SELECT `key`, `fluggebiet`, `text`, `level`, `closed`, `solvedText` FROM reparaturen ORDER BY `closed` ASC, `fluggebiet` ASC, `key` ASC';
$result = Database::query($sql, []);

header('Content-Type: application/json');
echo json_encode($result ?: [], JSON_THROW_ON_ERROR);


