<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔍 Log everything immediately — BEFORE includes or headers
file_put_contents('/tmp/session_debug.txt', "=== NEW REQUEST ===\n", FILE_APPEND);
file_put_contents('/tmp/session_debug.txt', "COOKIE: " . print_r($_COOKIE, true) . "\n", FILE_APPEND);

// Safe fallback if session isn’t started yet
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.save_path', '/tmp'); // Make sure it uses valid path
    session_start();
    file_put_contents('/tmp/session_debug.txt', "SESSION STARTED\n", FILE_APPEND);
}

// Now log session values and session file
file_put_contents('/tmp/session_debug.txt', "SESSION ID: " . session_id() . "\n", FILE_APPEND);
file_put_contents('/tmp/session_debug.txt', "SESSION PATH: " . session_save_path() . "\n", FILE_APPEND);
file_put_contents('/tmp/session_debug.txt', "SESSION CONTENT: " . print_r($_SESSION, true) . "\n", FILE_APPEND);

include('../../includes/session.php');
include('../../includes/functions.php');

// ✅ CORS headers
header("Access-Control-Allow-Origin: http://ckkso0s04080wkgskwkowwso.217.65.145.182.sslip.io");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 🔒 Timeout logic
$absolute_timeout = 28800;
$inactivity_timeout = 1800;

if (!isset($_SESSION['session_start_time'])) {
    $_SESSION['session_start_time'] = time();
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
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
        "user_uuid" => $_SESSION['user_uuid'],
        "username" => $_SESSION['username'],
        "role" => $_SESSION['role']
    ]);
} else {
    sendJsonResponse(false, "Session expired.");
}
?>
