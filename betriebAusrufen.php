<?php

require 'db_connect.php';
require 'insertSqlStatement.php';

$table = "moegliche_flugtage";
$flugtag = $_POST['flugtag'];
$betrieb_ngl = $_POST['flugbetrieb_ngl'];
$betrieb_hrp = $_POST['flugbetrieb_hrp'];
$betrieb_amd = $_POST['flugbetrieb_amd'];
$aufbau = $_POST['aufbau'];

$sql = "UPDATE $table SET `betrieb_ngl` = '$betrieb_ngl', `betrieb_hrp` = '$betrieb_hrp', `betrieb_amd` = '$betrieb_amd', `aufbau` = '$aufbau' WHERE  `datum` = '$flugtag'";

insertSqlStatement($conn, $sql);

$conn->close();
