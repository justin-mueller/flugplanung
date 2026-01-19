<?php

require_once __DIR__ . '/vendor/autoload.php';

use JustinMueller\Flugplanung\Helper;

Helper::loadConfiguration();
Helper::checkLogin();

// Check if user is admin
$mitgliederData = $_SESSION['mitgliederData'] ?? [];
if (!$mitgliederData['dienste_admin']) {
    http_response_code(403);
    exit('Forbidden');
}

$filename = isset($_GET['file']) ? $_GET['file'] : '';

// Sanitize filename (prevent directory traversal)
$filename = basename($filename);

if (empty($filename)) {
    http_response_code(400);
    exit('No file specified');
}

$filePath = __DIR__ . '/mails/' . $filename;

if (!file_exists($filePath)) {
    http_response_code(404);
    exit('File not found');
}

$content = file_get_contents($filePath);

// Extract subject
$subject = 'Kein Betreff';
$lines = preg_split("/\r\n|\n|\r/", $content);
foreach ($lines as $line) {
    if (stripos($line, "Subject:") === 0) {
        $subject = trim(substr($line, 8));
        break;
    }
}

// Get body (after first blank line)
$parts = preg_split("/\R\R/", $content, 2);
$body = count($parts) === 2 ? $parts[1] : $content;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($subject); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #343a40;
            color: white;
            padding: 15px 20px;
            margin: -20px -20px 20px -20px;
            border-radius: 0;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header .filename {
            color: #adb5bd;
            font-size: 12px;
            margin-top: 5px;
        }
        .content {
            border: 1px solid #ddd;
            padding: 20px;
            background: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Betreff: <?php echo htmlspecialchars($subject); ?></h1>
        <div class="filename">Datei: <?php echo htmlspecialchars($filename); ?></div>
    </div>
    <div class="content">
        <?php echo $body; ?>
    </div>
</body>
</html>
