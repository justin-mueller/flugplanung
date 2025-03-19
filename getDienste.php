<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

if (!isset($_GET['year']) || !filter_var($_GET['year'], FILTER_VALIDATE_INT)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid year parameter'], JSON_THROW_ON_ERROR);
    exit;
}


$allPilots = [['error']];

// Check if startDate and endDate are provided
$startDate = $_GET['startDate'] ?? null;
$endDate = $_GET['endDate'] ?? null;
$year = $_GET['year'] ?? null;
$sql = "

SELECT
    d.flugtag,
    NULLIF(GROUP_CONCAT(CASE 
        WHEN m.windenfahrer = 1 THEN CONCAT(m.firstname, ' ', m.lastname) 
        ELSE NULL 
    END SEPARATOR ', '), '') AS Windenfahrer,
    NULLIF(GROUP_CONCAT(CASE 
        WHEN m.windenfahrer = 0 THEN CONCAT(m.firstname, ' ',  m.lastname) 
        ELSE NULL 
    END SEPARATOR ', '), '') AS Startleiter,
    NULLIF(GROUP_CONCAT(CASE 
        WHEN m.windenfahrer = 1 THEN m.pilot_id
        ELSE NULL
    END SEPARATOR ', '), '') AS Windenfahrer_ids,
    NULLIF(GROUP_CONCAT(CASE 
        WHEN m.windenfahrer = 0 THEN m.pilot_id
        ELSE NULL
    END SEPARATOR ', '), '') AS Startleiter_ids
FROM 
    mitglieder m
LEFT JOIN 
    dienste d ON m.pilot_id = d.pilot_id
WHERE 
    m.verein = :clubId
    AND YEAR(d.flugtag) = :year
GROUP BY 
    d.flugtag
ORDER BY 
    d.flugtag;


";

if ($startDate && $endDate) {
    $sql .= ' AND (mf.datum BETWEEN :startDate AND :endDate)';
    $params = [
        'year' => $_GET['year'],
        'startDate' => $startDate,
        'endDate' => $endDate,
        'clubId' => Helper::$configuration['clubId'],
    ];
} else {
    $params = []; // No parameters needed if no date filter is applied
}

$result = Database::query($sql, $params);

header('Content-Type: application/json');
echo json_encode($result ?: [], JSON_THROW_ON_ERROR);
