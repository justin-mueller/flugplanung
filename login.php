<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/vendor/autoload.php';

\JustinMueller\Flugplanung\Helper::checkLogin();

$error = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    \JustinMueller\Flugplanung\Database::connect();

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Retrieve the hashed password from the database
    $sql = "SELECT * FROM mitglieder WHERE email = '$email'";
    $result = \JustinMueller\Flugplanung\Database::query($sql);
    \JustinMueller\Flugplanung\Database::close();

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

            header('Location: index.php');
            exit;
        }

        // Authentication failed
        $error = 'E-Mail oder Passwort falsch!';
    } else {
        // Authentication failed
        $error = 'E-Mail oder Passwort falsch!';
    }
}

// set up Twig
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);

echo $twig->render(
    'login.twig.html',
    [
        'email' => $email ?? '',
        'error' => $error,
        'clubs' => require 'options_vereine.php'
    ]
);
