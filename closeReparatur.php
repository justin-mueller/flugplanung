<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$key = $_POST['key'] ?? '';
$solvedText = $_POST['solvedText'] ?? null;

if ($key === '') {
    http_response_code(400);
    echo 'Missing key';
    exit;
}

$sql = 'UPDATE reparaturen SET `closed` = 1, `solvedText` = :solvedText, `closed_by` = :closed_by, `closed_at` = NOW() WHERE `key` = :key';
$result = Database::execute($sql, [
    'key' => $key, 
    'solvedText' => $solvedText,
    'closed_by' => $_SESSION['mitgliederData']['pilot_id']
]);

header('Content-Type: application/json');
echo json_encode($result, JSON_THROW_ON_ERROR);


