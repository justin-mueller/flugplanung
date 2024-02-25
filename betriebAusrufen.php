<?php
require_once __DIR__ . '/vendor/autoload.php';

\JustinMueller\Flugplanung\Helper::checkLogin();
\JustinMueller\Flugplanung\Database::connect();

$flugtag = $_POST['flugtag'];
$betrieb_ngl = $_POST['flugbetrieb_ngl'];
$betrieb_hrp = $_POST['flugbetrieb_hrp'];
$betrieb_amd = $_POST['flugbetrieb_amd'];
$abgesagt = $_POST['abgesagt'];
$aufbau = $_POST['aufbau'];

$sql = "UPDATE moegliche_flugtage SET `betrieb_ngl` = '$betrieb_ngl', `betrieb_hrp` = '$betrieb_hrp', `betrieb_amd` = '$betrieb_amd', `abgesagt` = '$abgesagt', `aufbau` = '$aufbau' WHERE  `datum` = '$flugtag'";
\JustinMueller\Flugplanung\Database::insertSqlStatement($sql);

\JustinMueller\Flugplanung\Database::close();
