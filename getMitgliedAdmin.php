<?php

require_once __DIR__ . '/vendor/autoload.php';

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

header('Content-Type: application/json');

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$mitgliederData = $_SESSION['mitgliederData'] ?? [];
if (empty($mitgliederData['dienste_admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden'], JSON_THROW_ON_ERROR);
    exit;
}

$pilotId = isset($_GET['pilot_id']) ? (int)$_GET['pilot_id'] : 0;
if ($pilotId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'pilot_id fehlt oder ungültig'], JSON_THROW_ON_ERROR);
    exit;
}

$sql = 'SELECT * FROM mitglieder WHERE pilot_id = :pilot_id LIMIT 1';
$rows = Database::query($sql, ['pilot_id' => $pilotId]);

if (!is_array($rows) || $rows === []) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Mitglied nicht gefunden'], JSON_THROW_ON_ERROR);
    exit;
}

echo json_encode([
    'success' => true,
    'member' => current($rows)
], JSON_THROW_ON_ERROR);
