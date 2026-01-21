<?php
/**
 * API de Grupos - Gestión completa
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
            $programa_id = $_GET['programa_id'] ?? null;
            
            if ($programa_id) {
                $grupos = (new Grupo())->getByPrograma($programa_id);
            } else {
                $grupos = (new Grupo())->getActivos();
            }
            
            jsonResponse(['success' => true, 'data' => $grupos]);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(['error' => 'ID requerido'], 400);
            
            $grupo = (new Grupo())->getById($id);
            if (!$grupo) jsonResponse(['error' => 'Grupo no encontrado'], 404);
            
            jsonResponse(['success' => true, 'data' => $grupo]);
            break;

        case 'create':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(['error' => 'Solo administradores pueden crear grupos'], 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $campos = ['codigo', 'nombre', 'programa_id', 'semestre', 'cantidad_estudiantes'];
            foreach ($campos as $campo) {
                if (empty($data[$campo]) && $data[$campo] !== 0) {
                    jsonResponse(['error' => "El campo $campo es requerido"], 400);
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
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Grupo creado correctamente']);
            break;

        case 'update':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(['error' => 'Solo administradores pueden editar grupos'], 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            
            if (!$id) jsonResponse(['error' => 'ID requerido'], 400);
            
            if (!(new Grupo())->getById($id)) {
                jsonResponse(['error' => 'Grupo no encontrado'], 404);
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
            
            jsonResponse(['success' => true, 'message' => 'Grupo actualizado correctamente']);
            break;

        case 'delete':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(['error' => 'Solo administradores pueden eliminar grupos'], 403);
            }
            
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(['error' => 'ID requerido'], 400);
            
            (new Grupo())->update($id, ['estado' => 'inactivo']);
            jsonResponse(['success' => true, 'message' => 'Grupo desactivado correctamente']);
            break;

        default:
            jsonResponse(['error' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    logError($e);
    jsonResponse(['error' => 'Error en servidor'], 500);
}
