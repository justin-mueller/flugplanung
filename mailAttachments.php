<?php

/**
 * Mail Attachments Configuration
 *
 * This file defines which images should be attached to specific email templates.
 * Images must be placed in the 'mails/' folder.
 */

// Enable/disable image attachments globally
$enableAttachments = true;

// Attachment configuration per mail file
// Format: 'mail_filename.html' => ['image_filename.jpg' => 'content_id@domain']
$mailAttachments = [
    'test_mail.html' => [
        'hdgflogo.png' => 'hdgf@flugplanung'
    ],
    'newsletter.html' => [
        'hdgflogo.png' => 'hdgf@flugplanung',
        'screenshot_flugtage.png' => 'screenshot_flugtage@flugplanung'
    ],
    // Add more mail files and their attachments here
    // 'another_mail.html' => [
    //     'image1.jpg' => 'image1@domain',
    //     'image2.png' => 'image2@domain'
    // ]
];

// Do not modify below this line
return [
    'enabled' => $enableAttachments,
    'attachments' => $mailAttachments
];