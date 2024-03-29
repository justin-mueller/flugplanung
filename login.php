<?php

include 'check_login.php';
require 'db_connect.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Retrieve the hashed password from the database
    $sql = "SELECT * FROM mitglieder WHERE email = '$email'";
    $result = $conn->query($sql);
    $conn->close();

    if ($result->num_rows == 1) {
        $mitgliederData = $result->fetch_assoc();
        $hashedPasswordFromDB = $mitgliederData['password'];
        unset($mitgliederData['password']);
        // Validate credentials using password_verify
        if (password_verify($password, $hashedPasswordFromDB)) {
            require 'clubs.php';
            $mitgliederData['vereinId'] = (int)$mitgliederData['verein'];
            $mitgliederData['verein'] = $clubs[$mitgliederData['vereinId']];
            // Authentication successful, store username and additional data in session
            $_SESSION['email'] = $email;
            $_SESSION['mitgliederData'] = $mitgliederData;

            header("Location: index.php");
            exit;
        } else {
            // Authentication failed
            $error = "E-Mail oder Passwort falsch!";
        }
    } else {
        // Authentication failed
        $error = "E-Mail oder Passwort falsch!";
    }
}
