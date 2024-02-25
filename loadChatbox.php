<?php
require_once __DIR__ . '/vendor/autoload.php';

\JustinMueller\Flugplanung\Helper::checkLogin();
\JustinMueller\Flugplanung\Database::connect();

$startDate = date('Y-m-d', strtotime('-1 week'));

$sql = "SELECT *
        FROM chatbox cb
        LEFT JOIN mitglieder m ON m.pilot_id = cb.pilot_id
        WHERE cb.datetime >= '$startDate'";

$result = \JustinMueller\Flugplanung\Database::query($sql);

$values = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $values[] = [
            'datetime' => $row['datetime'],
            'pilot_id' => $row['pilot_id'],
            'text' => $row['text'],
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'avatar' => $row['avatar']];
    }
    echo json_encode($values);
} else {
    echo json_encode(['error' => 'Keine Einträge in der Chatbox!']);
}

\JustinMueller\Flugplanung\Database::close();
