<?php
/**
 * API: Sugerencias Inteligentes de Horarios
 */

require_once '../config.php';

if (!isAuthenticated()) {
    jsonResponse(false, 'No autenticado', null, 401);
    exit;
}

$db = getDatabase();
$action = $_GET['action'] ?? 'generar';

if ($action === 'generar') {
    // Generar sugerencias de horarios automáticas
    $grupo_id = $_GET['grupo_id'] ?? null;
    $materia_id = $_GET['materia_id'] ?? null;
    
    if (!$grupo_id || !$materia_id) {
        jsonResponse(false, 'Parámetros incompletos');
        exit;
    }
    
    $sugerencias = [];
    $dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes'];
    $horas = ['08:00', '10:00', '12:00', '14:00', '16:00'];
    
    // Obtener horarios ya asignados
    $query = "SELECT dia, hora_inicio, hora_fin FROM horarios WHERE grupo_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $grupo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $horariosOcupados = [];
    while ($row = $result->fetch_assoc()) {
        $horariosOcupados[] = $row['dia'] . '-' . $row['hora_inicio'];
    }
    
    // Generar sugerencias
    foreach ($dias as $dia) {
        foreach ($horas as $hora) {
            if (!in_array($dia . '-' . $hora, $horariosOcupados)) {
                $hora_fin = date('H:i', strtotime($hora . ' +1 hour'));
                $sugerencias[] = [
                    'dia' => $dia,
                    'hora_inicio' => $hora,
                    'hora_fin' => $hora_fin,
                    'disponible' => true
                ];
            }
        }
    }
    
    jsonResponse(true, 'Sugerencias generadas', array_slice($sugerencias, 0, 5));
}
elseif ($action === 'optimo') {
    // Encontrar el horario más óptimo según disponibilidad de docentes
    $grupo_id = $_GET['grupo_id'] ?? null;
    $materia_id = $_GET['materia_id'] ?? null;
    
    // Obtener docentes disponibles
    $query = "SELECT d.id, COUNT(d.id) as disponibilidades 
              FROM docentes d
              JOIN disponibilidad_horaria dh ON d.id = dh.docente_id
              WHERE dh.disponible = 1
              GROUP BY d.id
              ORDER BY disponibilidades DESC
              LIMIT 1";
    
    $result = $db->query($query);
    
    if ($row = $result->fetch_assoc()) {
        // Obtener sus horarios disponibles
        $docente_id = $row['id'];
        $query2 = "SELECT * FROM disponibilidad_horaria WHERE docente_id = ? AND disponible = 1 LIMIT 5";
        $stmt = $db->prepare($query2);
        $stmt->bind_param('i', $docente_id);
        $stmt->execute();
        $result2 = $stmt->get_result();
        
        $horarios = [];
        while ($h = $result2->fetch_assoc()) {
            $horarios[] = $h;
        }
        
        jsonResponse(true, 'Horario óptimo sugerido', [
            'docente_id' => $docente_id,
            'horarios_sugeridos' => $horarios
        ]);
    } else {
        jsonResponse(false, 'No hay docentes disponibles');
    }
}
elseif ($action === 'validar') {
    // Validar si un horario es posible asignar
    $docente_id = $_POST['docente_id'] ?? null;
    $grupo_id = $_POST['grupo_id'] ?? null;
    $dia = $_POST['dia'] ?? null;
    $hora_inicio = $_POST['hora_inicio'] ?? null;
    $hora_fin = $_POST['hora_fin'] ?? null;
    
    if (!$docente_id || !$grupo_id || !$dia || !$hora_inicio || !$hora_fin) {
        jsonResponse(false, 'Parámetros incompletos');
        exit;
    }
    
    // Verificar conflicto con docente
    $conflictos_docente = 0;
    $query = "SELECT COUNT(*) as count FROM horarios 
              WHERE docente_id = ? AND dia = ? 
              AND NOT (hora_fin <= ? OR hora_inicio >= ?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param('isss', $docente_id, $dia, $hora_inicio, $hora_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $conflictos_docente = $row['count'];
    }
    
    // Verificar conflicto con grupo
    $conflictos_grupo = 0;
    $query = "SELECT COUNT(*) as count FROM horarios 
              WHERE grupo_id = ? AND dia = ? 
              AND NOT (hora_fin <= ? OR hora_inicio >= ?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param('isss', $grupo_id, $dia, $hora_inicio, $hora_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $conflictos_grupo = $row['count'];
    }
    
    if ($conflictos_docente > 0 || $conflictos_grupo > 0) {
        jsonResponse(false, 'Conflicto encontrado', [
            'conflictos_docente' => $conflictos_docente,
            'conflictos_grupo' => $conflictos_grupo
        ]);
    } else {
        jsonResponse(true, 'Horario válido');
    }
}
else {
    jsonResponse(false, 'Acción no válida');
}
?>
