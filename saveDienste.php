<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

try {
    //Database::beginTransaction();

    // Delete existing empty row(s) for this flugtag before inserting
    $sqlDelete = "DELETE FROM dienste 
    WHERE flugtag = :flugtag 
    AND windenfahrer = :windenfahrer 
    AND startleiter = :startleiter";

    Database::execute($sqlDelete, [
    'flugtag'      => $_POST['flugtag'],
    'windenfahrer' => $_POST['windenfahrer'],
    'startleiter'  => $_POST['startleiter'],
    ]);

    // Insert the new record

    
    $sqlInsert = "INSERT INTO dienste (id, flugtag, pilot_id, windenfahrer, startleiter) 
                  VALUES (CONCAT(:flugtag, '_' ,:pilot_id), :flugtag, :pilot_id, :windenfahrer, :startleiter)";

    Database::execute($sqlInsert, [
        'flugtag' => $_POST['flugtag'],
        'pilot_id' => $_POST['pilot_id'],
        'windenfahrer' => $_POST['windenfahrer'],
        'startleiter' => $_POST['startleiter'],
    ]);

    //Database::commit();

    echo json_encode(['success' => true], JSON_THROW_ON_ERROR);

} catch (Exception $e) {
    Database::rollback();
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
    ], JSON_THROW_ON_ERROR);
}
