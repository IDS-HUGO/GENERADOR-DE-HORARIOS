<?php
/**
 * API de Selección de Grupos y Materias por Docentes
 * Sistema de Gestión de Horarios - Universidad Maya
 * Copyright (c) 2026 Universidad Maya - Todos los derechos reservados
 */

require_once '../config.php';
require_once '../includes/Models.php';

header('Content-Type: application/json');
initSession();

// Verificar autenticación
$user = getCurrentUser();
if (!$user || $user['tipo_usuario'] !== 'docente') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$usuario_id = $user['usuario_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    $docente_model = new Docente();
    $usuario_model = new Usuario();
    
    // Obtener docente_id del usuario
    $docente = $docente_model->getByUserId($usuario_id);
    if (!$docente) {
        throw new Exception('Docente no encontrado');
    }
    $docente_id = $docente['docente_id'];
    
    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? '';
            
            switch ($action) {
                case 'grupos':
                    // Obtener grupos disponibles
                    $grupos = $docente_model->getGruposDisponibles($docente_id);
                    echo json_encode(['success' => true, 'data' => $grupos]);
                    break;
                    
                case 'materias':
                    // Obtener materias de un grupo específico
                    $grupo_id = $_GET['grupo_id'] ?? null;
                    if (!$grupo_id) {
                        throw new Exception('grupo_id requerido');
                    }
                    $materias = $docente_model->getMateriasDisponiblesPorGrupo($grupo_id, $docente_id);
                    echo json_encode(['success' => true, 'data' => $materias]);
                    break;
                    
                case 'mis-solicitudes':
                    // Obtener solicitudes del docente
                    $solicitudes = $docente_model->getMisSolicitudes($docente_id);
                    echo json_encode(['success' => true, 'data' => $solicitudes]);
                    break;
                
                case 'asignadas':
                    // Obtener materias asignadas al docente
                    $asignadas = $docente_model->getMateriasAsignadas($docente_id);
                    
                    // Procesar horarios si existen
                    foreach ($asignadas as &$materia) {
                        if (!empty($materia['horarios'])) {
                            $horarios_str = $materia['horarios'];
                            // Parsear el concatenado de horarios
                            $horarios_array = explode(' | ', $horarios_str);
                            $materia['horarios'] = array_map(function($h) {
                                // Extraer información del horario
                                preg_match('/(\w+)\s(\d{2}:\d{2})-(\d{2}:\d{2})\s\(Aula:\s(.*?)\)/', $h, $matches);
                                return [
                                    'dia' => $matches[1] ?? '',
                                    'hora_inicio' => $matches[2] ?? '',
                                    'hora_fin' => $matches[3] ?? '',
                                    'aula' => trim($matches[4] ?? '')
                                ];
                            }, $horarios_array);
                        } else {
                            $materia['horarios'] = [];
                        }
                    }
                    
                    echo json_encode([
                        'success' => true, 
                        'data' => [
                            'materias' => $asignadas,
                            'estadisticas' => [
                                'total_materias' => count($asignadas),
                                'total_grupos' => count(array_unique(array_column($asignadas, 'grupo_id'))),
                                'total_creditos' => array_sum(array_column($asignadas, 'creditos')),
                                'total_horas' => array_sum(array_column($asignadas, 'horas_semana'))
                            ]
                        ]
                    ]);
                    break;
                    
                case 'estadisticas':
                    // Estadísticas del docente
                    $stats_query = "SELECT 
                                        d.horas_asignadas,
                                        d.horas_maximas_semanales,
                                        d.antiguedad,
                                        COUNT(CASE WHEN a.estado = 'solicitada' THEN 1 END) as solicitudes_pendientes,
                                        COUNT(CASE WHEN a.estado = 'confirmada' THEN 1 END) as materias_confirmadas,
                                        COUNT(a.asignacion_id) as total_asignaciones
                                    FROM docentes d
                                    LEFT JOIN asignaciones a ON d.docente_id = a.docente_id
                                    WHERE d.docente_id = ?
                                    GROUP BY d.docente_id";
                    $db = getDatabase();
                    $stmt = $db->prepare($stats_query);
                    $stmt->bind_param("i", $docente_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $stats = $result->fetch_assoc();
                    $stmt->close();
                    
                    echo json_encode(['success' => true, 'data' => $stats]);
                    break;
                    
                default:
                    throw new Exception('Acción no válida');
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';
            
            switch ($action) {
                case 'solicitar':
                    // Solicitar asignación de materia/grupo
                    $materia_id = $input['materia_id'] ?? null;
                    $grupo_id = $input['grupo_id'] ?? null;
                    
                    if (!$materia_id || !$grupo_id) {
                        throw new Exception('materia_id y grupo_id requeridos');
                    }
                    
                    $resultado = $docente_model->solicitarAsignacion($docente_id, $materia_id, $grupo_id);
                    echo json_encode($resultado);
                    break;
                    
                case 'cancelar':
                    // Cancelar solicitud
                    $asignacion_id = $input['asignacion_id'] ?? null;
                    if (!$asignacion_id) {
                        throw new Exception('asignacion_id requerido');
                    }
                    
                    $update_query = "UPDATE asignaciones 
                                    SET estado = 'cancelada' 
                                    WHERE asignacion_id = ? AND docente_id = ? AND estado = 'solicitada'";
                    $db = getDatabase();
                    $stmt = $db->prepare($update_query);
                    $stmt->bind_param("ii", $asignacion_id, $docente_id);
                    $success = $stmt->execute();
                    $stmt->close();
                    
                    echo json_encode([
                        'success' => $success, 
                        'message' => $success ? 'Solicitud cancelada' : 'Error al cancelar'
                    ]);
                    break;
                    
                default:
                    throw new Exception('Acción no válida');
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
    
} catch (Exception $e) {
    error_log('[SELECCION DOCENTE API] Error: ' . $e->getMessage());
    error_log('[SELECCION DOCENTE API] Trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'error' => $e->getMessage()]);
}
