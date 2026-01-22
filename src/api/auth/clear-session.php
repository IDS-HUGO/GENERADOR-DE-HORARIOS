<?php
/**
 * API: Limpiar sesión (debug)
 */

require_once '../../config.php';

initSession();

// Destruir sesión completamente
$_SESSION = array();
session_destroy();

// Crear nueva sesión limpia
session_start();

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'message' => 'Sesión limpiada. Recarga la página.',
    'timestamp' => date('Y-m-d H:i:s')
]);
