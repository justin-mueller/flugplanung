<?php
require 'check_login.php';

require 'db_connect.php';
require 'insertSqlStatement.php';

$table = "tagesplanung";
$kommentar = $_POST['kommentar'];
$prio_result = $_POST['prio_result'];
$pilotid = $_POST['pilotid'];
$update = $_POST['update'];
$flugtag = $_POST['flugtag'];


if ($update) {
	$sql = "UPDATE $table SET  Kommentar = '$kommentar', NGL = $prio_result[0], HRP = $prio_result[1], AMD = $prio_result[2], flugtag = '$flugtag'  WHERE Pilot_ID = '$pilotid' and flugtag = '$flugtag'";
} else {
	$sql = "INSERT INTO $table (Pilot_ID,  Kommentar, NGL, HRP, AMD, flugtag) VALUES ('$pilotid', '$kommentar', $prio_result[0], $prio_result[1], $prio_result[2] , '$flugtag' )";
}

insertSqlStatement($conn, $sql);

$conn->close();
