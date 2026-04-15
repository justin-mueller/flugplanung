<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sql = "SELECT
            m.pilot_id AS Pilot_ID,
            CONCAT(m.firstname, ' ', m.lastname) AS Pilot,
            m.windenfahrer AS ist_windenfahrer,
            m.fluggeraet,
            m.verein AS VereinId,
            NULL AS site_priorities,
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
            m.fluggeraet,
            m.verein AS VereinId,
            (SELECT GROUP_CONCAT(CONCAT(ts.site_index, ':', ts.priority) ORDER BY ts.site_index)
             FROM tagesplanung_sites ts
             WHERE ts.pilot_id = t.pilot_id AND ts.flugtag = t.flugtag) AS site_priorities,
            Kommentar,
            timestamp,
            '' as windenfahrer_official,
            '' as startleiter_official
        FROM tagesplanung t
        INNER JOIN mitglieder m ON t.pilot_id = m.pilot_id
        WHERE t.flugtag = :flugtag
        ORDER BY timestamp ASC";


$result = Database::query($sql, ['flugtag' => $_GET['flugtag']]);
if ($result !== false && !empty($result)) {
    $data = [];
    foreach ($result as $row) {
        $row['VereinId'] = (int)$row['VereinId'];
        $row['Verein'] = Helper::$configuration['clubs'][$row['VereinId']]['shortName'] ?: Helper::$configuration['clubs'][$row['VereinId']]['name'];

        // Parse site_priorities into an indexed array, default all to 2 (not chosen)
        $sites = array_fill(0, Helper::getSiteCount(), 2);
        if (!empty($row['site_priorities'])) {
            foreach (explode(',', $row['site_priorities']) as $pair) {
                [$index, $priority] = explode(':', $pair);
                $sites[(int)$index] = (int)$priority;
            }
        }
        $row['sites'] = $sites;
        unset($row['site_priorities']);

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
