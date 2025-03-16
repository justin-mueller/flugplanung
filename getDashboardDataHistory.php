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

$sql = "SELECT
m.pilot_id,
m.firstname,
m.lastname,
-- Historical Counts
(SELECT COUNT(d.pilot_id)
 FROM dienste d
 LEFT JOIN flugtage mf ON mf.datum = d.flugtag
 WHERE d.pilot_id = m.pilot_id
   AND mf.datum < DATE_FORMAT(CURDATE(), '%Y-01-01')
   AND (mf.betrieb_ngl = 1 OR mf.betrieb_hrp = 1 OR mf.betrieb_amd = 1)
) AS active_duties_count_history,

(SELECT COUNT( d.pilot_id)
 FROM dienste d
 LEFT JOIN flugtage mf ON mf.datum = d.flugtag
 WHERE d.pilot_id = m.pilot_id
   AND mf.datum < DATE_FORMAT(CURDATE(), '%Y-01-01')
) AS duties_count_history,

-- Current Year Counts
(SELECT COUNT( d.pilot_id)
 FROM dienste d
 LEFT JOIN flugtage mf ON mf.datum = d.flugtag
 WHERE d.pilot_id = m.pilot_id
   AND mf.datum >= DATE_FORMAT(CURDATE(), '%Y-01-01')
   AND (mf.betrieb_ngl = 1 OR mf.betrieb_hrp = 1 OR mf.betrieb_amd = 1)
) AS active_duties_count_thisyear,

(SELECT COUNT( d.pilot_id)
 FROM dienste d
 LEFT JOIN flugtage mf ON mf.datum = d.flugtag
 WHERE d.pilot_id = m.pilot_id
   AND mf.datum >= DATE_FORMAT(CURDATE(), '%Y-01-01')
) AS duties_count_thisyear,

-- Active Flying Days History
-- Active Flying Days History
(SELECT COUNT(tp.pilot_id)
FROM tagesplanung tp
LEFT JOIN flugtage mf ON mf.datum = tp.flugtag
WHERE tp.pilot_id = m.pilot_id
AND tp.flugtag < DATE_FORMAT(CURDATE(), '%Y-01-01')
AND (mf.betrieb_ngl = 1 OR mf.betrieb_hrp = 1 OR mf.betrieb_amd = 1)
AND (tp.flugtag = mf.datum OR tp.flugtag IS NULL)
) AS active_flying_days_history


FROM
mitglieder m
WHERE 
    m.verein = :clubId;
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
