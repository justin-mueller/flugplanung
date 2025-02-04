<?php

use AdrianSuter\TwigCacheBusting\CacheBusters\QueryParamCacheBuster;
use AdrianSuter\TwigCacheBusting\CacheBustingTwigExtension;
use AdrianSuter\TwigCacheBusting\HashGenerators\FileMD5HashGenerator;
use JustinMueller\Flugplanung\Helper;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();

// Access additional user data stored in the session, if available
$mitgliederData = $_SESSION['mitgliederData'] ?? [];

// set up Twig
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);
$twig->addExtension(
    CacheBustingTwigExtension::create(
        new QueryParamCacheBuster(__DIR__, new FileMD5HashGenerator()),
        Helper::$configuration['basePath']
    )
);

// populate tabs
$tabs = [
    'flugplanung' => [
        'label' => 'Flugplanung',
        'content' => $twig->render('flugplanung.twig.html')
    ]
];

if ($mitgliederData['vereinId'] === Helper::$configuration['clubId']) {
    $tabs['wunschliste'] = [
        'label' => 'Wunschliste',
        'content' => $twig->render('wunschliste.twig.html')
    ];
}

if ($mitgliederData['dienste_admin']) {
    $tabs['dienste'] = [
        'label' => 'Dienste',
        'content' => $twig->render('dienste.twig.html')
    ];
    $tabs['flugtage'] = [
        'label' => 'Flugtage',
        'content' => $twig->render('flugtage.twig.html')
    ];
}

$tabs['einstellungen'] = [
    'label' => 'Nutzerkonto',
    'content' => $twig->render('einstellungen.twig.html', [
        'clubs' => Helper::$configuration['clubs']
    ])
];

echo $twig->render(
    'index.twig.html',
    [
        'clubId' => Helper::$configuration['clubId'],
        'sites' => Helper::$configuration['sites'],
        'mitgliederData' => $mitgliederData,
        'tabs' => $tabs
    ]
);
