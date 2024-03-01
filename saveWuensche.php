<?php
require 'check_login.php';

require 'db_connect.php';
require 'insertSqlStatement.php';

$pilot_id = $_POST['pilot_id'];
$datum = $_POST['datum'];
$wunsch = $_POST['wunsch'];

// Check if the entry exists
$sqlCheck = "SELECT * FROM dienste_wuensche WHERE pilot_id = $pilot_id AND datum = '$datum'";

$resultCheck = $conn->query($sqlCheck);

if ($resultCheck->num_rows > 0) {
	if ($wunsch === 'Egal') {
		$sql = "DELETE FROM dienste_wuensche WHERE pilot_id = $pilot_id AND datum = '$datum'";
		insertSqlStatement($conn, $sql);
	} else {
		$wunsch = ($wunsch === 'Ja') ? '1' : '0';
		$sql = "UPDATE dienste_wuensche SET wunsch = '$wunsch' WHERE pilot_id = $pilot_id AND datum = '$datum'";
		insertSqlStatement($conn, $sql);
	}
} else {
	if ($wunsch !== 'Egal') {
		$wunsch = ($wunsch === 'Ja') ? '1' : '0';
		$sql = "INSERT INTO dienste_wuensche (pilot_id, datum, wunsch) VALUES ($pilot_id, '$datum', '$wunsch')";
		insertSqlStatement($conn, $sql);
	}
}

$conn->close();