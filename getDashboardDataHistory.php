<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$allPilots = array(['error']);

// Check if startDate and endDate are provided
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;

$sql = "


SELECT
    EXTRACT(YEAR FROM d.flugtag) AS year,
    m.firstname, 
    m.lastname,
    COUNT(CASE WHEN (mf.betrieb_ngl = 1 OR mf.betrieb_hrp = 1 OR mf.betrieb_amd = 1) THEN d.pilot_id END) AS active_duties_count,
    COUNT(CASE WHEN (mf.betrieb_ngl = 0 AND mf.betrieb_hrp = 0 AND mf.betrieb_amd = 0) THEN d.pilot_id END) AS no_duties_count
FROM 
    moegliche_flugtage mf
LEFT JOIN 
    dienste d ON mf.datum = d.flugtag
LEFT JOIN 
    mitglieder m ON m.pilot_id = d.pilot_id
WHERE 
    m.verein = 198
    AND mf.datum IS NOT NULL
    AND d.flugtag IS NOT NULL
    AND (d.windenfahrer = 1 OR d.startleiter = 1)
GROUP BY 
    EXTRACT(YEAR FROM d.flugtag),
    m.firstname,
    m.lastname,
    m.pilot_id;


";

if ($startDate && $endDate) {
    $sql .= " AND (mf.datum BETWEEN :startDate AND :endDate)";
    $params = [
        'startDate' => $startDate,
        'endDate' => $endDate,
    ];
} else {
    $params = []; // No parameters needed if no date filter is applied
}

$sql .= " GROUP BY mf.datum;";

$result = Database::query($sql, $params);

$data = array();

if ($result !== []) {
    foreach ($result as $row) {
        $data[] = $row; // Simply add the entire row to the $data array
    }
}

echo json_encode($data);
