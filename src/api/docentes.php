<?php
/**
 * API de Docentes - Gestión completa
 */

require_once '../config.php';

if (!isAuthenticated()) {
    jsonResponse(false, 'No autorizado', null, 401);
}

$action = $_GET['action'] ?? '';
$user = getCurrentUser();

try {
    switch ($action) {
        case 'list':
            $docentes = (new Docente())->getActivosConUsuario();
            jsonResponse(true, '', $docentes);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            $docente = (new Docente())->getById($id);
            if (!$docente) jsonResponse(false, 'Docente no encontrado', null, 404);
            
            jsonResponse(true, '', $docente);
            break;

        case 'create':
            if ($user['tipo_usuario'] !== 'administrador') {
                jsonResponse(false, 'Solo administradores pueden crear docentes', null, 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validar datos requeridos
            $campos = ['nombre', 'apellido', 'email', 'especialidad', 'telefono'];
            foreach ($campos as $campo) {
                if (empty($data[$campo])) {
                    jsonResponse(false, "El campo $campo es requerido", null, 400);
                }
            }
            
            // Validar email único
            if ((new Usuario())->getByEmail($data['email'])) {
                jsonResponse(false, 'El email ya está registrado', null, 409);
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
            
            jsonResponse(true, 'Docente creado correctamente', ['id' => $docenteId]);
            break;

        case 'update':
            if ($user['tipo_usuario'] !== 'administrador') {
                jsonResponse(false, 'Solo administradores pueden editar docentes', null, 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            $docente = (new Docente())->getById($id);
            if (!$docente) jsonResponse(false, 'Docente no encontrado', null, 404);
            
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
            
            jsonResponse(true, 'Docente actualizado correctamente');
            break;

        case 'delete':
            if ($user['tipo_usuario'] !== 'administrador') {
                jsonResponse(false, 'Solo administradores pueden eliminar docentes', null, 403);
            }
            
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            (new Docente())->update($id, ['estado' => 'inactivo']);
            jsonResponse(true, 'Docente desactivado correctamente');
            break;

        default:
            jsonResponse(false, 'Acción no válida', null, 400);
    }
} catch (Exception $e) {
    logError($e);
    jsonResponse(false, 'Error en servidor', null, 500);
}
