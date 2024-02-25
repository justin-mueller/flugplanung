<?php
require_once __DIR__ . '/vendor/autoload.php';

\JustinMueller\Flugplanung\Helper::checkLogin();
\JustinMueller\Flugplanung\Database::connect();

$datum = $_POST['datum'];

$sql = "DELETE FROM moegliche_flugtage WHERE datum = '$datum'";
\JustinMueller\Flugplanung\Database::insertSqlStatement($sql);

\JustinMueller\Flugplanung\Database::close();
