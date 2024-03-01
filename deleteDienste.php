<?php
require 'check_login.php';

require 'db_connect.php';

$year = $_GET['year'];

$sqlDelete = "DELETE FROM dienste WHERE YEAR(flugtag) = '$year'";

if ($conn->query($sqlDelete) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
