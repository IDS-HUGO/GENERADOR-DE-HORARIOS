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
$esDirector = $user['tipo_usuario'] === 'director';
$esAdmin = $user['tipo_usuario'] === 'administrador';

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

        case 'gestion':
            // Vista completa para gestión (admin/director)
            if (!$esAdmin && !$esDirector) {
                jsonResponse(false, 'No autorizado', null, 403);
            }
            $filtros = [];
            if (isset($_GET['grupo_id'])) $filtros['grupo_id'] = $_GET['grupo_id'];
            if (isset($_GET['docente_id'])) $filtros['docente_id'] = $_GET['docente_id'];

            $horarios = (new Horario())->getActivos($filtros);
            jsonResponse(true, 'Listado de horarios con contexto', $horarios);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);

            $horario = (new Horario())->getById($id);
            if (!$horario) jsonResponse(false, 'Horario no encontrado', null, 404);

            jsonResponse(true, '', $horario);
            break;

        case 'create':
            // Crear horario (requiere asignación previa)
            if (!$esAdmin && !$esDirector) {
                jsonResponse(false, 'No autorizado', null, 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            $campos = ['asignacion_id', 'dia_semana', 'hora_inicio', 'hora_fin'];
            foreach ($campos as $campo) {
                if (empty($data[$campo])) {
                    jsonResponse(false, "Campo $campo requerido", null, 400);
                }
            }

            try {
                $horarioId = (new Horario())->create([
                    'asignacion_id' => (int)$data['asignacion_id'],
                    'dia_semana' => $data['dia_semana'],
                    'hora_inicio' => $data['hora_inicio'],
                    'hora_fin' => $data['hora_fin'],
                    'aula_id' => $data['aula_id'] ?? null,
                    'estado_horario' => $data['estado_horario'] ?? 'pendiente',
                    'confirmado' => 0,
                    'conflicto_detectado' => (int)($data['conflicto_detectado'] ?? 0),
                    'observaciones' => $data['observaciones'] ?? null
                ]);

                jsonResponse(true, 'Horario creado', ['id' => $horarioId]);
            } catch (Exception $e) {
                error_log('[HORARIO CREATE ERROR] ' . $e->getMessage());
                jsonResponse(false, 'Error: ' . $e->getMessage(), null, 500);
            }
            break;

        case 'update':
            // Actualizar horario
            if (!$esAdmin && !$esDirector) {
                jsonResponse(false, 'No autorizado', null, 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;

            if (!$id) jsonResponse(false, 'ID requerido', null, 400);

            $updateData = array_filter([
                'aula_id' => $data['aula_id'] ?? null,
                'hora_inicio' => $data['hora_inicio'] ?? null,
                'hora_fin' => $data['hora_fin'] ?? null,
                'estado_horario' => $data['estado_horario'] ?? null,
                'confirmado' => $data['confirmado'] ?? null,
                'conflicto_detectado' => $data['conflicto_detectado'] ?? null,
                'observaciones' => $data['observaciones'] ?? null
            ], fn($v) => $v !== null);

            if (!empty($updateData)) {
                (new Horario())->update($id, $updateData);
            }

            jsonResponse(true, 'Horario actualizado');
            break;

        case 'approve':
            // Aprobar/Rechazar horario (director/admin)
            if (!$esAdmin && !$esDirector) {
                jsonResponse(false, 'No autorizado', null, 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $horario_id = $data['id'] ?? null;
            $estado = $data['estado'] ?? 'aprobado';
            $observaciones = $data['observaciones'] ?? null;

            if (!$horario_id) jsonResponse(false, 'ID requerido', null, 400);

            (new Horario())->aprobarHorario($horario_id, $user['usuario_id'], $esDirector ? 'director' : 'administrador', $estado, $observaciones);
            jsonResponse(true, 'Horario actualizado');
            break;

        case 'delete':
            // Eliminar horario
            if (!$esAdmin && !$esDirector) {
                jsonResponse(false, 'No autorizado', null, 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);

            (new Horario())->delete($id);
            jsonResponse(true, 'Horario eliminado');
            break;

        default:
            jsonResponse(false, 'Acción no válida', null, 400);
    }
} catch (Exception $e) {
    logError($e);
    jsonResponse(false, 'Error en servidor', null, 500);
}
