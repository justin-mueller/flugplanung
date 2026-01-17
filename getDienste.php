<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

if (!isset($_GET['startDate']) || !isset($_GET['endDate'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing startDate or endDate parameter'], JSON_THROW_ON_ERROR);
    exit;
}

$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];
$clubId = Helper::$configuration['clubId'];

// Query dienste for the date range, using the dienste table fields to determine role
$sql = "
SELECT
    d.flugtag,
    GROUP_CONCAT(DISTINCT CASE 
        WHEN d.windenfahrer = 1 THEN CONCAT(m.firstname, ' ', m.lastname) 
        ELSE NULL 
    END SEPARATOR ', ') AS Windenfahrer,
    GROUP_CONCAT(DISTINCT CASE 
        WHEN d.startleiter = 1 THEN CONCAT(m.firstname, ' ', m.lastname) 
        ELSE NULL 
    END SEPARATOR ', ') AS Startleiter,
    GROUP_CONCAT(DISTINCT CASE 
        WHEN d.windenfahrer = 1 THEN CAST(m.pilot_id AS CHAR)
        ELSE NULL
    END SEPARATOR ',') AS Windenfahrer_ids,
    GROUP_CONCAT(DISTINCT CASE 
        WHEN d.startleiter = 1 THEN CAST(m.pilot_id AS CHAR)
        ELSE NULL
    END SEPARATOR ',') AS Startleiter_ids
FROM 
    dienste d
INNER JOIN 
    mitglieder m ON d.pilot_id = m.pilot_id
WHERE 
    m.verein = :clubId
    AND d.flugtag >= :startDate
    AND d.flugtag <= :endDate
GROUP BY 
    d.flugtag
ORDER BY 
    d.flugtag;
";

$params = [
    'startDate' => $startDate,
    'endDate' => $endDate,
    'clubId' => $clubId,
];

$result = Database::query($sql, $params);

header('Content-Type: application/json');
echo json_encode($result ?: [], JSON_THROW_ON_ERROR);
