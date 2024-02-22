<?php

require 'db_connect.php';

$table = "tagesplanung";
$pilotid = $_POST['pilotid_delete'];

$sql = "DELETE FROM $table WHERE Pilot_ID = $pilotid";

if ($conn->query($sql) === TRUE) {
    $response = array('success' => true);
    echo json_encode($response);
} else {
    $response = array('error' => 'Error deleting record: ' . $conn->error);
    echo json_encode($response);
}

$conn->close();
