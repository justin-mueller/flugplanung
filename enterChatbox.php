<?php
require_once __DIR__ . '/vendor/autoload.php';

\JustinMueller\Flugplanung\Helper::checkLogin();
\JustinMueller\Flugplanung\Database::connect();

$pilot_id = $_POST['pilot_id'];
$text = $_POST['text'];

$sql = "INSERT INTO chatbox  (Pilot_ID, text) VALUES ('$pilot_id', '$text')";

\JustinMueller\Flugplanung\Database::insertSqlStatement($sql);
\JustinMueller\Flugplanung\Database::close();
