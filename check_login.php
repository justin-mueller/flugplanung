<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

// Check if the user is not logged in, redirect to login page
if (!isset($_SESSION['email'])) {
	header("Location: login_page.php");
	exit;
}
