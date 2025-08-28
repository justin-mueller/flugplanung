<?php

require_once __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

$secret = "dsfkjh33fdsdsdfd3fc32098fe";

if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    http_response_code(403);
    exit("Forbidden");
}


Helper::loadConfiguration();
Database::connect();


// Configure transport (replace with your SMTP settings)
$dsn = 'smtp://newsletter@hdgf.de:123456@s185.goserver.host:587';
$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

echo "Sending newsletter...\n";
// Fetch newsletter subscribers
$sql = "SELECT email, firstname, lastname 
        FROM mitglieder 
        WHERE newsletter = 1 AND email = 'register@simulux.de' ";
$recipients = Database::query($sql, []);

// Load newsletter file
$emailFile = __DIR__ . '/newsletter/newsletter.html';
if (!file_exists($emailFile)) {
    die("Newsletter file not found!");
}
$emailContent = file_get_contents($emailFile);

// Try to extract subject and body
$lines = preg_split("/\r\n|\n|\r/", $emailContent);
$subject = "Newsletter";
$body = $emailContent;

foreach ($lines as $line) {
    if (stripos($line, "Subject:") === 0) {
        $subject = trim(substr($line, 8));
        break;
    }
}

// If .eml has headers + body separated by a blank line
$parts = preg_split("/\R\R/", $emailContent, 2);
if (count($parts) === 2) {
    $body = $parts[1]; // just the body part
}

foreach ($recipients as $recipient) {
    $to = $recipient['email'];

    // Personalize
    $personalizedBody = str_replace(
        ['{{firstname}}', '{{lastname}}'],
        [$recipient['firstname'], $recipient['lastname']],
        $body
    );

    $email = (new Email())
        ->from('Newsletter <newsletter@hdgf.de>')
        ->to($to)
        ->subject($subject)
        ->html($personalizedBody);

    try {
        $mailer->send($email);
        echo "Newsletter sent to {$to}\n";
    } catch (\Throwable $e) {
        echo "Failed to send to {$to}: " . $e->getMessage() . "\n";
    }
}
