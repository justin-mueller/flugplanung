<?php

/**
 * Automated Mail Trigger for Service Wishes Reminder (Dienst-Wünsche)
 * 
 * This script sends a reminder email to all users to fill in their service wishes
 * for the upcoming half-season. It acts as a wrapper around sendMail.php
 * and can be triggered by a cron job or HTTP request.
 * 
 * Usage: curl http://yourdomain.com/flugplanung/sendWuenscheReminder.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use JustinMueller\Flugplanung\Helper;

Helper::loadConfiguration();

$secret = Helper::$configuration['mailSecret'] ?? Helper::$configuration['newsletterSecret'] ?? null;

// Check authentication - key must be provided in URL
if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    http_response_code(403);
    exit("Forbidden: Invalid or missing key");
}

// Set sender email address for Flugplanung emails
$_POST['sender_email'] = Helper::$configuration['flugplanungFrom'];

// Simulate GET parameters for authentication (key already checked above)
$_GET['test'] = isset($_GET['test']) ? $_GET['test'] : 'false';

// Simulate POST parameters - specific to the Dienst-Wünsche reminder
$_POST['mail_file'] = 'wuensche_reminder.html';
$_POST['user_id_from'] = isset($_GET['from']) ? intval($_GET['from']) : 0;
$_POST['user_id_to'] = isset($_GET['to']) ? intval($_GET['to']) : 99999;
$_POST['internal_only'] = '1'; // Set to internal users only

// Optional test recipient - set here when running in test mode
// EDIT: change this email to the address you want to receive test mails
$defaultTestRecipient = 'register@simulux.de';
if (isset($_GET['test_recipient'])) {
    $_POST['test_recipient'] = $_GET['test_recipient'];
} elseif (isset($_GET['test']) && $_GET['test'] === 'true') {
    $_POST['test_recipient'] = $defaultTestRecipient;
}

// Include and execute the main mail script
require_once __DIR__ . '/sendMail.php';
