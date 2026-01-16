<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$allPilots = [['error']];

// Check if startDate and endDate are provided, fallback to previous year
$currentYear = (int)date('Y') - 1;
$defaultStartDate = $currentYear . '-01-01';
$defaultEndDate = $currentYear . '-12-31';

$startDate = $_GET['startDate'] ?? $defaultStartDate;
$endDate = $_GET['endDate'] ?? $defaultEndDate;

$sql = "SELECT
m.pilot_id,
m.firstname,
m.lastname,

-- Historical Counts (before startDate)
-- Count dienste with active betrieb (uses INNER JOIN since betrieb check requires flugtage)
(SELECT COUNT(d.pilot_id)
 FROM dienste d
 INNER JOIN flugtage mf ON mf.datum = d.flugtag
 WHERE d.pilot_id = m.pilot_id
   AND d.flugtag < :startDate
   AND (mf.betrieb_ngl = 1 OR mf.betrieb_hrp = 1 OR mf.betrieb_amd = 1)
) AS active_duties_count_history,

-- Count ALL dienste before startDate (no join needed - use dienste.flugtag directly)
(SELECT COUNT(d.pilot_id)
 FROM dienste d
 WHERE d.pilot_id = m.pilot_id
   AND d.flugtag < :startDate
) AS duties_count_history,

-- Current Range Counts (between startDate and endDate)
-- Count dienste with active betrieb in the selected range
(SELECT COUNT(d.pilot_id)
 FROM dienste d
 INNER JOIN flugtage mf ON mf.datum = d.flugtag
 WHERE d.pilot_id = m.pilot_id
   AND d.flugtag BETWEEN :startDate AND :endDate
   AND (mf.betrieb_ngl = 1 OR mf.betrieb_hrp = 1 OR mf.betrieb_amd = 1)
) AS active_duties_count_thisyear,

-- Count ALL dienste in the selected range (no join needed)
(SELECT COUNT(d.pilot_id)
 FROM dienste d
 WHERE d.pilot_id = m.pilot_id
   AND d.flugtag BETWEEN :startDate AND :endDate
) AS duties_count_thisyear,

-- Active Flying Days History (pilot flew on days with active betrieb before startDate)
(SELECT COUNT(tp.pilot_id)
 FROM tagesplanung tp
 INNER JOIN flugtage mf ON mf.datum = tp.flugtag
 WHERE tp.pilot_id = m.pilot_id
   AND tp.flugtag < :startDate
   AND (mf.betrieb_ngl = 1 OR mf.betrieb_hrp = 1 OR mf.betrieb_amd = 1)
) AS active_flying_days_history

FROM
mitglieder m
WHERE 
    m.verein = :clubId;
";

$params = [
    'startDate' => $startDate,
    'endDate' => $endDate,
    'clubId' => Helper::$configuration['clubId'],
];

$result = Database::query($sql, $params);

header('Content-Type: application/json');
echo json_encode($result ?: [], JSON_THROW_ON_ERROR);
