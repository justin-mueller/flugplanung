<?php
require_once __DIR__ . '/vendor/autoload.php';

\JustinMueller\Flugplanung\Helper::checkLogin();
\JustinMueller\Flugplanung\Database::connect();

$datum = $_POST['datum'];

$sql = "INSERT INTO moegliche_flugtage (datum) VALUES ('$datum')";
$result = \JustinMueller\Flugplanung\Database::insertSqlStatement($sql);

\JustinMueller\Flugplanung\Database::close();

echo json_encode($result);