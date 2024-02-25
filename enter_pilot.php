<?php
require_once __DIR__ . '/vendor/autoload.php';

\JustinMueller\Flugplanung\Helper::checkLogin();
\JustinMueller\Flugplanung\Database::connect();

$kommentar = $_POST['kommentar'];
$prio_result = $_POST['prio_result'];
$pilotid = $_POST['pilotid'];
$update = $_POST['update'];
$flugtag = $_POST['flugtag'];

if ($update) {
    $sql = "UPDATE tagesplanung SET  Kommentar = '$kommentar', NGL = $prio_result[0], HRP = $prio_result[1], AMD = $prio_result[2], flugtag = '$flugtag'  WHERE Pilot_ID = '$pilotid' and flugtag = '$flugtag'";
} else {
    $sql = "INSERT INTO tagesplanung (Pilot_ID,  Kommentar, NGL, HRP, AMD, flugtag) VALUES ('$pilotid', '$kommentar', $prio_result[0], $prio_result[1], $prio_result[2] , '$flugtag' )";
}

\JustinMueller\Flugplanung\Database::insertSqlStatement($sql);

\JustinMueller\Flugplanung\Database::close();
