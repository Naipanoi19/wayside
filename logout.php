<?php
/**
 * Wayside Airbnb Management System
 * Logout Page
 */

require_once __DIR__ . '/includes/functions.php';

startSession();

// Destroy session
$_SESSION = [];
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}
session_destroy();

setFlashMessage('success', 'You have been logged out successfully.');
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
header('Location: ' . $base . '/index.php');
exit;

