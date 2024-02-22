<?php
require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email_register'];
    $password = $_POST['password_register'];
    $vorname = ucwords(strtolower($_POST['vorname_register']));
    $nachname = ucwords(strtolower($_POST['nachname_register']));
    $verein = $_POST['verein_register'];
    $windenfahrer = $_POST['windenfahrer_register'];

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
          (email, password, firstname, lastname, verein, windenfahrer) VALUES
          ('$email', '$hashedPassword','$vorname', '$nachname','$verein', '$windenfahrer')";
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