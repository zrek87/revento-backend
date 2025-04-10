<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Debug file path
$log_path = '/var/www/html/storage/session_debug.txt';

// 🛡 Prevent accidental output before headers
ob_start();

// ✅ Log request details
if (is_writable(dirname($log_path))) {
    file_put_contents($log_path, "=== NEW REQUEST ===\n", FILE_APPEND);
    file_put_contents($log_path, "COOKIE: " . print_r($_COOKIE, true) . "\n", FILE_APPEND);
}

// ✅ Start PHP session safely
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.save_path', '/tmp');
    session_start();

    if (is_writable(dirname($log_path))) {
        file_put_contents($log_path, "SESSION STARTED\n", FILE_APPEND);
        file_put_contents($log_path, "SESSION ID: " . session_id() . "\n", FILE_APPEND);
        file_put_contents($log_path, "SESSION FILE: " . session_save_path() . "/sess_" . session_id() . "\n", FILE_APPEND);
        file_put_contents($log_path, "SESSION CONTENT: " . print_r($_SESSION, true) . "\n", FILE_APPEND);
    }
}

// ✅ Includes after session
include('../../includes/session.php');
include('../../includes/functions.php');

// ✅ CORS & headers
header("Access-Control-Allow-Origin: http://ckkso0s04080wkgskwkowwso.217.65.145.182.sslip.io");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Session expiration logic
$absolute_timeout = 28800;
$inactivity_timeout = 1800;

if (!isset($_SESSION['session_start_time'])) {
    $_SESSION['session_start_time'] = time();
}

if (!empty($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $current_time = time();
    $session_duration = $current_time - $_SESSION['session_start_time'];
    $inactive_duration = $current_time - ($_SESSION['last_activity'] ?? $current_time);

    if ($session_duration > $absolute_timeout || $inactive_duration > $inactivity_timeout) {
        session_unset();
        session_destroy();
        sendJsonResponse(false, "Session expired. Please log in again.");
        exit;
    }

    $_SESSION['last_activity'] = $current_time;

    sendJsonResponse(true, "Session is active", [
        "user_uuid" => $_SESSION['user_uuid'] ?? null,
        "username" => $_SESSION['username'] ?? null,
        "role" => $_SESSION['role'] ?? null
    ]);
} else {
    sendJsonResponse(false, "Session expired.");
}
