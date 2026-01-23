<?php
/**
 * Test de Sesión
 */

require_once '../config.php';

header('Content-Type: application/json');
initSession();

$user = getCurrentUser();

echo json_encode([
    'session_status' => session_status(),
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'user_from_function' => $user,
    'is_authenticated' => isAuthenticated()
], JSON_PRETTY_PRINT);
