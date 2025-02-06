<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sql = 'SELECT 
            mf.datum, 
            COALESCE(dw.wunsch, -1) AS wunsch
        FROM flugtage mf
        LEFT JOIN dienste_wuensche dw ON mf.datum = dw.datum AND dw.pilot_id = :pilot_id
            WHERE 
            mf.datum BETWEEN :startDate AND :endDate';

$result = Database::query($sql, ['pilot_id' => $_GET['pilot_id'], 'startDate' => $_GET['startDate'], 'endDate' => $_GET['endDate']]);

$values = [];
if ($result) {
    foreach ($result as $row) {
        $wunschValue = match ($row['wunsch']) {
            0 => 'Nein',
            1 => 'Ja',
            default => 'Egal'
        };
        $values[] = ['date' => $row['datum'], 'wunsch' => $wunschValue];
    }
}

header('Content-Type: application/json');
echo json_encode($values, JSON_THROW_ON_ERROR);
