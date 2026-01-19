<?php

/**
 * Automated Newsletter Trigger for Service Wishes Reminder (Dienst-Wünsche)
 * 
 * This script sends a reminder email to all users to fill in their service wishes
 * for the upcoming half-season. It acts as a wrapper around sendNewsletter.php
 * and can be triggered by a cron job or HTTP request.
 * 
 * Usage: curl http://yourdomain.com/flugplanung/sendWuenscheReminder.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use JustinMueller\Flugplanung\Helper;

Helper::loadConfiguration();

$secret = Helper::$configuration['newsletterSecret'];

// Simulate GET parameters for authentication
$_GET['key'] = $secret;
$_GET['test'] = isset($_GET['test']) ? $_GET['test'] : 'true';

// Simulate POST parameters - specific to the Dienst-Wünsche reminder
$_POST['mail_file'] = 'wuensche_reminder.html';
$_POST['user_id_from'] = isset($_GET['from']) ? intval($_GET['from']) : 0;
$_POST['user_id_to'] = isset($_GET['to']) ? intval($_GET['to']) : 99999;
$_POST['internal_only'] = '1'; // Set to internal users only

// Optional test recipient
if (isset($_GET['test_recipient'])) {
    $_POST['test_recipient'] = $_GET['test_recipient'];
}

// Include and execute the main newsletter script
require_once __DIR__ . '/sendNewsletter.php';
