<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$params = ['pilotid' => $_POST['pilotid_delete'], 'flugtag' => $_POST['flugtag']];

Database::execute('DELETE FROM tagesplanung_sites WHERE pilot_id = :pilotid AND flugtag = :flugtag', $params);
$result = Database::execute('DELETE FROM tagesplanung WHERE Pilot_ID = :pilotid AND flugtag = :flugtag', $params);

header('Content-Type: application/json');
echo json_encode($result, JSON_THROW_ON_ERROR);
