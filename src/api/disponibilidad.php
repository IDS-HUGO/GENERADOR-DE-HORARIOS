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
$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    // Obtener disponibilidades del usuario actual
    $query = "SELECT * FROM disponibilidad_horaria WHERE docente_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $usuario['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $disponibilidades = [];
    
    while ($row = $result->fetch_assoc()) {
        $disponibilidades[] = $row;
    }
    
    jsonResponse(true, 'Disponibilidades obtenidas', $disponibilidades);
} 
elseif ($action === 'update') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Método no permitido');
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos
    if (empty($data['dia']) || empty($data['hora_inicio']) || empty($data['hora_fin'])) {
        jsonResponse(false, 'Datos incompletos');
        exit;
    }
    
    $docente_id = $usuario['usuario_id'];
    $dia = $data['dia'];
    $hora_inicio = $data['hora_inicio'];
    $hora_fin = $data['hora_fin'];
    $disponible = $data['disponible'] ?? 1;
    
    // Verificar si ya existe
    $check = $db->prepare("SELECT id FROM disponibilidad_horaria WHERE docente_id = ? AND dia = ?");
    $check->bind_param('is', $docente_id, $dia);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();
    
    if ($existing) {
        // Actualizar
        $update = $db->prepare("UPDATE disponibilidad_horaria SET hora_inicio = ?, hora_fin = ?, disponible = ? WHERE id = ?");
        $update->bind_param('ssii', $hora_inicio, $hora_fin, $disponible, $existing['id']);
        $result = $update->execute();
    } else {
        // Insertar
        $insert = $db->prepare("INSERT INTO disponibilidad_horaria (docente_id, dia, hora_inicio, hora_fin, disponible) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param('isssi', $docente_id, $dia, $hora_inicio, $hora_fin, $disponible);
        $result = $insert->execute();
    }
    
    if ($result) {
        logInfo("Disponibilidad actualizada para docente: $docente_id");
        jsonResponse(true, 'Disponibilidad actualizada');
    } else {
        jsonResponse(false, 'Error al actualizar disponibilidad');
    }
}
elseif ($action === 'sugerir') {
    // Sugerir horarios disponibles
    $docente_id = $_GET['docente_id'] ?? $usuario['usuario_id'];
    
    $query = "SELECT * FROM disponibilidad_horaria WHERE docente_id = ? AND disponible = 1";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $docente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $disponibilidades = [];
    while ($row = $result->fetch_assoc()) {
        $disponibilidades[] = $row;
    }
    
    if (empty($disponibilidades)) {
        jsonResponse(true, 'Sin disponibilidades registradas', []);
    } else {
        jsonResponse(true, 'Horarios sugeridos', $disponibilidades);
    }
}
else {
    jsonResponse(false, 'Acción no válida');
}
?>
