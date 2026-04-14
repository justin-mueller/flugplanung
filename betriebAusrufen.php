<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

// Update flugtage main row
$result = Database::execute(
    'UPDATE flugtage SET abgesagt = :abgesagt, aufbau = :aufbau WHERE datum = :flugtag',
    ['flugtag' => $_POST['flugtag'], 'abgesagt' => $_POST['abgesagt'], 'aufbau' => $_POST['aufbau']]
);

if ($result['success'] === true) {
    // Update betrieb per site
    Database::execute('DELETE FROM flugtage_betrieb WHERE datum = :d', ['d' => $_POST['flugtag']]);
    $betrieb = $_POST['betrieb'] ?? [];
    foreach ($betrieb as $siteIndex => $active) {
        $result = Database::execute(
            'INSERT INTO flugtage_betrieb (datum, site_index, betrieb) VALUES (:d, :s, :b)',
            ['d' => $_POST['flugtag'], 's' => $siteIndex, 'b' => $active]
        );
        if ($result['success'] === false) {
            break;
        }
    }
}

header('Content-Type: application/json');
echo json_encode($result, JSON_THROW_ON_ERROR);
