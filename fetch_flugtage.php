<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];

$query = 'SELECT datum FROM flugtage WHERE datum BETWEEN :startDate AND :endDate ORDER BY datum DESC';

$result = Database::query($query, ['startDate' => $startDate, 'endDate' => $endDate]);

header('Content-Type: application/json');
echo json_encode($result ?: [], JSON_THROW_ON_ERROR);
