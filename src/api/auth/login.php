<?php
/**
 * API: Login
 */

require_once '../../config.php';
require_once INCLUDES_PATH . '/Models.php';

initSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método no permitido', null, 405);
}

// Leer JSON del body o usar $_POST como fallback
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (empty($data)) {
    $data = $_POST;
}

if (empty($data['email']) || empty($data['password'])) {
    jsonResponse(false, 'Email y contraseña son requeridos', null, 400);
}

$email = sanitize($data['email']);
$password = $data['password'];

// Validaciones básicas
if (!validateEmail($email)) {
    jsonResponse(false, 'Formato de email inválido', null, 400);
}

// Protección: límite de intentos por sesión
$maxAttempts = 5;
$lockoutSeconds = 300; // 5 minutos
$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
$_SESSION['login_last_attempt'] = $_SESSION['login_last_attempt'] ?? 0;

if ($_SESSION['login_attempts'] >= $maxAttempts) {
    $since = time() - $_SESSION['login_last_attempt'];
    if ($since < $lockoutSeconds) {
        $wait = ceil(($lockoutSeconds - $since) / 60);
        jsonResponse(false, "Demasiados intentos. Intente nuevamente en {$wait} minuto(s)", null, 429);
    } else {
        // Resetear contador pasados los segundos de bloqueo
        $_SESSION['login_attempts'] = 0;
    }
}

$usuarioModel = new Usuario();
$result = $usuarioModel->authenticate($email, $password);

if (!$result['success']) {
    // Incrementar contador de intentos (evitar loguear contraseñas)
    $_SESSION['login_attempts']++;
    $_SESSION['login_last_attempt'] = time();
    logInfo('Failed login attempt', ['email' => $email, 'attempts' => $_SESSION['login_attempts']]);
    jsonResponse(false, $result['message'], null, 401);
}

$user = $result['user'];

// Resetear contador de intentos tras login exitoso
$_SESSION['login_attempts'] = 0;

// Crear sesión con datos mínimos
$_SESSION['usuario_id'] = $user['usuario_id'];
$_SESSION['nombre'] = $user['nombre'];
$_SESSION['apellido'] = $user['apellido'] ?? '';
$_SESSION['email'] = $user['email'];
$_SESSION['tipo_usuario'] = $user['tipo_usuario'];
$_SESSION['login_time'] = time();

logInfo('User logged in', ['usuario_id' => $user['usuario_id']]);

// Determinar redirección
$redirect = baseUrl('public/');
if (isset($user['tipo_usuario']) && $user['tipo_usuario'] === 'administrador') {
    $redirect = baseUrl('public/admin/dashboard.php');
} else {
    $redirect = baseUrl('public/docente/dashboard.php');
}

jsonResponse(true, 'Login exitoso', [
    'usuario_id' => $user['usuario_id'],
    'nombre' => $user['nombre'],
    'tipo_usuario' => $user['tipo_usuario'],
    'redirect' => $redirect
]);
