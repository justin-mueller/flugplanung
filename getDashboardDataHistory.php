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
	m.pilot_id,
    m.firstname,
    m.lastname,
    -- Historical Counts
    COUNT(CASE 
        WHEN mf.datum < DATE_FORMAT(CURDATE(), '%Y-01-01') 
             AND (mf.betrieb_ngl = 1 OR mf.betrieb_hrp = 1 OR mf.betrieb_amd = 1) 
        THEN d.pilot_id 
        END) AS active_duties_count_history,
    COUNT(CASE 
        WHEN mf.datum < DATE_FORMAT(CURDATE(), '%Y-01-01') 
        THEN d.pilot_id 
        END) AS duties_count_history,
    -- Current Year Counts
    COUNT(CASE 
        WHEN mf.datum >= DATE_FORMAT(CURDATE(), '%Y-01-01') 
             AND (mf.betrieb_ngl = 1 OR mf.betrieb_hrp = 1 OR mf.betrieb_amd = 1) 
        THEN d.pilot_id 
        END) AS active_duties_count_thisyear,
    COUNT(CASE 
        WHEN mf.datum >= DATE_FORMAT(CURDATE(), '%Y-01-01') 
        THEN d.pilot_id 
        END) AS duties_count_thisyear
FROM 
    mitglieder m
LEFT JOIN 
    dienste d ON m.pilot_id = d.pilot_id
LEFT JOIN 
    moegliche_flugtage mf ON mf.datum = d.flugtag
WHERE 
    m.verein = 198
GROUP BY 
	m.pilot_id,
    m.firstname,
    m.lastname;

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
