<?php
// ✅ CORS headers (before any output)
header("Access-Control-Allow-Origin: http://ckkso0s04080wkgskwkowwso.217.65.145.182.sslip.io");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// ✅ Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Cross-domain session cookie settings
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '0'); // change to '1' if using HTTPS (sslip.io supports it)

session_start();

include('../../includes/session.php');
include('../../includes/conn.php');
include('../../includes/functions.php');

$data = json_decode(file_get_contents("php://input"), true);

if (json_last_error() !== JSON_ERROR_NONE || empty($data['email']) || empty($data['password'])) {
    sendJsonResponse(false, "Invalid request. Email and password are required.");
    exit;
}

$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$password = $data['password'];

if (!isValidEmail($email)) {
    sendJsonResponse(false, "Invalid email format.");
    exit;
}

// Rate Limiting
$_SESSION['failed_attempts'] = ($_SESSION['failed_attempts'] ?? 0) + 1;
if ($_SESSION['failed_attempts'] > 5) {
    sendJsonResponse(false, "Too many failed login attempts. Try again later.");
    exit;
}

// Fetch user
$sql = "SELECT uuid, fullname, username, email, password, role FROM users WHERE email = :email";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        $user_uuid = bin2hex($user['uuid']);

        $_SESSION['user_uuid'] = strtoupper($user_uuid);
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['loggedin'] = true;
        $_SESSION['session_start_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['failed_attempts'] = 0;

        error_log("Session Data After Login: " . print_r($_SESSION, true));

        $auth_token = bin2hex(random_bytes(16));

        // Set cookies for frontend awareness (optional)
        setcookie("auth_token", $auth_token, [
            "expires" => time() + 3600,
            "path" => "/",
            "domain" => ".217.65.145.182.sslip.io", // Allow all subdomains
            "secure" => false, // change to true if using HTTPS
            "httponly" => true,
            "samesite" => "None"
        ]);

        setcookie("user_role", $user['role'], [
            "expires" => time() + 3600,
            "path" => "/",
            "domain" => ".217.65.145.182.sslip.io",
            "secure" => false,
            "httponly" => false,
            "samesite" => "None"
        ]);

        sendJsonResponse(true, "Login successful!", [
            "user_uuid" => strtoupper($user_uuid),
            "fullname" => $user['fullname'],
            "username" => $user['username'],
            "email" => $user['email'],
            "role" => $user['role']
        ]);
    } else {
        sendJsonResponse(false, "Invalid email or password.");
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    sendJsonResponse(false, "An error occurred during login.");
}
