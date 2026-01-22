<?php

namespace JustinMueller\Flugplanung;

class Helper
{

    static array $configuration = [];

    public static function loadConfiguration(): void
    {
        // Load default config if it exists
        if (file_exists(__DIR__ . '/../config.dist.php')) {
            self::$configuration = include __DIR__ . '/../config.dist.php';
        }

        // Load and merge actual config
        if (file_exists(__DIR__ . '/../config.php')) {
            $config = include __DIR__ . '/../config.php';
            self::$configuration = array_merge(self::$configuration, $config);
        }
        
        // Ensure configuration was loaded
        if (empty(self::$configuration)) {
            throw new \Exception('No configuration file found. Please ensure config.php exists.');
        }
    }


    public static function checkLogin(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_save_path(realpath(dirname($_SERVER['DOCUMENT_ROOT']) . '/../tmp'));
            session_start();
        }

        // Check if the user is not logged in, redirect to login page
        if (!isset($_SESSION['email']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
            header('Location: login.php');
            exit;
        }
    }
}
