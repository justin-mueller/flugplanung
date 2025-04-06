<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sql = 'DELETE FROM tagesplanung WHERE Pilot_ID = :pilotid AND flugtag = :flugtag';

$result = Database::execute($sql, ['pilotid' => $_POST['pilotid_delete'], 'flugtag' => $_POST['flugtag']]);

header('Content-Type: application/json');
echo json_encode($result, JSON_THROW_ON_ERROR);
