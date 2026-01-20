<?php

require_once __DIR__ . '/vendor/autoload.php';

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

header('Content-Type: application/json');

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

// Check if user is admin
$mitgliederData = $_SESSION['mitgliederData'] ?? [];
if (!$mitgliederData['dienste_admin']) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$response = [];

// Get all mail files from mails folder
$mailsDir = __DIR__ . '/mails';
$mails = [];

if (is_dir($mailsDir)) {
    $files = scandir($mailsDir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        if (pathinfo($file, PATHINFO_EXTENSION) === 'html') {
            $filePath = $mailsDir . '/' . $file;
            $content = file_get_contents($filePath);
            
            // Extract subject from first line
            $subject = 'Kein Betreff';
            $lines = preg_split("/\r\n|\n|\r/", $content);
            foreach ($lines as $line) {
                if (stripos($line, "Subject:") === 0) {
                    $subject = trim(substr($line, 8));
                    break;
                }
            }
            
            // Determine mail type and sender based on filename
            $mailType = 'Newsletter';
            $wrapper = 'sendNewsletter.php';
            $sender = Helper::$configuration['newsletterFrom'];
            if (strpos($file, 'wuensche') !== false) {
                $mailType = 'Wünsche-Erinnerung';
                $wrapper = 'sendWuenscheReminder.php';
                $sender = Helper::$configuration['flugplanungFrom'];
            } elseif (strpos($file, 'duty') !== false || strpos($file, 'dienst') !== false) {
                $mailType = 'Dienst-Erinnerung';
                $wrapper = 'sendDutyReminder.php';
                $sender = Helper::$configuration['flugplanungFrom'];
            } elseif (strpos($file, 'neujahr') !== false) {
                $mailType = 'Flugplanung';
                $wrapper = 'sendFlugplanung.php';
                $sender = Helper::$configuration['newsletterFrom']; // Changed to newsletter sender
            }
            
            $mails[] = [
                'filename' => $file,
                'subject' => $subject,
                'modified' => filemtime($filePath),
                'type' => $mailType,
                'wrapper' => $wrapper,
                'sender' => $sender
            ];
        }
    }
    
    // Sort by modified date, newest first
    usort($mails, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
}

$response['mails'] = $mails;

// Get all members for test recipient dropdown
$sql = "SELECT pilot_id, firstname, lastname, email 
        FROM mitglieder 
        ORDER BY lastname, firstname";
$members = Database::query($sql, []);
$response['members'] = $members ?: [];

echo json_encode($response);
