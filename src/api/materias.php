<?php
/**
 * API de Materias - Gestión completa
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
                $materias = (new Materia())->getByPrograma($programa_id);
            } else {
                $materias = (new Materia())->getAll();
            }
            
            jsonResponse(['success' => true, 'data' => $materias]);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(['error' => 'ID requerido'], 400);
            
            $materia = (new Materia())->getById($id);
            if (!$materia) jsonResponse(['error' => 'Materia no encontrada'], 404);
            
            jsonResponse(['success' => true, 'data' => $materia]);
            break;

        case 'create':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(['error' => 'Solo administradores pueden crear materias'], 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $campos = ['codigo', 'nombre', 'programa_id', 'creditos', 'semestre'];
            foreach ($campos as $campo) {
                if (empty($data[$campo])) {
                    jsonResponse(['error' => "El campo $campo es requerido"], 400);
                }
            }
            
            // Validar código único
            if ((new Materia())->getByCodigo($data['codigo'])) {
                jsonResponse(['error' => 'El código de materia ya existe'], 409);
            }
            
            $id = (new Materia())->create([
                'codigo' => $data['codigo'],
                'nombre' => $data['nombre'],
                'programa_id' => $data['programa_id'],
                'creditos' => $data['creditos'],
                'semestre' => $data['semestre'],
                'obligatoria' => $data['obligatoria'] ?? true,
                'descripcion' => $data['descripcion'] ?? null,
                'estado' => 'activo'
            ]);
            
            jsonResponse(['success' => true, 'id' => $id, 'message' => 'Materia creada correctamente']);
            break;

        case 'update':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(['error' => 'Solo administradores pueden editar materias'], 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            
            if (!$id) jsonResponse(['error' => 'ID requerido'], 400);
            
            if (!(new Materia())->getById($id)) {
                jsonResponse(['error' => 'Materia no encontrada'], 404);
            }
            
            $updateData = array_filter([
                'nombre' => $data['nombre'] ?? null,
                'creditos' => $data['creditos'] ?? null,
                'semestre' => $data['semestre'] ?? null,
                'obligatoria' => $data['obligatoria'] ?? null,
                'descripcion' => $data['descripcion'] ?? null
            ], fn($v) => $v !== null);
            
            if (!empty($updateData)) {
                (new Materia())->update($id, $updateData);
            }
            
            jsonResponse(['success' => true, 'message' => 'Materia actualizada correctamente']);
            break;

        case 'delete':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(['error' => 'Solo administradores pueden eliminar materias'], 403);
            }
            
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(['error' => 'ID requerido'], 400);
            
            (new Materia())->update($id, ['estado' => 'inactivo']);
            jsonResponse(['success' => true, 'message' => 'Materia desactivada correctamente']);
            break;

        default:
            jsonResponse(['error' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    logError($e);
    jsonResponse(['error' => 'Error en servidor'], 500);
}
