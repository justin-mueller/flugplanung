<?php

require 'db_connect.php';

$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];

$query = "SELECT datum FROM moegliche_flugtage WHERE datum BETWEEN '$startDate' AND '$endDate' ORDER BY datum DESC";

$result = $conn->query($query);

$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
