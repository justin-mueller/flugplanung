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

// Initialize log data
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'mail_file' => $mailFile,
    'subject' => $subject,
    'is_test' => $testing,
    'test_recipient_id' => $testRecipientId,
    'total_recipients' => $total,
    'recipients' => []
];

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
    $recipientLog = [
        'email' => $to,
        'firstname' => $recipient['firstname'],
        'lastname' => $recipient['lastname'],
        'status' => 'failed',
        'attempts' => 0,
        'error' => null
    ];
    
    for ($attempt = 1; $attempt <= $retries && !$sent; $attempt++) {
        $recipientLog['attempts'] = $attempt;
        try {
            $mailer->send($email);
            echo "[{$current}/{$total}] ✓ Sent to {$to}<br>";
            $successCount++;
            $sent = true;
            $recipientLog['status'] = 'success';
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
            $recipientLog['error'] = $errorMsg;
            
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
    
    $logData['recipients'][] = $recipientLog;

    // Add delay between emails to avoid rate limiting (skip on last email)
    if ($current < $total) {
        usleep(500000); // 0.5 seconds
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

// Generate HTML log file
$logData['success_count'] = $successCount;
$logData['fail_count'] = $failCount;
$logData['completed_at'] = date('Y-m-d H:i:s');

$logFilename = generateLogFilename($mailFile, $testing);
$logPath = __DIR__ . '/mails/mail_logs/' . $logFilename;
file_put_contents($logPath, generateHtmlLog($logData));

echo "<br><br><strong>Log saved:</strong> " . htmlspecialchars($logFilename);

/**
 * Generate log filename based on mail template and timestamp
 */
function generateLogFilename($mailFile, $isTesting) {
    $baseName = pathinfo($mailFile, PATHINFO_FILENAME);
    $timestamp = date('Y-m-d_His');
    $testSuffix = $isTesting ? '_TEST' : '';
    return "{$baseName}{$testSuffix}_{$timestamp}.html";
}

/**
 * Generate HTML log content
 */
function generateHtmlLog($logData) {
    $html = '<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Log - ' . htmlspecialchars($logData['subject']) . '</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .metadata {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .metadata p {
            margin: 5px 0;
        }
        .metadata strong {
            display: inline-block;
            width: 180px;
        }
        .summary {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-box {
            flex: 1;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .success-box {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .fail-box {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        .summary-box h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .summary-box .count {
            font-size: 36px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #343a40;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #dee2e6;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-success {
            background: #28a745;
            color: white;
        }
        .status-failed {
            background: #dc3545;
            color: white;
        }
        .error-msg {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            font-style: italic;
        }
        .test-mode {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Newsletter Versand-Protokoll</h1>
        
        ' . ($logData['is_test'] ? '<div class="test-mode">⚠️ TEST-MODUS - Diese E-Mail wurde nur an einen Test-Empfänger gesendet.</div>' : '') . '
        
        <div class="metadata">
            <p><strong>Zeitstempel:</strong> ' . htmlspecialchars($logData['timestamp']) . '</p>
            <p><strong>Abgeschlossen:</strong> ' . htmlspecialchars($logData['completed_at']) . '</p>
            <p><strong>E-Mail Vorlage:</strong> ' . htmlspecialchars($logData['mail_file']) . '</p>
            <p><strong>Betreff:</strong> ' . htmlspecialchars($logData['subject']) . '</p>
            <p><strong>Gesamt Empfänger:</strong> ' . htmlspecialchars($logData['total_recipients']) . '</p>
        </div>
        
        <div class="summary">
            <div class="summary-box success-box">
                <h3>✓ Erfolgreich</h3>
                <div class="count">' . htmlspecialchars($logData['success_count']) . '</div>
            </div>
            <div class="summary-box fail-box">
                <h3>✗ Fehlgeschlagen</h3>
                <div class="count">' . htmlspecialchars($logData['fail_count']) . '</div>
            </div>
        </div>
        
        <h2>Empfänger Details</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>E-Mail</th>
                    <th>Status</th>
                    <th>Versuche</th>
                    <th>Fehler</th>
                </tr>
            </thead>
            <tbody>';
    
    $index = 1;
    foreach ($logData['recipients'] as $recipient) {
        $statusClass = $recipient['status'] === 'success' ? 'status-success' : 'status-failed';
        $statusIcon = $recipient['status'] === 'success' ? '✓' : '✗';
        $statusText = $recipient['status'] === 'success' ? 'Erfolgreich' : 'Fehlgeschlagen';
        
        $html .= '<tr>
                    <td>' . $index++ . '</td>
                    <td>' . htmlspecialchars($recipient['lastname'] . ', ' . $recipient['firstname']) . '</td>
                    <td>' . htmlspecialchars($recipient['email']) . '</td>
                    <td><span class="status-badge ' . $statusClass . '">' . $statusIcon . ' ' . $statusText . '</span></td>
                    <td>' . htmlspecialchars($recipient['attempts']) . '</td>
                    <td>' . ($recipient['error'] ? '<div class="error-msg">' . htmlspecialchars($recipient['error']) . '</div>' : '-') . '</td>
                </tr>';
    }
    
    $html .= '</tbody>
        </table>
    </div>
</body>
</html>';
    
    return $html;
}