<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['success' => false, 'error' => 'Method not allowed'], JSON_THROW_ON_ERROR);
	exit;
}

$clubId = (int)(Helper::$configuration['clubId'] ?? 0);
$userClubId = (int)($_SESSION['mitgliederData']['vereinId'] ?? 0);

if ($userClubId !== $clubId) {
	http_response_code(403);
	echo json_encode(['success' => false, 'error' => 'Nur Vereinsmitglieder dürfen Flugtage eröffnen.'], JSON_THROW_ON_ERROR);
	exit;
}

$datum = $_POST['datum'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datum)) {
	http_response_code(400);
	echo json_encode(['success' => false, 'error' => 'Ungültiges Datumsformat.'], JSON_THROW_ON_ERROR);
	exit;
}

$sql = 'INSERT INTO flugtage (datum) VALUES (:datum)';
$result = Database::execute($sql, ['datum' => $datum]);

if (!$result['success']) {
	if (isset($result['error']) && str_contains($result['error'], 'Duplicate entry')) {
		http_response_code(409);
		echo json_encode(['success' => false, 'error' => 'Dieser Flugtag ist bereits eingetragen.'], JSON_THROW_ON_ERROR);
		exit;
	}

	http_response_code(500);
	echo json_encode(['success' => false, 'error' => 'Der Flugtag konnte nicht gespeichert werden.'], JSON_THROW_ON_ERROR);
	exit;
}

echo json_encode($result, JSON_THROW_ON_ERROR);
