<?php

use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();

// Remove the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Destroy the session data
session_destroy();

header('Location: login.php');
