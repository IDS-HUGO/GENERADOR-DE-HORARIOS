<?php
require_once '../../config.php';
require_once INCLUDES_PATH . '/Models.php';

initSession();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método no permitido', null, 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['email']) || empty($data['password'])) {
    jsonResponse(false, 'Email y contraseña requeridos', null, 400);
}

$email = sanitize($data['email']);
$password = $data['password'];

error_log('[LOGIN] Intentando: ' . $email);

if (!validateEmail($email)) {
    jsonResponse(false, 'Email inválido', null, 400);
}

try {
    $usuarioModel = new Usuario();
    $result = $usuarioModel->authenticate($email, $password);

    if (!$result['success']) {
        error_log('[LOGIN] Fallo: ' . $result['message']);
        jsonResponse(false, $result['message'], null, 401);
    }

    $user = $result['user'];

    $_SESSION['usuario_id'] = $user['usuario_id'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
    $_SESSION['login_time'] = time();

    // Construir URL de redirección dinámicamente según rol
    // APP_URL ya tiene la carpeta del proyecto
    // Ejemplo: http://localhost/ClassControl
    switch ($user['tipo_usuario']) {
        case 'director':
            $redirect = APP_URL . '/public/admin/dashboard.php?role=director';
            break;
        case 'administrador':
            $redirect = APP_URL . '/public/admin/dashboard.php';
            break;
        case 'docente':
            $redirect = APP_URL . '/public/docente/dashboard.php';
            break;
        default:
            $redirect = APP_URL . '/public/index.html';
    }

    error_log('[LOGIN] Éxito. Redirect: ' . $redirect);

    jsonResponse(true, 'Login exitoso', [
        'usuario_id' => $user['usuario_id'],
        'nombre' => $user['nombre'],
        'email' => $user['email'],
        'tipo_usuario' => $user['tipo_usuario'],
        'redirect' => $redirect
    ]);

} catch (Exception $e) {
    error_log('[LOGIN] Error: ' . $e->getMessage());
    jsonResponse(false, 'Error en servidor', null, 500);
}
?>
