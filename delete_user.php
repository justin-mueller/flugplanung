<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Database::connect();

    $sql = 'DELETE FROM mitglieder WHERE pilot_id = :pilotid';

    if (Database::query($sql, ['pilotid' => $_SESSION['mitgliederData']['pilot_id']]) !== false) {
        $response = ['success' => true];
    } else {
        $response = ['error' => 'Error deleting record'];
    }

    header('Content-Type: application/json');
    echo json_encode($response, JSON_THROW_ON_ERROR);
}
