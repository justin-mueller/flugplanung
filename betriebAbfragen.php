<?php
require_once __DIR__ . '/vendor/autoload.php';

\JustinMueller\Flugplanung\Helper::checkLogin();
\JustinMueller\Flugplanung\Database::connect();

$flugtag = $_GET['flugtag'];

$query = "SELECT * FROM moegliche_flugtage WHERE datum = '$flugtag'";
$result = \JustinMueller\Flugplanung\Database::query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $betrieb_ngl = $row['betrieb_ngl'];
    $betrieb_hrp = $row['betrieb_hrp'];
    $betrieb_amd = $row['betrieb_amd'];
    $aufbau = $row['aufbau'];
    $abgesagt = $row['abgesagt'];

    $response = array(
        'betrieb_ngl' => $betrieb_ngl,
        'betrieb_hrp' => $betrieb_hrp,
        'betrieb_amd' => $betrieb_amd,
        'abgesagt' => $abgesagt,
        'aufbau' => $aufbau
    );

    $jsonResponse = json_encode($response);

    header('Content-Type: application/json');
    echo $jsonResponse;
} else {
    echo json_encode(array('error' => 'No entries found in the "mitglieder" table'));
}

\JustinMueller\Flugplanung\Database::close();
