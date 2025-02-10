<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$allPilots = [['error']];

// Check if startDate and endDate are provided
$startDate = $_GET['startDate'] ?? null;
$endDate = $_GET['endDate'] ?? null;

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
    flugtage mf ON mf.datum = d.flugtag
WHERE 
    m.verein = :clubId
GROUP BY 
    m.pilot_id,
    m.firstname,
    m.lastname;

";

if ($startDate && $endDate) {
    $sql .= ' AND (mf.datum BETWEEN :startDate AND :endDate)';
    $params = [
        'startDate' => $startDate,
        'endDate' => $endDate,
        'clubId' => Helper::$configuration['clubId'],
    ];
} else {
    $params = []; // No parameters needed if no date filter is applied
}

$sql .= ' GROUP BY mf.datum;';

$result = Database::query($sql, $params);

header('Content-Type: application/json');
echo json_encode($result ?: [], JSON_THROW_ON_ERROR);
