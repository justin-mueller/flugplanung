<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

if ($_POST['update']) {
    $sql = 'UPDATE tagesplanung SET Kommentar = :kommentar WHERE pilot_id = :pilotid AND flugtag = :flugtag';
} else {
    $sql = 'INSERT INTO tagesplanung (pilot_id, Kommentar, flugtag) VALUES (:pilotid, :kommentar, :flugtag)';
}

$result = Database::execute($sql, [
    'kommentar' => $_POST['kommentar'],
    'pilotid' => $_POST['pilotid'],
    'flugtag' => $_POST['flugtag']
]);

// Save site priorities
Database::execute(
    'DELETE FROM tagesplanung_sites WHERE pilot_id = :pilotid AND flugtag = :flugtag',
    ['pilotid' => $_POST['pilotid'], 'flugtag' => $_POST['flugtag']]
);
foreach ($_POST['prio_result'] as $siteIndex => $priority) {
    Database::execute(
        'INSERT INTO tagesplanung_sites (pilot_id, flugtag, site_index, priority) VALUES (:pilotid, :flugtag, :site_index, :priority)',
        ['pilotid' => $_POST['pilotid'], 'flugtag' => $_POST['flugtag'], 'site_index' => $siteIndex, 'priority' => $priority]
    );
}

header('Content-Type: application/json');
echo json_encode($result, JSON_THROW_ON_ERROR);
