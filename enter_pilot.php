<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

if ($_POST['update']) {
    $sql = 'UPDATE tagesplanung SET  Kommentar = :kommentar, NGL = :ngl, HRP = :hrp, AMD = :amd, zeit = :zeit, flugtag = :flugtag WHERE Pilot_ID = :pilotid AND flugtag = :flugtag';
} else {
    $sql = 'INSERT INTO tagesplanung (Pilot_ID,  Kommentar, NGL, HRP, AMD, flugtag, zeit) VALUES (:pilotid, :kommentar, :ngl, :hrp, :amd , :flugtag, :zeit)';
}

$result = Database::execute($sql, [
    'kommentar' => $_POST['kommentar'],
    'ngl' => $_POST['prio_result'][0],
    'hrp' => $_POST['prio_result'][1],
    'amd' => $_POST['prio_result'][2],
    'pilotid' => $_POST['pilotid'],
    'flugtag' => $_POST['flugtag'],
    'zeit' => $_POST['zeit']
]);

header('Content-Type: application/json');
echo json_encode($result, JSON_THROW_ON_ERROR);
