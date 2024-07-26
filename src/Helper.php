<?php

namespace JustinMueller\Flugplanung;

class Helper
{

    static array $configuration = [];

    public static function loadConfiguration(): void
    {
        self::$configuration = include __DIR__ . '/../config.dist.php';

        if (file_exists(__DIR__ . '/../config.php')) {
            self::$configuration = array_merge(self::$configuration, include __DIR__ . '/../config.php');
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
            header("Location: login.php");
            exit;
        }
    }
}
