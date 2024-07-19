<?php

use AdrianSuter\TwigCacheBusting\CacheBusters\QueryParamCacheBuster;
use AdrianSuter\TwigCacheBusting\CacheBustingTwigExtension;
use AdrianSuter\TwigCacheBusting\HashGenerators\FileMD5HashGenerator;
use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/vendor/autoload.php';

Helper::checkLogin();

$error = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Database::connect();

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Retrieve the hashed password from the database
    $sql = 'SELECT * FROM mitglieder WHERE email = :email';
    $mitgliederData = current(Database::query($sql, ['email' => $email]));

    if ($mitgliederData) {
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
$twig->addExtension(
    CacheBustingTwigExtension::create(
        new QueryParamCacheBuster(__DIR__, new FileMD5HashGenerator())
    )
);

echo $twig->render(
    'login.twig.html',
    [
        'email' => $email ?? '',
        'error' => $error,
        'clubs' => require 'options_vereine.php'
    ]
);
