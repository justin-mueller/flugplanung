<?php

use AdrianSuter\TwigCacheBusting\CacheBusters\QueryParamCacheBuster;
use AdrianSuter\TwigCacheBusting\CacheBustingTwigExtension;
use AdrianSuter\TwigCacheBusting\HashGenerators\FileMD5HashGenerator;
use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$error = '';

// Delete expired password reset tokens
$sql = 'DELETE FROM password_resets WHERE expires < :now';
Database::query($sql, ['now' => date('Y-m-d H:i:s')]);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Retrieve the hashed password from the database
    $sql = 'SELECT * FROM mitglieder WHERE email = :email';
    $result = Database::query($sql, ['email' => $email]);

    if ($result !== false && $result !== []) {
        $mitgliederData = current($result);
        $hashedPasswordFromDB = $mitgliederData['password'];
        unset($mitgliederData['password']);
        // Validate credentials using password_verify
        if (password_verify($password, $hashedPasswordFromDB)) {
            $mitgliederData['vereinId'] = (int)$mitgliederData['verein'];
            $mitgliederData['verein'] = Helper::$configuration['clubs'][$mitgliederData['vereinId']];
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
        new QueryParamCacheBuster(__DIR__, new FileMD5HashGenerator()),
        Helper::$configuration['basePath']
    )
);

echo $twig->render(
    'login.twig.html',
    [
        'clubId' => Helper::$configuration['clubId'],
        'email' => $email ?? '',
        'error' => $error,
        'clubs' => Helper::$configuration['clubs']
    ]
);
