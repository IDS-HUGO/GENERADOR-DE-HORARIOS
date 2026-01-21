<?php
/**
 * API de Programas Académicos - Gestión completa
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
            $programas = (new ProgramaAcademico())->getActivos();
            jsonResponse(['success' => true, 'data' => $programas]);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(['error' => 'ID requerido'], 400);
            
            $programa = (new ProgramaAcademico())->getById($id);
            if (!$programa) jsonResponse(['error' => 'Programa no encontrado'], 404);
            
            jsonResponse(['success' => true, 'data' => $programa]);
            break;

        case 'create':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(['error' => 'Solo administradores pueden crear programas'], 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $campos = ['codigo', 'nombre', 'nivel'];
            foreach ($campos as $campo) {
                if (empty($data[$campo])) {
                    jsonResponse(['error' => "El campo $campo es requerido"], 400);
                }
            }
            
            if ((new ProgramaAcademico())->getByCodigo($data['codigo'])) {
                jsonResponse(['error' => 'El código del programa ya existe'], 409);
            }
            
            $id = (new ProgramaAcademico())->create([
                'codigo' => $data['codigo'],
                'nombre' => $data['nombre'],
                'nivel' => $data['nivel'],
                'duracion_semestres' => $data['duracion_semestres'] ?? 8,
                'descripcion' => $data['descripcion'] ?? null,
                'estado' => 'activo'
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Programa creado correctamente']);
            break;

        case 'update':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(['error' => 'Solo administradores pueden editar programas'], 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            
            if (!$id) jsonResponse(['error' => 'ID requerido'], 400);
            
            if (!(new ProgramaAcademico())->getById($id)) {
                jsonResponse(['error' => 'Programa no encontrado'], 404);
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
            
            jsonResponse(['success' => true, 'message' => 'Programa actualizado correctamente']);
            break;

        case 'delete':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(['error' => 'Solo administradores pueden eliminar programas'], 403);
            }
            
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(['error' => 'ID requerido'], 400);
            
            (new ProgramaAcademico())->update($id, ['estado' => 'inactivo']);
            jsonResponse(['success' => true, 'message' => 'Programa desactivado correctamente']);
            break;

        default:
            jsonResponse(['error' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    logError($e);
    jsonResponse(['error' => 'Error en servidor'], 500);
}
