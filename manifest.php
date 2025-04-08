<?php
declare(strict_types=1);

namespace JustinMueller\Flugplanung;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();

$clubId = Helper::$configuration['clubId'];
$club = Helper::$configuration['clubs'][$clubId];

$shortName = $club['shortName'];
$logo = strtolower($shortName);

header('Content-type: application/json');

echo <<<JSON
{
    "name": "Flugplanung {$shortName}",
    "short_name": "{$shortName}",
    "display": "standalone",
    "start_url": "/",
    "icons": [
      {
        "src": "/img/clubs/{$logo}.png",
        "sizes": "192x192",
        "type": "image/png"
      },
      {
        "src": "/img/clubs/{$logo}-512.png",
        "sizes": "512x512",
        "type": "image/png"
      },
      {
        "src": "/img/clubs/{$logo}-maskable.png",
        "sizes": "192x192",
        "type": "image/png",
        "purpose": "maskable"
      },
      {
        "src": "/img/clubs/{$logo}-maskable-512.png",
        "sizes": "512x512",
        "type": "image/png",
        "purpose": "maskable"
      }
    ]
  }
JSON;