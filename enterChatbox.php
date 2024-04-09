<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::checkLogin();
Database::connect();

$sql = 'INSERT INTO chatbox  (Pilot_ID, text) VALUES (:pilotId, :text)';

Database::insertSqlStatement(
    $sql,
    [
        'pilotId' => $_POST['pilot_id'],
        'text' => $_POST['text']
    ]
);
