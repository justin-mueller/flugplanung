<?php

header('Content-Type: application/json');

// Get mail file parameter
$mailFile = isset($_GET['mail_file']) ? $_GET['mail_file'] : '';

if (empty($mailFile)) {
    echo json_encode(['error' => 'mail_file parameter required']);
    exit;
}

// Sanitize filename
$mailFile = basename($mailFile);
$baseName = pathinfo($mailFile, PATHINFO_FILENAME);

// Scan logs directory for matching log files
$logsDir = __DIR__ . '/mails/mail_logs';
$logs = [];

if (is_dir($logsDir)) {
    $files = scandir($logsDir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        // Match log files for this mail template
        // Pattern: {baseName}_YYYY-MM-DD_HHmmss.html or {baseName}_TEST_YYYY-MM-DD_HHmmss.html
        if (preg_match('/^' . preg_quote($baseName, '/') . '(_TEST)?_(\d{4}-\d{2}-\d{2}_\d{6})\.html$/', $file, $matches)) {
            $isTest = !empty($matches[1]);
            $timestamp = $matches[2];
            
            // Convert timestamp to readable format
            $dateTime = DateTime::createFromFormat('Y-m-d_His', $timestamp);
            $readableDate = $dateTime ? $dateTime->format('d.m.Y H:i:s') : $timestamp;
            
            $logs[] = [
                'filename' => $file,
                'timestamp' => $timestamp,
                'readable_date' => $readableDate,
                'is_test' => $isTest,
                'file_size' => filesize($logsDir . '/' . $file)
            ];
        }
    }
    
    // Sort by timestamp descending (newest first)
    usort($logs, function($a, $b) {
        return strcmp($b['timestamp'], $a['timestamp']);
    });
}

echo json_encode(['logs' => $logs]);
