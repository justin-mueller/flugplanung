<?php

/**
 * Automated Duty Reminder Script
 * 
 * This script sends reminder emails to users who have duties scheduled 7 days from today.
 * It runs daily via cron job and sends individual personalized emails to each user.
 * 
 * Usage: curl "http://yourdomain.com/flugplanung/sendDutyReminder.php?key=YOUR_SECRET"
 */

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

// Increase execution time
set_time_limit(300);

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

Helper::loadConfiguration();

$secret = Helper::$configuration['mailSecret'] ?? Helper::$configuration['newsletterSecret'] ?? null;

// Check authentication - key must be provided in URL
if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    http_response_code(403);
    exit("Forbidden: Invalid or missing key");
}

// Set preference filter for this mail type
// This ensures only users who have duty_reminder enabled will receive emails
$preferenceFilter = 'duty_reminder';

echo "<h2>Duty Reminder - Running</h2>";
echo "<p>Current date: " . date('Y-m-d') . "</p>";
echo "<p>Preference filter: " . htmlspecialchars($preferenceFilter) . " = 1</p>";
echo "<hr>";

Database::connect();

// Configure transport
$dsn = Helper::$configuration['smtpDsn'];
$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

// Query for duties with personalized reminder dates based on user preferences
// Only queries users where {$preferenceFilter} = 1 (respects user preference filter)
// The query finds users who have duty_reminder enabled and calculates the target date
// based on their duty_reminder_days preference
$sql = "SELECT 
    d.flugtag,
    d.pilot_id,
    d.windenfahrer,
    d.startleiter,
    m.firstname,
    m.lastname,
    m.email,
    m.duty_reminder_days
FROM dienste d
JOIN mitglieder m ON d.pilot_id = m.pilot_id
WHERE d.pilot_id IS NOT NULL
AND m.{$preferenceFilter} = 1
AND DATEDIFF(d.flugtag, CURDATE()) = m.duty_reminder_days
ORDER BY d.pilot_id, d.flugtag";

$duties = Database::query($sql, []);

if (empty($duties)) {
    echo "<p><strong>No duty reminders to send today</strong></p>";
    echo "<p>No users have duties matching their reminder preference for today.</p>";
    exit;
}

echo "<p>Found " . count($duties) . " duty assignment(s) to remind about</p>";
echo "<hr>";

// Load email template
$emailFile = __DIR__ . '/mails/duty_reminder.html';
if (!file_exists($emailFile)) {
    die("Email template not found: duty_reminder.html");
}
$emailTemplate = file_get_contents($emailFile);

// Extract subject from template
$subject = "Erinnerung: Dein Flugdienst";
$lines = preg_split("/\r\n|\n|\r/", $emailTemplate);
foreach ($lines as $line) {
    if (stripos($line, "Subject:") === 0) {
        $subject = trim(substr($line, 8));
        break;
    }
}

// Get body content (after headers)
$parts = preg_split("/\R\R/", $emailTemplate, 2);
$bodyTemplate = count($parts) === 2 ? $parts[1] : $emailTemplate;

$successCount = 0;
$failCount = 0;
$sentEmails = [];

// Group duties by pilot to send one email per person with all their duties
$dutiesByPilot = [];
foreach ($duties as $duty) {
    $pilotId = $duty['pilot_id'];
    if (!isset($dutiesByPilot[$pilotId])) {
        $dutiesByPilot[$pilotId] = [
            'pilot' => $duty,
            'duties' => []
        ];
    }
    $dutiesByPilot[$pilotId]['duties'][] = $duty;
}

// Get sender email from configuration
$senderEmail = Helper::$configuration['mailFrom'] ?? Helper::$configuration['flugplanungFrom'] ?? 'noreply@example.com';

foreach ($dutiesByPilot as $pilotId => $data) {
    $pilot = $data['pilot'];
    $pilotDuties = $data['duties'];
    
    // Determine duties/functions
    $functions = [];
    foreach ($pilotDuties as $duty) {
        if ($duty['windenfahrer']) $functions[] = 'Windenfahrer';
        if ($duty['startleiter']) $functions[] = 'Startleiter';
    }
    $functionsText = !empty($functions) ? implode(', ', array_unique($functions)) : 'Nicht angegeben';
    
    // Format date
    $dutyDate = date('d.m.Y (l)', strtotime($pilot['flugtag']));
    
    // Get reminder days for this user
    $reminderDays = $pilot['duty_reminder_days'] ?? 7;
    
    // Personalize email body
    $personalizedBody = str_replace(
        ['{{firstname}}', '{{lastname}}', '{{duty_date}}', '{{duties}}', '{{reminder_days}}'],
        [$pilot['firstname'], $pilot['lastname'], $dutyDate, $functionsText, $reminderDays],
        $bodyTemplate
    );
    
    // Personalize subject
    $personalizedSubject = str_replace(
        ['{{duty_date}}'],
        [$dutyDate],
        $subject
    );
    
    $to = $pilot['email'];
    
    $email = (new Email())
        ->from($senderEmail)
        ->to($to)
        ->subject($personalizedSubject)
        ->html($personalizedBody);
    
    try {
        $mailer->send($email);
        echo "✓ Sent to {$pilot['firstname']} {$pilot['lastname']} ({$to}) - Functions: {$functionsText} - Reminder: {$reminderDays} days before<br>";
        $successCount++;
        $sentEmails[] = $to;
    } catch (\Throwable $e) {
        echo "✗ Failed to send to {$to}: " . htmlspecialchars($e->getMessage()) . "<br>";
        $failCount++;
    }
    
    // Small delay to avoid rate limiting
    if ($pilotId < count($dutiesByPilot)) {
        usleep(500000); // 0.5 seconds
    }
}

echo "<br><hr>";
echo "<strong>Summary:</strong><br>";
echo "✓ Successfully sent: {$successCount}<br>";
echo "✗ Failed: {$failCount}<br>";
echo "<br><strong>Done!</strong>";
