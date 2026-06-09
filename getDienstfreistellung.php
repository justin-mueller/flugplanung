<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sql = "SELECT
    df.pilot_id,
    m.firstname,
    m.lastname,
    df.grund
FROM dienstfreistellung df
INNER JOIN mitglieder m ON df.pilot_id = m.pilot_id
WHERE m.verein = :clubId
ORDER BY m.lastname, m.firstname";

$result = Database::query($sql, ['clubId' => Helper::$configuration['clubId']]);

header('Content-Type: application/json');
echo json_encode($result ?: [], JSON_THROW_ON_ERROR);
