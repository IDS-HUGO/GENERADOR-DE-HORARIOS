<?php
/**
 * API de Docentes - Gestión completa
 */

require_once '../../config.php';

if (!isAuthenticated()) {
    jsonResponse(['error' => 'No autorizado'], 401);
}

$action = $_GET['action'] ?? '';
$user = getCurrentUser();

try {
    switch ($action) {
        case 'list':
            // Listar todos los docentes
            $docentes = (new Docente())->getActivosConUsuario();
            jsonResponse(['success' => true, 'data' => $docentes]);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(['error' => 'ID requerido'], 400);
            
            $docente = (new Docente())->getById($id);
            if (!$docente) jsonResponse(['error' => 'Docente no encontrado'], 404);
            
            jsonResponse(['success' => true, 'data' => $docente]);
            break;

        case 'create':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(['error' => 'Solo administradores pueden crear docentes'], 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validar datos requeridos
            $campos = ['nombre', 'apellido', 'email', 'especialidad', 'telefono'];
            foreach ($campos as $campo) {
                if (empty($data[$campo])) {
                    jsonResponse(['error' => "El campo $campo es requerido"], 400);
                }
            }
            
            // Validar email único
            if ((new Usuario())->getByEmail($data['email'])) {
                jsonResponse(['error' => 'El email ya está registrado'], 409);
            }
            
            // Crear usuario
            $password = $data['password'] ?? 'Docente123!';
            $usuarioId = (new Usuario())->create([
                'email' => $data['email'],
                'password' => hashPassword($password),
                'tipo' => 'docente',
                'estado' => 'activo'
            ]);
            
            // Crear perfil docente
            $docenteId = (new Docente())->create([
                'usuario_id' => $usuarioId,
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'especialidad' => $data['especialidad'],
                'telefono' => $data['telefono'],
                'horas_asignadas' => $data['horas_asignadas'] ?? 0,
                'estado' => 'activo'
            ]);
            
            // Crear disponibilidad inicial
            (new DisponibilidadHoraria())->createInitialAvailability($docenteId);
            
            jsonResponse(['success' => true, 'id' => $docenteId, 'message' => 'Docente creado correctamente']);
            break;

        case 'update':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(['error' => 'Solo administradores pueden editar docentes'], 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            
            if (!$id) jsonResponse(['error' => 'ID requerido'], 400);
            
            $docente = (new Docente())->getById($id);
            if (!$docente) jsonResponse(['error' => 'Docente no encontrado'], 404);
            
            // Actualizar datos
            $updateData = array_filter([
                'nombre' => $data['nombre'] ?? null,
                'apellido' => $data['apellido'] ?? null,
                'especialidad' => $data['especialidad'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'horas_asignadas' => $data['horas_asignadas'] ?? null
            ], fn($v) => $v !== null);
            
            if (!empty($updateData)) {
                (new Docente())->update($id, $updateData);
            }
            
            jsonResponse(['success' => true, 'message' => 'Docente actualizado correctamente']);
            break;

        case 'delete':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(['error' => 'Solo administradores pueden eliminar docentes'], 403);
            }
            
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(['error' => 'ID requerido'], 400);
            
            (new Docente())->update($id, ['estado' => 'inactivo']);
            jsonResponse(['success' => true, 'message' => 'Docente desactivado correctamente']);
            break;

        default:
            jsonResponse(['error' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    logError($e);
    jsonResponse(['error' => 'Error en servidor'], 500);
}
