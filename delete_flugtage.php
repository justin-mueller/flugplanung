<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$result = Database::execute('DELETE FROM flugtage_betrieb WHERE datum = :datum', ['datum' => $_POST['datum']]);

if ($result['success'] === true) {
    $result = Database::execute('DELETE FROM flugtage WHERE datum = :datum', ['datum' => $_POST['datum']]);
}

header('Content-Type: application/json');
echo json_encode($result, JSON_THROW_ON_ERROR);
