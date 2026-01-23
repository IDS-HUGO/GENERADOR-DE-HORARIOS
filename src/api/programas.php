<?php
/**
 * API de Programas Académicos - Gestión completa
 */

require_once '../config.php';
require_once INCLUDES_PATH . '/Models.php';

if (!isAuthenticated()) {
    jsonResponse(false, 'No autorizado', null, 401);
}

$action = $_GET['action'] ?? '';
$user = getCurrentUser();

try {
    switch ($action) {
        case 'list':
            $programas = (new ProgramaAcademico())->getActivos();
            jsonResponse(true, '', $programas);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            $programa = (new ProgramaAcademico())->getById($id);
            if (!$programa) jsonResponse(false, 'Programa no encontrado', null, 404);
            
            jsonResponse(true, '', $programa);
            break;

        case 'create':
            if ($user['tipo_usuario'] !== 'administrador') {
                jsonResponse(false, 'Solo administradores pueden crear programas', null, 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $campos = ['codigo', 'nombre'];
            foreach ($campos as $campo) {
                if (empty($data[$campo])) {
                    jsonResponse(false, "El campo $campo es requerido", null, 400);
                }
            }
            
            if ((new ProgramaAcademico())->getByCodigo($data['codigo'])) {
                jsonResponse(false, 'El código del programa ya existe', null, 409);
            }
            
            try {
                $id = (new ProgramaAcademico())->create([
                    'codigo' => $data['codigo'],
                    'nombre' => $data['nombre'],
                    'nivel' => $data['nivel'] ?? 'profesional',
                    'modalidad' => $data['modalidad'] ?? 'presencial',
                    'duracion_semestres' => (int)($data['duracion_semestres'] ?? 8),
                    'descripcion' => $data['descripcion'] ?? null,
                    'estado' => 'activo'
                ]);
                
                jsonResponse(true, 'Programa creado correctamente', ['id' => $id]);
            } catch (Exception $e) {
                error_log('[PROGRAMA CREATE ERROR] ' . $e->getMessage());
                jsonResponse(false, 'Error: ' . $e->getMessage(), null, 500);
            }
            break;            jsonResponse(true, 'Programa creado correctamente', ['id' => $id]);
            break;

        case 'update':
            if ($user['tipo_usuario'] !== 'administrador') {
                jsonResponse(false, 'Solo administradores pueden editar programas', null, 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            if (!(new ProgramaAcademico())->getById($id)) {
                jsonResponse(false, 'Programa no encontrado', null, 404);
            }
            
            $updateData = array_filter([
                'nombre' => $data['nombre'] ?? null,
                'nivel' => $data['nivel'] ?? null,
                'duracion_semestres' => $data['duracion_semestres'] ?? null,
                'descripcion' => $data['descripcion'] ?? null
            ], fn($v) => $v !== null);
            
            if (!empty($updateData)) {
                (new ProgramaAcademico())->update($id, $updateData);
            }
            
            jsonResponse(true, 'Programa actualizado correctamente');
            break;

        case 'delete':
            if ($user['tipo_usuario'] !== 'administrador') {
                jsonResponse(false, 'Solo administradores pueden eliminar programas', null, 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            (new ProgramaAcademico())->update($id, ['estado' => 'inactivo']);
            jsonResponse(true, 'Programa desactivado correctamente');
            break;

        default:
            jsonResponse(false, 'Acción no válida', null, 400);
    }
} catch (Exception $e) {
    logError($e);
    jsonResponse(false, 'Error en servidor', null, 500);
}
