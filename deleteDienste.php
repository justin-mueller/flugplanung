<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

if (!isset($_GET['year']) || !filter_var($_GET['year'], FILTER_VALIDATE_INT)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid year parameter'], JSON_THROW_ON_ERROR);
    exit;
}

$sqlDelete = 'DELETE FROM dienste WHERE YEAR(flugtag) = :year';

try {
    // Execute the query and check if rows were deleted
    $result = Database::query($sqlDelete, ['year' => $_GET['year']]);

    // Return the result as JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'affected_rows' => $result], JSON_THROW_ON_ERROR);
} catch (Exception $e) {
    // Handle errors if query execution fails
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error deleting records: ' . $e->getMessage()], JSON_THROW_ON_ERROR);
}
