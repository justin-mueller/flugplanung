<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

header('Content-Type: application/json');

if (!isset($_POST['flugtag']) || !isset($_POST['pilot_id'])) {
    echo json_encode(['error' => 'Missing flugtag or pilot_id parameter'], JSON_THROW_ON_ERROR);
    exit;
}

$sql = "DELETE FROM dienste WHERE flugtag = :flugtag AND pilot_id = :pilot_id";

try {
    $result = Database::execute($sql, [
        'flugtag' => $_POST['flugtag'],
        'pilot_id' => $_POST['pilot_id']
    ]);

    echo json_encode(['success' => true], JSON_THROW_ON_ERROR);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error deleting dienst: ' . $e->getMessage()], JSON_THROW_ON_ERROR);
}
