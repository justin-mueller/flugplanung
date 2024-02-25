<?php
require_once __DIR__ . '/vendor/autoload.php';

\JustinMueller\Flugplanung\Helper::checkLogin();
\JustinMueller\Flugplanung\Database::connect();
require 'clubs.php';

$flugtag = $_GET['flugtag'];

$sql = "
        SELECT
            m.pilot_id AS Pilot_ID,
            CONCAT(m.firstname, ' ', m.lastname) AS Pilot,
            m.windenfahrer AS ist_windenfahrer,
            m.verein AS VereinId,
            2 AS NGL,
            2 AS HRP,
            2 AS AMD,
            '' AS Kommentar,
            '' AS timestamp,
				d.windenfahrer as windenfahrer_official,
				d.startleiter as startleiter_official
        FROM dienste d
        INNER JOIN mitglieder m ON d.pilot_id = m.pilot_id
        WHERE d.flugtag = '$flugtag' AND (d.startleiter = '1' OR d.windenfahrer = '1')
		
		UNION
		 
		SELECT
            m.pilot_id AS Pilot_ID,
            CONCAT(m.firstname, ' ', m.lastname) AS Pilot,
            m.windenfahrer AS ist_windenfahrer,
            m.verein AS VereinId,
            NGL,
            HRP,
            AMD,
            Kommentar,
            timestamp,
				'' as windenfahrer_official,
				'' as startleiter_official
				
        FROM tagesplanung t
		  INNER JOIN mitglieder m ON t.pilot_id = m.pilot_id
        WHERE flugtag = '$flugtag'";



$result = \JustinMueller\Flugplanung\Database::query($sql);

$data = array();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
     $row['VereinId'] = (int)$row['VereinId'];
     $row['Verein'] = $clubs[$row['VereinId']];
     $data[] = $row;
  }
}

if (empty($data)) {
  echo "Keine Daten vorhanden.";
} else {
  $jsonEncodedData = json_encode($data);
  if ($jsonEncodedData === false) {
    echo "JSON encoding failed: " . json_last_error_msg();
  } else {
    header('Content-Type: application/json');
    echo $jsonEncodedData;
  }
}

\JustinMueller\Flugplanung\Database::close();
