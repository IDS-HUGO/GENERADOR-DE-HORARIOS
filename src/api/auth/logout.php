<?php
/**
 * API: Logout - Cierre de sesión completo
 */

require_once '../../config.php';

initSession();
header('Content-Type: application/json; charset=utf-8');

try {
    // Registrar logout en logs
    $userEmail = $_SESSION['email'] ?? 'desconocido';
    logInfo('User logged out', ['email' => $userEmail]);
    
    // Destruir sesión
    $_SESSION = [];
    
    // Limpiar cookies de sesión
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
    
    // Construir URL de redirección dinámica usando APP_URL
    // APP_URL ya tiene: http://localhost/ClassControl (o la carpeta que sea)
    $redirectUrl = APP_URL . '/public/index.html';
    
    jsonResponse(true, 'Sesión cerrada correctamente', [
        'redirect' => $redirectUrl,
        'message' => 'Sesión cerrada. Redirigiendo al login...'
    ]);
    
} catch (Exception $e) {
    logError('Logout Error', $e->getMessage());
    jsonResponse(false, 'Error al cerrar sesión', null, 500);
}
