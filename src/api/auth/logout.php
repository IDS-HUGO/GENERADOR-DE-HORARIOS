<?php
/**
 * API: Logout
 */

require_once '../../config.php';

initSession();

// Destruir sesión
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        SESSION_NAME,
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

logInfo('User logged out');

jsonResponse(true, 'Sesión cerrada', [
    'redirect' => baseUrl('public/index.html')
]);
