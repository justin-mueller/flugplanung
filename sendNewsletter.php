<?php

require_once __DIR__ . '/vendor/autoload.php';

// Increase execution time for bulk sending (10 minutes)
set_time_limit(600);
ini_set('max_execution_time', 600);

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Flush output immediately
ini_set('output_buffering', 'off');
ini_set('implicit_flush', true);
ob_implicit_flush(true);
if (ob_get_level()) ob_end_flush();

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

Helper::loadConfiguration();

$secret = Helper::$configuration['newsletterSecret'];

if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    http_response_code(403);
    exit("Forbidden");
}
Database::connect();

// Configure transport
$dsn = Helper::$configuration['smtpDsn'];
$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

// Get parameters
$testing = isset($_GET['test']) && $_GET['test'] === 'true';
$mailFile = isset($_POST['mail_file']) ? $_POST['mail_file'] : 'newsletter.html';
$testRecipientId = isset($_POST['test_recipient']) ? intval($_POST['test_recipient']) : null;

// Sanitize mail file name (prevent directory traversal)
$mailFile = basename($mailFile);

echo "Sending mail: " . htmlspecialchars($mailFile) . "<br>";
echo "Test mode: " . ($testing ? "true" : "false") . "<br>";

// Fetch recipients
if ($testing && $testRecipientId) {
    // Send only to selected test recipient
    $sql = "SELECT email, firstname, lastname 
            FROM mitglieder 
            WHERE pilot_id = :pilotId";
    $recipients = Database::query($sql, ['pilotId' => $testRecipientId]);
    echo "Test recipient ID: " . $testRecipientId . "<br>";
} else {
    // Send to all newsletter subscribers
    $sql = "SELECT email, firstname, lastname 
            FROM mitglieder 
            WHERE newsletter = 1";
    $recipients = Database::query($sql, []);
}

if (empty($recipients)) {
    die("No recipients found!");
}

// Load mail file from mails folder
$emailFile = __DIR__ . '/mails/' . $mailFile;
if (!file_exists($emailFile)) {
    die("Mail file not found: " . htmlspecialchars($mailFile));
}
$emailContent = file_get_contents($emailFile);

// Try to extract subject from first line
$lines = preg_split("/\r\n|\n|\r/", $emailContent);
$subject = "Newsletter";
$body = $emailContent;

foreach ($lines as $line) {
    if (stripos($line, "Subject:") === 0) {
        $subject = trim(substr($line, 8));
        break;
    }
}

// If headers + body separated by a blank line
$parts = preg_split("/\R\R/", $emailContent, 2);
if (count($parts) === 2) {
    $body = $parts[1]; // just the body part
}

echo "Subject: " . htmlspecialchars($subject) . "<br>";
echo "Sending to " . count($recipients) . " recipient(s)<br>";
echo "<strong>Adding 2 second delay between emails to avoid rate limiting...</strong><br><br>";

$successCount = 0;
$failCount = 0;
$failedEmails = [];
$total = count($recipients);
$current = 0;

foreach ($recipients as $recipient) {
    $current++;
    $to = $recipient['email'];

    // Personalize
    $personalizedBody = str_replace(
        ['{{firstname}}', '{{lastname}}'],
        [$recipient['firstname'], $recipient['lastname']],
        $body
    );

    $email = (new Email())
        ->from(Helper::$configuration['newsletterFrom'])
        ->to($to)
        ->subject($subject)
        ->html($personalizedBody);

    $sent = false;
    $retries = 3;
    
    for ($attempt = 1; $attempt <= $retries && !$sent; $attempt++) {
        try {
            $mailer->send($email);
            echo "[{$current}/{$total}] ✓ Sent to {$to}<br>";
            $successCount++;
            $sent = true;
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
            
            // Check if it's a rate limit error (450)
            if (strpos($errorMsg, '450') !== false && $attempt < $retries) {
                echo "[{$current}/{$total}] ⏳ Rate limited for {$to}, waiting 5 seconds (attempt {$attempt}/{$retries})...<br>";
                sleep(5);
            } else {
                echo "[{$current}/{$total}] ✗ Failed to send to {$to}: {$errorMsg}<br>";
                $failCount++;
                $failedEmails[] = ['email' => $to, 'error' => $errorMsg];
            }
        }
    }

    // Add delay between emails to avoid rate limiting (skip on last email)
    if ($current < $total) {
        sleep(3);
    }
}

echo "<br><hr>";
echo "<strong>Summary:</strong><br>";
echo "✓ Successfully sent: {$successCount}<br>";
echo "✗ Failed: {$failCount}<br>";

if (!empty($failedEmails)) {
    echo "<br><strong>Failed emails:</strong><br>";
    foreach ($failedEmails as $failed) {
        echo "- " . htmlspecialchars($failed['email']) . "<br>";
    }
}

echo "<br><strong>Done!</strong>";