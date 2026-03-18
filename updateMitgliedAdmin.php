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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed'], JSON_THROW_ON_ERROR);
    exit;
}

$originalPilotId = isset($_POST['original_pilot_id']) ? (int)$_POST['original_pilot_id'] : 0;
$newPilotId = isset($_POST['pilot_id']) ? (int)$_POST['pilot_id'] : 0;

if ($originalPilotId <= 0 || $newPilotId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'pilot_id ist ungültig'], JSON_THROW_ON_ERROR);
    exit;
}

$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$fluggeraet = trim($_POST['fluggeraet'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($firstname === '' || $lastname === '' || $email === '' || $fluggeraet === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Pflichtfelder fehlen'], JSON_THROW_ON_ERROR);
    exit;
}

$maxDiensteRaw = trim((string)($_POST['max_dienste_halbjahr'] ?? ''));
$maxDienste = $maxDiensteRaw === '' ? null : (int)$maxDiensteRaw;

$params = [
    'new_pilot_id' => $newPilotId,
    'firstname' => $firstname,
    'lastname' => $lastname,
    'verein' => (int)($_POST['verein'] ?? 0),
    'fluggeraet' => $fluggeraet,
    'windenfahrer' => empty($_POST['windenfahrer']) ? 0 : 1,
    'dienste_admin' => empty($_POST['dienste_admin']) ? 0 : 1,
    'password' => $password,
    'email' => $email,
    'avatar' => (int)($_POST['avatar'] ?? 1),
    'newsletter' => empty($_POST['newsletter']) ? 0 : 1,
    'duty_reminder' => empty($_POST['duty_reminder']) ? 0 : 1,
    'duty_reminder_days' => (int)($_POST['duty_reminder_days'] ?? 7),
    'wuensche_reminder' => empty($_POST['wuensche_reminder']) ? 0 : 1,
    'max_dienste_halbjahr' => $maxDienste,
    'original_pilot_id' => $originalPilotId
];

$updateSql = 'UPDATE mitglieder SET
    pilot_id = :new_pilot_id,
    firstname = :firstname,
    lastname = :lastname,
    verein = :verein,
    fluggeraet = :fluggeraet,
    windenfahrer = :windenfahrer,
    dienste_admin = :dienste_admin,
    password = :password,
    email = :email,
    avatar = :avatar,
    newsletter = :newsletter,
    duty_reminder = :duty_reminder,
    duty_reminder_days = :duty_reminder_days,
    wuensche_reminder = :wuensche_reminder,
    max_dienste_halbjahr = :max_dienste_halbjahr
    WHERE pilot_id = :original_pilot_id';

$result = Database::execute($updateSql, $params);

if (empty($result['success'])) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $result['error'] ?? 'Update fehlgeschlagen'
    ], JSON_THROW_ON_ERROR);
    exit;
}

$rows = Database::query('SELECT * FROM mitglieder WHERE pilot_id = :pilot_id LIMIT 1', ['pilot_id' => $newPilotId]);
$updatedMember = (is_array($rows) && $rows !== []) ? current($rows) : null;

echo json_encode([
    'success' => true,
    'member' => $updatedMember
], JSON_THROW_ON_ERROR);
