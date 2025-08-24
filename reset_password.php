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
Database::connect();
//session_start();

$token = urldecode($token = $_POST['token'] ?? $_GET['token'] ?? '');
$tokenHash  = hash('sha256', $token);

$tokenValid = false;

if ($token) {
    // Check if the token is valid and not expired

    $sql = 'SELECT * FROM password_resets WHERE token = :token AND expires >= :now';
    $reset = current(Database::query($sql, ['token' => $tokenHash, 'now' => date("Y-m-d H:i:s")]));

    $tokenValid = !empty($reset);
    if ($tokenValid) {
        // Token is valid, the user can proceed to reset password
        $userId = $reset['pilot_id'];
    } else {
        echo 'Ungültiger oder abgelaufener Link.';
    }
} else {
    echo 'Kein Link angegeben.';
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {

    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword === $confirmPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the user's password
        $sql = 'UPDATE mitglieder SET password = :password WHERE pilot_id = :pilot_id';
        Database::query($sql, ['password' => $hashedPassword, 'pilot_id' => $userId]);

        // Delete the reset token
        $sql = 'DELETE FROM password_resets WHERE token = :token';
        Database::query($sql, ['token' => $token]);

        echo "Ihr Passwort wurde erfolgreich zurückgesetzt.";
    } else {
        echo "Passwörter stimmen nicht überein.";
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


// Render the form template with Twig, passing the token status
echo $twig->render('reset_password.twig.html', [
    'tokenValid' => $tokenValid,
    'token' => htmlspecialchars($token)
]);

?>

