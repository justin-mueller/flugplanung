<?php

use AdrianSuter\TwigCacheBusting\CacheBusters\QueryParamCacheBuster;
use AdrianSuter\TwigCacheBusting\CacheBustingTwigExtension;
use AdrianSuter\TwigCacheBusting\HashGenerators\FileMD5HashGenerator;
use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/vendor/autoload.php';

// Load app configuration
Helper::loadConfiguration();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    Database::connect();
    $email = $_POST['email'] ?? null;

    if ($email) {
        // Check if the email exists in the database
        $sql = 'SELECT pilot_id FROM mitglieder WHERE email = :email';
        $user = current(Database::query($sql, ['email' => $email]));

        if ($user) {
            $userId = $user['pilot_id'];
            $token  = bin2hex(random_bytes(50)); // secure random token
            $tokenHash  = hash('sha256', $token); // store this in DB

            // Store the token in DB with expiration (30 min)
            $expires = date('Y-m-d H:i:s', time() + 1800);
            $sql = 'INSERT INTO password_resets (pilot_id, token, expires) 
                    VALUES (:pilot_id, :token, :expires)';
            Database::query($sql, [
                'pilot_id' => $userId,
                'token'    => $tokenHash,
                'expires'  => $expires
            ]);

            // Build reset link
            $resetLink = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/' . Helper::$configuration['basePath'] . '/reset_password.php?token=' . urlencode($token);

            // --- MAILER SETUP ---
            $dsn = 'smtp://no-reply@hdgf.de:8uI43Oqqhjx1hvjA@s185.goserver.host:587';
            $transport = Transport::fromDsn($dsn);
            $mailer = new Mailer($transport);

            $emailMessage = (new Email())
                ->from('no-reply@hdgf.de')
                ->to($email)
                ->subject('Passwort zurücksetzen')
                ->text("Bitte klicke auf folgenden Link, um Dein Passwort zurückzusetzen:\n\n" . $resetLink);

            $mailer->send($emailMessage);
        }
    }

    // Set up Twig
    $loader = new FilesystemLoader(__DIR__ . '/templates');
    $twig = new Environment($loader);
    $twig->addExtension(
        CacheBustingTwigExtension::create(
            new QueryParamCacheBuster(__DIR__, new FileMD5HashGenerator()),
            Helper::$configuration['basePath']
        )
    );

    // Always same response (no email enumeration)
    echo $twig->render('password_reset_sent.twig.html');
}
