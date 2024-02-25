<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once __DIR__ . '/vendor/autoload.php';

require 'check_login.php';

// Access additional user data stored in the session, if available
$mitgliederData = $_SESSION['mitgliederData'] ?? [];

// HGDF club id
$clubId = 198;

// set up Twig
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);

// populate tabs
$tabs = [
	'flugplanung' => [
		'label' => 'Flugplanung',
		'content' => $twig->render('flugplanung.twig.html')
	]
];
if ($mitgliederData['vereinId'] === $clubId) {
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

echo $twig->render(
	'index.twig.html',
	[
		'clubId' => $clubId,
		'mitgliederData' => $mitgliederData,
		'tabs' => $tabs
	]
);
