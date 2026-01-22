<?php

/**
 * Newsletter Mail Trigger
 * 
 * This script sends newsletter emails to all subscribed users.
 * It acts as a wrapper around sendMail.php and can be triggered
 * by admin interface or HTTP request.
 * 
 * Usage: curl "http://yourdomain.com/flugplanung/sendNewsletter.php?key=YOUR_SECRET"
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

// Set sender email address for newsletter emails
$_POST['sender_email'] = Helper::$configuration['newsletterFrom'];

// Set preference filter - only send to users who have newsletter enabled
$_POST['preference_filter'] = 'newsletter';

// Pass through test mode
if (!isset($_GET['test'])) {
    $_GET['test'] = isset($_POST['test']) ? $_POST['test'] : 'false';
}

// Include and execute the main mail script
require_once __DIR__ . '/sendMail.php';
