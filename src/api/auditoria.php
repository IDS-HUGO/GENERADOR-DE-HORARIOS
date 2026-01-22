<?php
/**
 * API de Auditoría - Registro de cambios
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
            // Listar registros de auditoría (solo admin)
            if ($user['tipo_usuario'] !== 'administrador') {
                jsonResponse(false, 'No autorizado', null, 403);
            }

            $filtros = [];
            if (isset($_GET['usuario_id'])) $filtros['usuario_id'] = $_GET['usuario_id'];
            if (isset($_GET['entidad'])) $filtros['entidad'] = $_GET['entidad'];
            if (isset($_GET['accion'])) $filtros['accion'] = $_GET['accion'];

            $registros = (new Auditoria())->obtener($filtros);
            jsonResponse(true, '', $registros);
            break;

        case 'registrar':
            // Registrar cambio (use internamente)
            $data = json_decode(file_get_contents('php://input'), true);

            $auditoria = new Auditoria();
            $auditoria->registrar([
                'usuario_id' => $user['id'],
                'entidad' => $data['entidad'],
                'entidad_id' => $data['entidad_id'],
                'accion' => $data['accion'],
                'cambios' => $data['cambios'] ?? null,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);

            jsonResponse(true, 'Registro creado');
            break;

        case 'resumen':
            // Resumen de auditoría
            if ($user['tipo_usuario'] !== 'administrador') {
                jsonResponse(false, 'No autorizado', null, 403);
            }

            $auditoria = new Auditoria();
            $resumen = [
                'total_cambios' => $auditoria->contar(),
                'cambios_hoy' => $auditoria->contarPorFecha(date('Y-m-d')),
                'cambios_semana' => $auditoria->contarPorPeriodo(7),
                'usuarios_activos' => $auditoria->usuariosActivos()
            ];

            jsonResponse(true, '', $resumen);
            break;

        default:
            jsonResponse(false, 'Acción no válida', null, 400);
    }
} catch (Exception $e) {
    logError($e);
    jsonResponse(false, 'Error en servidor', null, 500);
}
