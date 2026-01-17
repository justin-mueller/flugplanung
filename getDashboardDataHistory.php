<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

// Default to 1.1.[current year - 1]
$currentYear = (int)date('Y') - 1;
$defaultStartDate = $currentYear . '-01-01';

$startDate = $_GET['startDate'] ?? $defaultStartDate;

$sql = "SELECT
m.pilot_id,
m.firstname,
m.lastname,

-- Count ALL dienste from startDate onwards
(SELECT COUNT(d.pilot_id)
 FROM dienste d
 WHERE d.pilot_id = m.pilot_id
   AND d.flugtag >= :startDate
) AS duties_count,

-- Count dienste with active betrieb from startDate onwards
(SELECT COUNT(d.pilot_id)
 FROM dienste d
 INNER JOIN flugtage mf ON mf.datum = d.flugtag
 WHERE d.pilot_id = m.pilot_id
   AND d.flugtag >= :startDate
   AND (mf.betrieb_ngl = 1 OR mf.betrieb_hrp = 1 OR mf.betrieb_amd = 1)
) AS active_duties_count,

-- Active Flying Days (pilot flew on days with active betrieb from startDate onwards)
(SELECT COUNT(tp.pilot_id)
 FROM tagesplanung tp
 INNER JOIN flugtage mf ON mf.datum = tp.flugtag
 WHERE tp.pilot_id = m.pilot_id
   AND tp.flugtag >= :startDate
   AND (mf.betrieb_ngl = 1 OR mf.betrieb_hrp = 1 OR mf.betrieb_amd = 1)
) AS active_flying_days

FROM
mitglieder m
WHERE 
    m.verein = :clubId;
";

$params = [
    'startDate' => $startDate,
    'clubId' => Helper::$configuration['clubId'],
];

$result = Database::query($sql, $params);

header('Content-Type: application/json');
echo json_encode($result ?: [], JSON_THROW_ON_ERROR);
