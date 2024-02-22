<?php
function insertSqlStatement($conn, $sql)
{
	if ($conn->query($sql) === TRUE) {
		// Successful insertion
		echo json_encode(['success' => true]);
		echo 'query: ' . $sql;
	} else {
		// Error in insertion
		echo json_encode(['success' => false, 'error' => $conn->error]);
	}
}

