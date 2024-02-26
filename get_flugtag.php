<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::checkLogin();
Database::connect();
require 'clubs.php';

$sql = "SELECT
            m.pilot_id AS Pilot_ID,
            CONCAT(m.firstname, ' ', m.lastname) AS Pilot,
            m.windenfahrer AS ist_windenfahrer,
            m.verein AS VereinId,
            2 AS NGL,
            2 AS HRP,
            2 AS AMD,
            '' AS Kommentar,
            '' AS timestamp,
            d.windenfahrer as windenfahrer_official,
            d.startleiter as startleiter_official
        FROM dienste d
        INNER JOIN mitglieder m ON d.pilot_id = m.pilot_id
        WHERE d.flugtag = :flugtag AND (d.startleiter = '1' OR d.windenfahrer = '1')
        
        UNION
         
        SELECT
            m.pilot_id AS Pilot_ID,
            CONCAT(m.firstname, ' ', m.lastname) AS Pilot,
            m.windenfahrer AS ist_windenfahrer,
            m.verein AS VereinId,
            NGL,
            HRP,
            AMD,
            Kommentar,
            timestamp,
            '' as windenfahrer_official,
            '' as startleiter_official
        FROM tagesplanung t
        INNER JOIN mitglieder m ON t.pilot_id = m.pilot_id
        WHERE flugtag = :flugtag";


$result = Database::query($sql, ['flugtag' => $_GET['flugtag']]);
if ($result) {
    $data = [];
    foreach ($result as $row) {
     $row['VereinId'] = (int)$row['VereinId'];
     $row['Verein'] = $clubs[$row['VereinId']];
     $data[] = $row;
    }
} else {
    $data = 'Keine Daten vorhanden.';
}

header('Content-Type: application/json');
try {
    echo json_encode($data, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    $data = 'JSON encoding failed: ' . $e->getMessage();
}
