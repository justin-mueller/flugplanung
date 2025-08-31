<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$fluggebiet = $_POST['fluggebiet'] ?? '';
$text = $_POST['text'] ?? '';
$level = $_POST['level'] ?? 0;

if ($fluggebiet === '' || $text === '') {
    http_response_code(400);
    echo 'Missing parameters';
    exit;
}

$sql = 'INSERT INTO reparaturen (`fluggebiet`, `text`, `level`, `closed`) VALUES (:fluggebiet, :text, :level, 0)';
$result = Database::execute($sql, ['fluggebiet' => $fluggebiet, 'text' => $text, 'level' => $level]);

header('Content-Type: application/json');
echo json_encode($result, JSON_THROW_ON_ERROR);


