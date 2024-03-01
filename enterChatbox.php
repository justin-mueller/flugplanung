<?php

require 'db_connect.php';
require 'insertSqlStatement.php';

$table = "chatbox";
$pilot_id = $_POST['pilot_id'];
$text = $_POST['text'];

$sql = "INSERT INTO $table  (Pilot_ID, text) VALUES ('$pilot_id', '$text')";


insertSqlStatement($conn, $sql);

$conn->close();
