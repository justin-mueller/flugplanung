<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Database::connect();

    $sql = 'DELETE FROM mitglieder WHERE pilot_id = :pilotid';

    $result = Database::query($sql, ['pilotid' => $_SESSION['mitgliederData']['pilot_id']]);

    header('Content-Type: application/json');
    echo json_encode($result, JSON_THROW_ON_ERROR);
}
