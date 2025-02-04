<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sql = 'DELETE FROM tagesplanung WHERE Pilot_ID = :pilotid';

$result = Database::execute($sql, ['pilotid' => $_POST['pilotid_delete']]);

header('Content-Type: application/json');
echo json_encode($result, JSON_THROW_ON_ERROR);
