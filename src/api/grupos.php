<?php
/**
 * API de Grupos - Gestión completa
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
            $programa_id = $_GET['programa_id'] ?? null;
            
            if ($programa_id) {
                $grupos = (new Grupo())->getByPrograma($programa_id);
            } else {
                $grupos = (new Grupo())->getActivos();
            }
            
            jsonResponse(true, '', $grupos);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            $grupo = (new Grupo())->getById($id);
            if (!$grupo) jsonResponse(false, 'Grupo no encontrado', null, 404);
            
            jsonResponse(true, '', $grupo);
            break;

        case 'create':
            if ($user['tipo_usuario'] !== 'administrador') {
                jsonResponse(false, 'Solo administradores pueden crear grupos', null, 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $campos = ['codigo', 'nombre', 'programa_id', 'semestre', 'cantidad_estudiantes'];
            foreach ($campos as $campo) {
                if (empty($data[$campo]) && $data[$campo] !== 0) {
                    jsonResponse(false, "El campo $campo es requerido", null, 400);
                }
            }
            
            $id = (new Grupo())->create([
                'codigo' => $data['codigo'],
                'nombre' => $data['nombre'],
                'programa_id' => $data['programa_id'],
                'semestre' => $data['semestre'],
                'cantidad_estudiantes' => $data['cantidad_estudiantes'],
                'jornada' => $data['jornada'] ?? 'matutina',
                'descripcion' => $data['descripcion'] ?? null,
                'estado' => 'activo'
            ]);
            
            jsonResponse(true, 'Grupo creado correctamente', ['id' => $id]);
            break;

        case 'update':
            if ($user['tipo_usuario'] !== 'administrador') {
                jsonResponse(false, 'Solo administradores pueden editar grupos', null, 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            if (!(new Grupo())->getById($id)) {
                jsonResponse(false, 'Grupo no encontrado', null, 404);
            }
            
            $updateData = array_filter([
                'nombre' => $data['nombre'] ?? null,
                'semestre' => $data['semestre'] ?? null,
                'cantidad_estudiantes' => $data['cantidad_estudiantes'] ?? null,
                'jornada' => $data['jornada'] ?? null,
                'descripcion' => $data['descripcion'] ?? null
            ], fn($v) => $v !== null);
            
            if (!empty($updateData)) {
                (new Grupo())->update($id, $updateData);
            }
            
            jsonResponse(true, 'Grupo actualizado correctamente');
            break;

        case 'delete':
            if ($user['tipo_usuario'] !== 'administrador') {
                jsonResponse(false, 'Solo administradores pueden eliminar grupos', null, 403);
            }
            
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            (new Grupo())->update($id, ['estado' => 'inactivo']);
            jsonResponse(true, 'Grupo desactivado correctamente');
            break;

        default:
            jsonResponse(false, 'Acción no válida', null, 400);
    }
} catch (Exception $e) {
    logError($e);
    jsonResponse(false, 'Error en servidor', null, 500);
}
