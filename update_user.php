<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    Database::connect();

    $params = [
        'pilotId' => $_SESSION['mitgliederData']['pilot_id'],
        'vorname' => $_POST['vorname'],
        'nachname' => $_POST['nachname'],
        'verein' => $_POST['verein'],
        'windenfahrer' => $_POST['windenfahrer'],
        'fluggeraet' => $_POST['fluggeraete_combined'],
        'avatar' => $_POST['avatar'],
    ];

    $password = $_POST['password'];

    if (!empty($password)) {
        if ($password === $_POST['password_confirm']) {
            $params['password'] = password_hash($password, PASSWORD_DEFAULT);
            $passwordString = ', password = :password';
        } else {
            throw new RuntimeException('Passwörter stimmen nicht überein!');
        }
    } else {
        $passwordString = '';
    }

    $emailString = '';
    if (!empty($_POST['email'])) {
        $emailString = ', email = :email';
        $params['email'] = $_POST['email'];
    }

    $updateQuery = 'UPDATE mitglieder SET 
    firstname = :vorname ,
    lastname = :nachname , 
    windenfahrer = :windenfahrer , 
    fluggeraet = :fluggeraet , 
    verein = :verein ,
    avatar = :avatar ' . $emailString . $passwordString . ' WHERE pilot_id = :pilotId';

    $result = Database::execute($updateQuery, $params);

    header('Content-Type: application/json');
    if ($result['success']) {
        $sql = 'SELECT * FROM mitglieder WHERE pilot_id = :pilotId';
        $mitgliederData = current(Database::query($sql, ['pilotId' => $_SESSION['mitgliederData']['pilot_id']]));

        if ($mitgliederData) {
            unset($mitgliederData['password']);
            $mitgliederData['vereinId'] = (int)$mitgliederData['verein'];
            $mitgliederData['verein'] = Helper::$configuration['clubs'][$mitgliederData['vereinId']];
            $_SESSION['email'] = $mitgliederData['email'];
            $_SESSION['mitgliederData'] = $mitgliederData;
        }
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
