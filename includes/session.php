<?php
if (session_status() == PHP_SESSION_NONE) {
    // 🔧 Set session lifetime and save location
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.save_path', '/var/www/html/sessions'); // ✅ FORCE SAVE PATH

    // 🔐 Proper cross-domain cookie settings
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => '.217.65.145.182.sslip.io', // ✅ Allow subdomains to share session
        'secure' => false,                      // ✅ false for HTTP, true for HTTPS
        'httponly' => true,
        'samesite' => 'None'                    // ✅ Required for cross-origin cookie sharing
    ]);

    session_start();
}

// 🔄 Remove fallback that might override valid session
// if (!isset($_SESSION['loggedin'])) {
//     $_SESSION['loggedin'] = false;
// }

// 🕒 Timeout Settings
$timeout_duration = 1800;     // 30 min inactivity
$absolute_timeout = 28800;    // 8 hours total session max

// ❌ Destroy session if user has been inactive too long
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    session_start();
}

// ❌ Destroy session if user has had session too long
if (isset($_SESSION['session_start_time']) && (time() - $_SESSION['session_start_time']) > $absolute_timeout) {
    session_unset();
    session_destroy();
    session_start();
}

// ⏱️ Update last activity timestamp
if (!empty($_SESSION['loggedin'])) {
    $_SESSION['last_activity'] = time();
}

// ♻️ Regenerate session ID every 10 minutes
function regenerateSession() {
    if (!empty($_SESSION['loggedin']) && (!isset($_SESSION['session_regenerated']) || (time() - $_SESSION['session_regenerated']) > 600)) {
        session_regenerate_id(true);
        $_SESSION['session_regenerated'] = time();
    }
}
regenerateSession();
?>
