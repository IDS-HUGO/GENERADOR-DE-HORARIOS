<?php
/**
 * API de Horarios - Gestión completa
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
            // Listar horarios
            $filtros = [];
            if (isset($_GET['grupo_id'])) $filtros['grupo_id'] = $_GET['grupo_id'];
            if (isset($_GET['docente_id'])) $filtros['docente_id'] = $_GET['docente_id'];

            $horarios = (new Horario())->getActivos($filtros);
            jsonResponse(true, '', $horarios);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);

            $horario = (new Horario())->getById($id);
            if (!$horario) jsonResponse(false, 'Horario no encontrado', null, 404);

            jsonResponse(true, '', $horario);
            break;

        case 'create':
            // Crear horario
            if ($user['tipo'] !== 'admin' && $user['tipo'] !== 'coordinador') {
                jsonResponse(false, 'No autorizado', null, 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            $campos = ['grupo_id', 'materia_id', 'docente_id', 'aula', 'dia_semana', 'hora_inicio', 'hora_fin'];
            foreach ($campos as $campo) {
                if (empty($data[$campo])) {
                    jsonResponse(false, "Campo $campo requerido", null, 400);
                }
            }

            $horarioId = (new Horario())->create([
                'grupo_id' => $data['grupo_id'],
                'materia_id' => $data['materia_id'],
                'docente_id' => $data['docente_id'],
                'aula' => $data['aula'],
                'dia_semana' => $data['dia_semana'],
                'hora_inicio' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
                'estado' => 'activo'
            ]);

            jsonResponse(true, 'Horario creado', ['id' => $horarioId]);
            break;

        case 'update':
            // Actualizar horario
            if ($user['tipo'] !== 'admin' && $user['tipo'] !== 'coordinador') {
                jsonResponse(false, 'No autorizado', null, 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) jsonResponse(false, 'ID requerido', null, 400);

            $updateData = array_filter([
                'aula' => $data['aula'] ?? null,
                'hora_inicio' => $data['hora_inicio'] ?? null,
                'hora_fin' => $data['hora_fin'] ?? null,
                'estado' => $data['estado'] ?? null
            ], fn($v) => $v !== null);

            if (!empty($updateData)) {
                (new Horario())->update($id, $updateData);
            }

            jsonResponse(true, 'Horario actualizado');
            break;

        case 'delete':
            // Eliminar horario
            if ($user['tipo'] !== 'admin') {
                jsonResponse(false, 'No autorizado', null, 403);
            }

            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);

            (new Horario())->update($id, ['estado' => 'inactivo']);
            jsonResponse(true, 'Horario eliminado');
            break;

        default:
            jsonResponse(false, 'Acción no válida', null, 400);
    }
} catch (Exception $e) {
    logError($e);
    jsonResponse(false, 'Error en servidor', null, 500);
}
