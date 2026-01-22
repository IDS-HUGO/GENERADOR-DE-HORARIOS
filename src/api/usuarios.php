<?php
/**
 * API de Usuarios - Gestión completa
 */

require_once '../config.php';

if (!isAuthenticated()) {
    jsonResponse(false, 'No autorizado', null, 401);
}

$action = $_GET['action'] ?? '';
$user = getCurrentUser();

try {
    switch ($action) {
        case 'current':
            // Obtener usuario actual
            jsonResponse(true, '', $user);
            break;

        case 'list':
            // Listar usuarios (solo admin)
            if ($user['tipo'] !== 'admin') {
                jsonResponse(false, 'No autorizado', null, 403);
            }

            $filtro = [];
            if (isset($_GET['tipo'])) $filtro['tipo'] = $_GET['tipo'];
            if (isset($_GET['estado'])) $filtro['estado'] = $_GET['estado'];

            $usuarios = (new Usuario())->getAll($filtro);
            jsonResponse(true, '', $usuarios);
            break;

        case 'search':
            // Buscar usuario por email
            $email = $_GET['email'] ?? null;
            if (!$email) jsonResponse(false, 'Email requerido', null, 400);

            $usuario = (new Usuario())->getByEmail($email);
            jsonResponse(true, '', $usuario);
            break;

        case 'create':
            // Crear usuario (solo admin)
            if ($user['tipo'] !== 'admin') {
                jsonResponse(false, 'No autorizado', null, 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validar datos
            if (empty($data['email']) || empty($data['nombre'])) {
                jsonResponse(false, 'Email y nombre requeridos', null, 400);
            }

            if ((new Usuario())->getByEmail($data['email'])) {
                jsonResponse(false, 'Email ya existe', null, 409);
            }

            // Crear usuario
            $password = $data['password'] ?? 'Usuario123!';
            $usuarioId = (new Usuario())->create([
                'email' => $data['email'],
                'password' => hashPassword($password),
                'tipo' => $data['tipo'] ?? 'docente',
                'estado' => 'activo'
            ]);

            jsonResponse(true, 'Usuario creado', ['id' => $usuarioId, 'temporal_password' => $password]);
            break;

        case 'update':
            // Actualizar usuario
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) jsonResponse(false, 'ID requerido', null, 400);

            // Validar permisos
            if ($user['tipo'] !== 'admin' && $user['id'] != $id) {
                jsonResponse(false, 'No puedes editar otro usuario', null, 403);
            }

            $updateData = array_filter([
                'tipo' => $data['tipo'] ?? null,
                'estado' => $data['estado'] ?? null
            ], fn($v) => $v !== null);

            if (!empty($updateData)) {
                (new Usuario())->update($id, $updateData);
            }

            jsonResponse(true, 'Usuario actualizado');
            break;

        case 'delete':
            // Desactivar usuario (soft delete)
            if ($user['tipo'] !== 'admin') {
                jsonResponse(false, 'No autorizado', null, 403);
            }

            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);

            (new Usuario())->update($id, ['estado' => 'inactivo']);
            jsonResponse(true, 'Usuario desactivado');
            break;

        case 'reset_password':
            // Resetear contraseña
            if ($user['tipo'] !== 'admin') {
                jsonResponse(false, 'No autorizado', null, 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) jsonResponse(false, 'ID requerido', null, 400);

            $newPassword = 'Temporal' . rand(1000, 9999) . '!';
            (new Usuario())->update($id, ['password' => hashPassword($newPassword)]);

            jsonResponse(true, 'Contraseña reseteada', ['temporal_password' => $newPassword]);
            break;

        default:
            jsonResponse(false, 'Acción no válida', null, 400);
    }
} catch (Exception $e) {
    logError($e);
    jsonResponse(false, 'Error en servidor', null, 500);
}
