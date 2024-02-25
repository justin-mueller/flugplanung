<?php
require_once __DIR__ . '/vendor/autoload.php';

\JustinMueller\Flugplanung\Helper::checkLogin();
\JustinMueller\Flugplanung\Database::connect();

$pilot_id = $_GET['pilot_id'];
$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];


$sql = "SELECT 
            mf.datum, 
            COALESCE(dw.wunsch, -1) AS wunsch
        FROM moegliche_flugtage mf
        LEFT JOIN dienste_wuensche dw ON mf.datum = dw.datum AND dw.pilot_id = $pilot_id
		          WHERE 
		mf.datum BETWEEN '$startDate' AND '$endDate'";

$result = \JustinMueller\Flugplanung\Database::query($sql);

$values = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $wunschValue = ($row['wunsch'] == 1) ? 'Ja' : (($row['wunsch'] == 0) ? 'Nein' : 'Egal');
        $values[] = array('date' => $row['datum'], 'wunsch' => $wunschValue);
    }
}

echo json_encode($values);

\JustinMueller\Flugplanung\Database::close();
