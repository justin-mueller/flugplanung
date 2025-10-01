<?php
// --- DEV ONLY: show PHP errors (remove in production) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

// Load app configuration
Helper::loadConfiguration();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

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
            $expires = date("Y-m-d H:i:s", time() + 1800);
            $sql = 'INSERT INTO password_resets (pilot_id, token, expires) 
                    VALUES (:pilot_id, :token, :expires)';
            Database::query($sql, [
                'pilot_id' => $userId,
                'token'    => $tokenHash,
                'expires'  => $expires
            ]);

            // Build reset link (adjust domain!)
            $resetLink = "http://localhost/flugplanung/reset_password.php?token=" . urlencode($token);

            // --- MAILER SETUP ---
            // Save mails into local folder /mails for testing
            // (they will be .eml files you can open in any mail client)
            
            $dsn = 'smtp://no-reply@hdgf.de:8uI43Oqqhjx1hvjA@s185.goserver.host:587';
            $transport = Transport::fromDsn($dsn);
            $mailer = new Mailer($transport);

            $emailMessage = (new Email())
                ->from('no-reply@hdgf.de')
                ->to($email)
                ->subject('Password Reset Request')
                ->text("Bitte auf diesen Link gehen, um das Passwort zurueckzusetzen:\n\n" . $resetLink);

            $mailer->send($emailMessage);

            $emailsDir = __DIR__ . '/mails';
            if (!is_dir($emailsDir)) mkdir($emailsDir, 0777, true);

            //$emailFile = $emailsDir . '/' . time() . '-' . md5($email) . '.txt';
            //file_put_contents($emailFile, "To: $email\nSubject: Password Reset Request\nFrom: no-reply@hdgf.de.com\n\nClick the link to reset your password:\n$resetLink");

            echo "If this email is registered, a password reset link has been saved to $email! ";


        }
    }

    // Always same response (no email enumeration)
    echo "If this email is registered, a password reset link has been sent.";
}


