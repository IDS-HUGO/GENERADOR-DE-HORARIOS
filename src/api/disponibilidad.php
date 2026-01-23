<?php
/**
 * API: Disponibilidad Horaria
 */

require_once '../config.php';

if (!isAuthenticated()) {
    jsonResponse(false, 'No autenticado', null, 401);
    exit;
}

$db = getDatabase();
$usuario = getCurrentUser();
$docenteModel = new Docente();
$dispoModel = new DisponibilidadHoraria();
$docente = $docenteModel->getByUserId($usuario['usuario_id']);

if (!$docente) {
    jsonResponse(false, 'Perfil de docente no encontrado', null, 404);
}

$docenteId = (int)$docente['docente_id'];
$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $disponibilidades = $dispoModel->getByDocente($docenteId);
    jsonResponse(true, 'Disponibilidades obtenidas', $disponibilidades);
}
elseif ($action === 'update') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Método no permitido', null, 405);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    // Construir payload de días
    $diasPayload = [];
    $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];

    // Caso: payload compacto {dia_semana, hora_inicio, hora_fin}
    if (!empty($data['dia_semana']) || !empty($data['dia'])) {
        $diasPayload[] = [
            'dia_semana' => $data['dia_semana'] ?? $data['dia'],
            'hora_inicio' => $data['hora_inicio'] ?? null,
            'hora_fin' => $data['hora_fin'] ?? null,
            'disponible' => $data['disponible'] ?? 1,
            'tipo_disponibilidad' => $data['tipo_disponibilidad'] ?? 'disponible'
        ];
    }

    // Caso: formulario por día (lunes_inicio, lunes_fin, ...)
    if (isset($data['lunes_inicio'])) {
        $diasPayload = [];
        foreach ($diasSemana as $dia) {
            $inicio = $data[$dia . '_inicio'] ?? null;
            $fin = $data[$dia . '_fin'] ?? null;
            if ($inicio && $fin) {
                $diasPayload[] = [
                    'dia_semana' => $dia,
                    'hora_inicio' => $inicio,
                    'hora_fin' => $fin,
                    'disponible' => 1,
                    'tipo_disponibilidad' => 'disponible'
                ];
            }
        }
    }

    // Caso: arreglo explícito de días
    if (isset($data['dias']) && is_array($data['dias'])) {
        $diasPayload = $data['dias'];
    }

    if (empty($diasPayload)) {
        jsonResponse(false, 'Datos de disponibilidad incompletos', null, 400);
    }

    // Upsert por día
    foreach ($diasPayload as $slot) {
        $diaSemana = $slot['dia_semana'] ?? null;
        $inicio = $slot['hora_inicio'] ?? null;
        $fin = $slot['hora_fin'] ?? null;
        $disponible = isset($slot['disponible']) ? (int)$slot['disponible'] : 1;
        $tipo = $slot['tipo_disponibilidad'] ?? 'disponible';

        if (!$diaSemana || !$inicio || !$fin) {
            jsonResponse(false, 'Cada día debe tener dia_semana, hora_inicio y hora_fin', null, 400);
        }

        $check = $db->prepare("SELECT disponibilidad_id FROM disponibilidad_horaria WHERE docente_id = ? AND dia_semana = ?");
        $check->bind_param('is', $docenteId, $diaSemana);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        $check->close();

        if ($existing) {
            $update = $db->prepare("UPDATE disponibilidad_horaria SET hora_inicio = ?, hora_fin = ?, disponible = ?, tipo_disponibilidad = ? WHERE disponibilidad_id = ?");
            $update->bind_param('ssisi', $inicio, $fin, $disponible, $tipo, $existing['disponibilidad_id']);
            $update->execute();
            $update->close();
        } else {
            $insert = $db->prepare("INSERT INTO disponibilidad_horaria (docente_id, dia_semana, hora_inicio, hora_fin, disponible, tipo_disponibilidad) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->bind_param('isssis', $docenteId, $diaSemana, $inicio, $fin, $disponible, $tipo);
            $insert->execute();
            $insert->close();
        }
    }

    // Actualizar horas máximas si se envían
    if (!empty($data['horas_maximas'])) {
        $docenteModel->update($docenteId, ['horas_maximas_semanales' => (int)$data['horas_maximas']]);
    }

    $actualizada = $dispoModel->getByDocente($docenteId);
    logInfo("Disponibilidad actualizada para docente: $docenteId");
    jsonResponse(true, 'Disponibilidad actualizada', $actualizada);
}
elseif ($action === 'sugerir') {
    $disponibles = $dispoModel->getByDocente($docenteId);
    $filtradas = array_values(array_filter($disponibles, fn($d) => !empty($d['disponible'])));
    jsonResponse(true, 'Horarios sugeridos', $filtradas);
}
else {
    jsonResponse(false, 'Acción no válida', null, 400);
}
?>
