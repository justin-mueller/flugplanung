<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();

if (Helper::$configuration['paraglideableApiKey']) {
    $json = file_get_contents(sprintf('https://api.paraglidable.com/?key=%s&format=JSON', Helper::$configuration['paraglideableApiKey']));
    $forecast = json_decode($json, true);
} else {
    $forecast = null;
}

header('Content-Type: application/json');
if ($forecast === null || (is_array($forecast) && $forecast !== [])) {
    echo json_encode($forecast, JSON_THROW_ON_ERROR);
} else {
    echo json_encode(['error' => 'No forecast found'], JSON_THROW_ON_ERROR);
}
