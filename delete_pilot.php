<?php
require_once __DIR__ . '/vendor/autoload.php';

\JustinMueller\Flugplanung\Helper::checkLogin();
\JustinMueller\Flugplanung\Database::connect();

$table = "tagesplanung";
$pilotid = $_POST['pilotid_delete'];

$sql = "DELETE FROM $table WHERE Pilot_ID = $pilotid";

if (\JustinMueller\Flugplanung\Database::query($sql) === TRUE) {
    $response = array('success' => true);
    echo json_encode($response);
} else {
    $response = array('error' => 'Error deleting record');
    echo json_encode($response);
}

\JustinMueller\Flugplanung\Database::close();
