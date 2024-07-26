<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$startDate = date('Y-m-d', strtotime('-1 week'));

$sql = 'SELECT *
        FROM chatbox cb
        LEFT JOIN mitglieder m ON m.pilot_id = cb.pilot_id
        WHERE cb.datetime >= :startDate';

$result = Database::query($sql, ['startDate' => $startDate]);

header('Content-Type: application/json');

$values = [];
if ($result) {
    foreach ($result as $row) {
        $values[] = [
            'datetime' => $row['datetime'],
            'pilot_id' => $row['pilot_id'],
            'text' => $row['text'],
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'avatar' => $row['avatar']];
    }
    echo json_encode($values);
} else {
    echo json_encode(['error' => 'Keine Einträge in der Chatbox!']);
}
