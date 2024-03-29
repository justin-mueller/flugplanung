<?php
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email_register'];
    $password = $_POST['password_register'];
    $vorname = ucwords(strtolower($_POST['vorname_register']));
    $nachname = ucwords(strtolower($_POST['nachname_register']));
    $verein = $_POST['verein_register'];
    $fluggerät_G = isset($_POST['fluggeraet_gleitschirm']) ? 'G' : '';
    $fluggerät_D = isset($_POST['fluggeraet_drachen']) ? 'D' : '';
    $fluggerät_S = isset($_POST['fluggeraet_sonstiges']) ? 'S' : '';    
    $fluggeraetCombined = $fluggerät_G . $fluggerät_D . $fluggerät_S;
    $windenfahrer = $_POST['windenfahrer_register'];
    $avatar = $_POST['avatar_register'];

    // Check if the email already exists
    $checkEmailQuery = "SELECT * FROM mitglieder WHERE email = '$email'";
    $checkResult = $conn->query($checkEmailQuery);

    if ($checkResult->num_rows > 0) {
        // Email already exists, send error response
        echo json_encode(['success' => false, 'error' => 'E-Mail existiert bereits!']);
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into the database
        $insertQuery = "INSERT INTO mitglieder
          (email, password, firstname, lastname, verein, fluggeraet, windenfahrer, avatar) VALUES
          ('$email', '$hashedPassword','$vorname', '$nachname','$verein', '$fluggeraetCombined', '$windenfahrer', '$avatar')";
        if ($conn->query($insertQuery) === TRUE) {
            // Successful insertion
            echo json_encode(['success' => true]);
        } else {
            // Error in insertion
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
    }

    $conn->close();
}