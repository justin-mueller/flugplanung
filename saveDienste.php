<?php
require_once __DIR__ . '/vendor/autoload.php';

\JustinMueller\Flugplanung\Helper::checkLogin();
\JustinMueller\Flugplanung\Database::connect();

$flugtag = $_POST['flugtag'];
$pilot_id = $_POST['pilot_id'];
$windenfahrer = $_POST['windenfahrer'];
$startleiter = $_POST['startleiter'];

$sql = "INSERT INTO dienste (id, flugtag, pilot_id, windenfahrer, startleiter) 
			  VALUES (CONCAT('$flugtag', '_' ,'$pilot_id') , '$flugtag', '$pilot_id', '$windenfahrer', '$startleiter')";

\JustinMueller\Flugplanung\Database::insertSqlStatement($sql);

\JustinMueller\Flugplanung\Database::close();
