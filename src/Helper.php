<?php

namespace JustinMueller\Flugplanung;

class Helper
{
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
