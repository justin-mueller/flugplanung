<?php
require_once __DIR__ . '/vendor/autoload.php';

\JustinMueller\Flugplanung\Helper::checkLogin();
\JustinMueller\Flugplanung\Database::connect();

$year = $_GET['year'];

$sqlDelete = "DELETE FROM dienste WHERE YEAR(flugtag) = '$year'";

if (\JustinMueller\Flugplanung\Database::query($sqlDelete) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

\JustinMueller\Flugplanung\Database::close();
