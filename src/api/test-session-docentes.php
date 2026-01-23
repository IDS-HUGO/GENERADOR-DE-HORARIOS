<?php
/**
 * TEST de sesión para crear docentes
 * Verifica que la sesión funciona correctamente
 */

require_once '../config.php';

// Debug información
echo "<h1>Test de Sesión</h1>";

echo "<h2>Cookies recibidas:</h2>";
echo "<pre>";
var_dump($_COOKIE);
echo "</pre>";

echo "<h2>Session ID:</h2>";
echo "<p>" . session_id() . "</p>";

echo "<h2>Session vars (antes de initSession):</h2>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

initSession();

echo "<h2>Session vars (después de initSession):</h2>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

echo "<h2>Autenticado:</h2>";
echo "<p>" . (isAuthenticated() ? 'SÍ' : 'NO') . "</p>";

if (isAuthenticated()) {
    $user = getCurrentUser();
    echo "<h2>Usuario actual:</h2>";
    echo "<pre>";
    var_dump($user);
    echo "</pre>";
}

?>
