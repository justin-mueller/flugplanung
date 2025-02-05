<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sql = "INSERT INTO dienste (id, flugtag, pilot_id, windenfahrer, startleiter) 
        VALUES (CONCAT(:flugtag, '_' ,:pilot_id) , :flugtag, :pilot_id, :windenfahrer, :startleiter)";

$result = Database::execute(
    $sql,
    [
        'flugtag' => $_POST['flugtag'],
        'pilot_id' => $_POST['pilot_id'],
        'windenfahrer' => $_POST['windenfahrer'],
        'startleiter' => $_POST['startleiter']
    ]
);

echo json_encode($result, JSON_THROW_ON_ERROR);
