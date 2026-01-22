<?php
/**
 * API de Materias - Gestión completa
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
                $materias = (new Materia())->getByPrograma($programa_id);
            } else {
                $materias = (new Materia())->getAll();
            }
            
            jsonResponse(true, '', $materias);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            $materia = (new Materia())->getById($id);
            if (!$materia) jsonResponse(false, 'Materia no encontrada', null, 404);
            
            jsonResponse(true, '', $materia);
            break;

        case 'create':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(false, 'Solo administradores pueden crear materias', null, 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            $campos = ['codigo', 'nombre', 'programa_id', 'creditos', 'semestre'];
            foreach ($campos as $campo) {
                if (empty($data[$campo])) {
                    jsonResponse(false, "El campo $campo es requerido", null, 400);
                }
            }
            
            // Validar código único
            if ((new Materia())->getByCodigo($data['codigo'])) {
                jsonResponse(false, 'El código de materia ya existe', null, 409);
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
            
            jsonResponse(true, 'Materia creada correctamente', ['id' => $id]);
            break;

        case 'update':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(false, 'Solo administradores pueden editar materias', null, 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            if (!(new Materia())->getById($id)) {
                jsonResponse(false, 'Materia no encontrada', null, 404);
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
            
            jsonResponse(true, 'Materia actualizada correctamente');
            break;

        case 'delete':
            if ($user['tipo'] !== 'admin') {
                jsonResponse(false, 'Solo administradores pueden eliminar materias', null, 403);
            }
            
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            (new Materia())->update($id, ['estado' => 'inactivo']);
            jsonResponse(true, 'Materia desactivada correctamente');
            break;

        default:
            jsonResponse(false, 'Acción no válida', null, 400);
    }
} catch (Exception $e) {
    logError($e);
    jsonResponse(false, 'Error en servidor', null, 500);
}
