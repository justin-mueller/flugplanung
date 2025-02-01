<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    Database::connect();

    $params = ['pilotId' => $_POST['pilot_id']];
    $params['vorname'] = $_POST['vorname'];
    $params['nachname'] = $_POST['nachname'];
    $params['verein'] = $_POST['verein'];
    $params['windenfahrer'] = $_POST['windenfahrer'];
    $params['fluggeraet'] = $_POST['fluggeraete_combined'];
    $params['avatar'] = $_POST['avatar-update'];
  

    $pw = $_POST['password'];

    if (!empty($pw)) {

        if ($_POST['password'] === $_POST['password_confirm']) {
            $params['password'] = password_hash($pw, PASSWORD_DEFAULT);
            $passwordString = ', password = :password';
        } else {
            throw new Exception('Passwörter stimmen nicht überein!');
        }
    } else {
        $passwordString = '';
    }

    $emailString = empty($_POST['email']) ? '' : ', email = :email';
   
    $updateQuery = "UPDATE mitglieder SET 
    `firstname` = :vorname ,
    `lastname` = :nachname , 
    `windenfahrer` = :windenfahrer , 
    `fluggeraet` = :fluggeraet , 
    `verein` = :verein ,
    `avatar` = :avatar " . $emailString . $passwordString . " WHERE pilot_id = :pilotId";

    $result = Database::query($updateQuery, $params);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Update erfolgreich!'
        ], JSON_THROW_ON_ERROR);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Query failed. Please check the database.'
        ], JSON_THROW_ON_ERROR);
    }
    
}