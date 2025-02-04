<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$allPilots = array(['error']);

$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];


$sql = "
SELECT
    mf.datum,
    IFNULL(GROUP_CONCAT(DISTINCT CASE WHEN m.windenfahrer = '0' THEN CONCAT(m.firstname, ' ', m.lastname, IF(dw.pilot_id IS NOT NULL AND dw.pilot_id = m.pilot_id, IF(dw.wunsch = '1', '+', '-'), '')) END), 'Keine SL in Datenbank') AS startleiterOptionen,
    IFNULL(GROUP_CONCAT(DISTINCT CASE WHEN m.windenfahrer = '1' THEN CONCAT(m.firstname, ' ', m.lastname, IF(dw.pilot_id IS NOT NULL AND dw.pilot_id = m.pilot_id, IF(dw.wunsch = '1', '+', '-'), '')) END), 'Keine WF in Datenbank') AS windenfahrerOptionen,
    IFNULL(GROUP_CONCAT(DISTINCT CASE WHEN m.windenfahrer = '0' THEN CONCAT(m.firstname, '_', m.lastname, '_', m.pilot_id, IF(dw.pilot_id IS NOT NULL AND dw.pilot_id = m.pilot_id, IF(dw.wunsch = '1', '+', '-'), '')) END), '') AS startleiterOptionen_ID,
    IFNULL(GROUP_CONCAT(DISTINCT CASE WHEN m.windenfahrer = '1' THEN CONCAT(m.firstname, '_', m.lastname, '_', m.pilot_id, IF(dw.pilot_id IS NOT NULL AND dw.pilot_id = m.pilot_id, IF(dw.wunsch = '1', '+', '-'), '')) END), '') AS windenfahrerOptionen_ID,
    IFNULL(GROUP_CONCAT(DISTINCT CASE WHEN m.windenfahrer = '0' THEN CONCAT(m.firstname, ' ', m.lastname, IF(dw.pilot_id IS NOT NULL AND dw.pilot_id = m.pilot_id, IF(dw.wunsch = '1', '+', '-'), '')) END), 'Keine SL in Datenbank') AS startleiter,
    IFNULL(GROUP_CONCAT(DISTINCT CASE WHEN m.windenfahrer = '1' THEN CONCAT(m.firstname, ' ', m.lastname, IF(dw.pilot_id IS NOT NULL AND dw.pilot_id = m.pilot_id, IF(dw.wunsch = '1', '+', '-'), '')) END), 'Keine WF in Datenbank') AS windenfahrer,
    IFNULL(GROUP_CONCAT(DISTINCT CASE WHEN d.windenfahrer = '0' THEN d.pilot_id END), 'Kein SL') AS startleiter,
    IFNULL(GROUP_CONCAT(DISTINCT CASE WHEN d.windenfahrer = '1' THEN d.pilot_id END), 'Kein WF') AS windenfahrer
FROM
    moegliche_flugtage mf
LEFT JOIN
    dienste_wuensche dw ON mf.datum = dw.datum
LEFT JOIN
    mitglieder m ON 1 = 1
LEFT JOIN
    dienste d ON mf.datum = d.flugtag AND m.pilot_id = d.pilot_id
WHERE
    (mf.datum BETWEEN :startDate AND :endDate)
    AND
    (m.verein = 198)
GROUP BY
    mf.datum;
";

//hardcoded HDGF, as I failed to parse the $clubId to the query

$result = Database::query($sql, [
    'startDate' => $startDate,
    'endDate' => $endDate
    //,'clubId' => $clubId
]);

$data = array();

if ($result !== false && $result !== []) {
    foreach ($result as $row) {
        $startleiterOptions = $row['startleiterOptionen'] !== null ? explode(',', $row['startleiterOptionen']) : $allPilots;
        $windenfahrerOptions = $row['windenfahrerOptionen'] !== null ? explode(',', $row['windenfahrerOptionen']) : $allPilots;

        $startleiterOptionsWithId = array();
        $startleiterIds = $row['startleiterOptionen_ID'] !== null ? explode(',', $row['startleiterOptionen_ID']) : array();

        foreach ($startleiterOptions as $index => $pilot) {
            $id = isset($startleiterIds[$index]) && $startleiterIds[$index] !== 'null' ? $startleiterIds[$index] : null;
            $startleiterOptionsWithId[] = array(
                'name' => $pilot,
                'id' => $id,
            );
        }

        $windenfahrerOptionsWithId = array();
        $windenfahrerIds = $row['windenfahrerOptionen_ID'] !== null ? explode(',', $row['windenfahrerOptionen_ID']) : array();

        foreach ($windenfahrerOptions as $index => $pilot) {
            $id = isset($windenfahrerIds[$index]) && $windenfahrerIds[$index] !== 'null' ? $windenfahrerIds[$index] : null;
            $windenfahrerOptionsWithId[] = array(
                'name' => $pilot,
                'id' => $id,
            );
        }

        $entry = array(
            'date' => $row['datum'],
            'startleiterOptionen' => $startleiterOptionsWithId,
            'windenfahrerOptionen' => $windenfahrerOptionsWithId,
            'startleiter' => $row['startleiter'],
            'windenfahrer' => $row['windenfahrer'],
        );
        $data[] = $entry;
    }
}

echo json_encode($data);
