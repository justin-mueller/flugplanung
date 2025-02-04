<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$pilot_id = $_POST['pilot_id'];
$datum = $_POST['datum'];
$wunsch = $_POST['wunsch'];

// Check if the entry exists
$sqlCheck = 'SELECT * FROM dienste_wuensche WHERE pilot_id = :pilot_id AND datum = :datum';
$resultCheck = Database::query($sqlCheck, ['pilot_id' => $pilot_id, 'datum' => $datum]);

if ($resultCheck) {
    if ($wunsch === 'Egal') {
        $sql = 'DELETE FROM dienste_wuensche WHERE pilot_id = :pilot_id AND datum = :datum';
        Database::execute($sql, ['pilot_id' => $pilot_id, 'datum' => $datum]);
    } else {
        $wunsch = ($wunsch === 'Ja') ? '1' : '0';
        $sql = 'UPDATE dienste_wuensche SET wunsch = :wunsch WHERE pilot_id = :pilot_id AND datum = :datum';
        Database::execute($sql, ['wunsch' => $wunsch, 'pilot_id' => $pilot_id, 'datum' => $datum]);
    }
} elseif ($wunsch !== 'Egal') {
    $wunsch = ($wunsch === 'Ja') ? '1' : '0';
    $sql = 'INSERT INTO dienste_wuensche (pilot_id, datum, wunsch) VALUES (:pilot_id, :datum, :wunsch)';
    Database::execute($sql, ['wunsch' => $wunsch, 'pilot_id' => $pilot_id, 'datum' => $datum]);
}
