<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pilot_id = $_POST['pilot_id'];
    $max_dienste = $_POST['max_dienste_halbjahr'];
    
    // Validate that the user is updating their own data
    if ((int)$pilot_id !== (int)$_SESSION['mitgliederData']['pilot_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Forbidden']);
        exit;
    }
    
    // Validate that the user is a windenfahrer
    if (!$_SESSION['mitgliederData']['windenfahrer']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Only windenfahrer can set this limit']);
        exit;
    }
    
    // Convert empty string to NULL
    $max_dienste = ($max_dienste === '' || $max_dienste === 'null') ? null : (int)$max_dienste;
    
    $sql = 'UPDATE mitglieder SET max_dienste_halbjahr = :max_dienste WHERE pilot_id = :pilot_id';
    $result = Database::execute($sql, [
        'max_dienste' => $max_dienste,
        'pilot_id' => $pilot_id
    ]);
    
    // Update session data
    $_SESSION['mitgliederData']['max_dienste_halbjahr'] = $max_dienste;
    
    header('Content-Type: application/json');
    if ($result['success']) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
}
