<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Force session path for Docker
ini_set('session.save_path', '/tmp');

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Show everything we need
header('Content-Type: text/plain');
echo "=== SESSION DEBUG ===\n";
echo "COOKIE:\n";
print_r($_COOKIE);

echo "\nSESSION ID: " . session_id() . "\n";
echo "SESSION CONTENT:\n";
print_r($_SESSION);

echo "\nSERVER:\n";
print_r($_SERVER);
