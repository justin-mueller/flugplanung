<?php

require 'db_connect.php';

$table = "tagesplanung";
$flugtag = $_GET['flugtag'];

$query = "SELECT * FROM mitglieder WHERE pilot_id = '$active_user_pilot_id'";
$result = $conn->query($query);

if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();

    $pilotName = $row['firstname'] . ' ' . $row['lastname'];
    $pilotId = $row['pilot_id'];
    $verein = $row['verein'];
    $ist_windenfahrer = $row['windenfahrer'];

    $recordQuery = "SELECT COUNT(*) AS count FROM tagesplanung WHERE pilot_id = '$pilotId' and flugtag = '$flugtag'";
    $recordResult = $conn->query($recordQuery);
    $recordCount = $recordResult->fetch_assoc()['count'];
    $recordPresent = ($recordCount > 0) ? true : false;


    $response = array(
        'pilotName' => $pilotName,
        'pilotId' => $pilotId,
        'verein' => $verein,
        'ist_windenfahrer' => $ist_windenfahrer,
        'record_present' => $recordPresent
    );

    $jsonResponse = json_encode($response);

    header('Content-Type: application/json');
    echo $jsonResponse;
} else {
    echo json_encode(array('error' => 'No entries found in the "mitglieder" table'));
}

$conn->close();