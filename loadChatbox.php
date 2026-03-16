<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
$newerThan = isset($_GET['newerThan']) ? trim((string) $_GET['newerThan']) : '';

if ($offset < 0) {
    $offset = 0;
}

if ($limit < 1) {
    $limit = 1;
}

if ($limit > 50) {
    $limit = 50;
}

$limitPlusOne = $limit + 1;

if ($newerThan !== '') {
    $sql = 'SELECT *
        FROM chatbox cb
        LEFT JOIN mitglieder m ON m.pilot_id = cb.pilot_id
        WHERE cb.datetime > :newerThan
        ORDER BY cb.datetime ASC
        LIMIT ' . $limit;

    $result = Database::query($sql, ['newerThan' => $newerThan]);
} else {
    $sql = 'SELECT *
        FROM chatbox cb
        LEFT JOIN mitglieder m ON m.pilot_id = cb.pilot_id
        ORDER BY cb.datetime DESC
        LIMIT ' . $limitPlusOne . ' OFFSET ' . $offset;

    $result = Database::query($sql, []);
}

header('Content-Type: application/json');

$messages = [];
if ($result) {
    $hasMore = false;
    if ($newerThan === '') {
        $hasMore = count($result) > $limit;
        if ($hasMore) {
            $result = array_slice($result, 0, $limit);
        }
        $result = array_reverse($result);
    }

    foreach ($result as $row) {
        $messages[] = [
            'datetime' => $row['datetime'],
            'pilot_id' => $row['pilot_id'],
            'text' => $row['text'],
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'avatar' => $row['avatar']];
    }

    echo json_encode([
        'messages' => $messages,
        'hasMore' => $hasMore
    ], JSON_THROW_ON_ERROR);
} else {
    echo json_encode([
        'messages' => [],
        'hasMore' => false
    ], JSON_THROW_ON_ERROR);
}
