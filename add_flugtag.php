<?php
require 'check_login.php';

require 'db_connect.php';
require 'insertSqlStatement.php';

$table = "moegliche_flugtage";
$datum = $_POST['datum'];

$sql = "INSERT INTO $table (datum) VALUES ('$datum')";

insertSqlStatement($conn, $sql);

$conn->close();

