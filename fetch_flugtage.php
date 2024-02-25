<?php
require_once __DIR__ . '/vendor/autoload.php';

\JustinMueller\Flugplanung\Helper::checkLogin();
\JustinMueller\Flugplanung\Database::connect();

$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];

$query = "SELECT datum FROM moegliche_flugtage WHERE datum BETWEEN '$startDate' AND '$endDate' ORDER BY datum DESC";

$result = \JustinMueller\Flugplanung\Database::query($query);

$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
\JustinMueller\Flugplanung\Database::close();
