<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$siteIndex = $_POST['site_index'] ?? '';
$text = $_POST['text'] ?? '';
$level = $_POST['level'] ?? 0;

if ($siteIndex === '' || $text === '' || (int)$siteIndex < 0 || (int)$siteIndex >= Helper::getSiteCount()) {
    http_response_code(400);
    echo 'Missing or invalid parameters';
    exit;
}

$sql = 'INSERT INTO reparaturen (`site_index`, `text`, `level`, `closed`, `created_by`) VALUES (:site_index, :text, :level, 0, :created_by)';
$result = Database::execute($sql, [
    'site_index' => (int)$siteIndex,
    'text' => $text,
    'level' => $level,
    'created_by' => $_SESSION['mitgliederData']['pilot_id']
]);

header('Content-Type: application/json');
echo json_encode($result, JSON_THROW_ON_ERROR);


