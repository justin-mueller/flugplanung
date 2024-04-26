<?php

use JustinMueller\Flugplanung\Database;

require_once __DIR__ . '/vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    Database::connect();

    $email = $_POST['email_register'];

    // Check if the email already exists
    $checkEmailQuery = 'SELECT * FROM mitglieder WHERE email = :email';
    $checkResult = Database::query($checkEmailQuery, ['email' => $email]);

    header('Content-Type: application/json');
    if (current($checkResult)) {
        // Email already exists, send error response
        echo json_encode(['success' => false, 'error' => 'E-Mail existiert bereits!'], JSON_THROW_ON_ERROR);
    } else {
        $password = $_POST['password_register'];
        $vorname = $_POST['vorname_register'];
        $nachname = $_POST['nachname_register'];
        $verein = $_POST['verein_register'];
        $fluggerät_G = isset($_POST['fluggeraet_gleitschirm']) ? 'G' : '';
        $fluggerät_D = isset($_POST['fluggeraet_drachen']) ? 'D' : '';
        $fluggerät_S = isset($_POST['fluggeraet_sonstiges']) ? 'S' : '';
        $fluggeraetCombined = $fluggerät_G . $fluggerät_D . $fluggerät_S;
        $windenfahrer = $_POST['windenfahrer_register'];
        $avatar = $_POST['avatar_register'];

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into the database
        $insertQuery = 'INSERT INTO mitglieder
          (email, password, firstname, lastname, verein, fluggeraet, windenfahrer, avatar) VALUES
          (:email, :hashedPassword, :vorname, :nachname, :verein, :fluggeraet, :windenfahrer, :avatar)';
        $result = Database::insertSqlStatement(
            $insertQuery,
            [
                'email' => $email,
                'hashedPassword' => $hashedPassword,
                'vorname' => $vorname,
                'nachname' => $nachname,
                'verein' => $verein,
                'fluggeraet' => $fluggeraetCombined,
                'windenfahrer' => $windenfahrer,
                'avatar' => $avatar,
            ]
        );

        echo json_encode($result, JSON_THROW_ON_ERROR);
    }
}
