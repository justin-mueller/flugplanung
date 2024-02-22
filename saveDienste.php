<?php

require 'db_connect.php';
require 'insertSqlStatement.php';

$flugtag = $_POST['flugtag'];
$pilot_id = $_POST['pilot_id'];
$windenfahrer = $_POST['windenfahrer'];
$startleiter = $_POST['startleiter'];

$sql = "INSERT INTO dienste (id, flugtag, pilot_id, windenfahrer, startleiter) 
			  VALUES (CONCAT('$flugtag', '_' ,'$pilot_id') , '$flugtag', '$pilot_id', '$windenfahrer', '$startleiter')";

insertSqlStatement($conn, $sql);

$conn->close();
