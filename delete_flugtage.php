<?php

require 'db_connect.php';
require 'insertSqlStatement.php';

$datum = $_POST['datum'];

$sql = "DELETE FROM moegliche_flugtage WHERE datum = '$datum'";

insertSqlStatement($conn, $sql);

$conn->close();


