<?php
include('../../includes/session.php'); // Ensure session starts

header("Access-Control-Allow-Origin: http://ckkso0s04080wkgskwkowwso.217.65.145.182.sslip.io");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$_SESSION = [];

session_unset();
session_destroy();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/', 'localhost', false, true);
}

if (isset($_COOKIE['auth_token'])) {
    setcookie("auth_token", "", time() - 3600, "/", "localhost", false, true);
}

echo json_encode([
    "success" => true,
    "message" => "Logged out successfully."
]);
exit;
?>
