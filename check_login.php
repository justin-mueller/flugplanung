<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_save_path(realpath(dirname($_SERVER['DOCUMENT_ROOT']) . '/../tmp'));
	session_start();
}

if (!isset($_SESSION['email']) && basename($_SERVER['PHP_SELF']) !== 'login_page.php') {
	header("Location: login_page.php");
	exit;
}