<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sql = 'UPDATE moegliche_flugtage SET betrieb_ngl = :betrieb_ngl, betrieb_hrp = :betrieb_hrp, betrieb_amd = :betrieb_amd, abgesagt = :abgesagt, aufbau = :aufbau WHERE  datum = :flugtag';
$result = Database::execute($sql, [
        'flugtag' => $_POST['flugtag'],
        'betrieb_ngl' => $_POST['flugbetrieb_ngl'],
        'betrieb_hrp' => $_POST['flugbetrieb_hrp'],
        'betrieb_amd' => $_POST['flugbetrieb_amd'],
        'abgesagt' => $_POST['abgesagt'],
        'aufbau' => $_POST['aufbau']
    ]
);

header('Content-Type: application/json');
echo json_encode($result, JSON_THROW_ON_ERROR);
