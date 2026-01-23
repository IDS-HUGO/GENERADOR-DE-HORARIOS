<?php
/**
 * API de Gestión de Administradores (Solo para Directores)
 * Sistema de Gestión de Horarios - Universidad Maya
 * Copyright (c) 2026 Universidad Maya - Todos los derechos reservados
 */

require_once '../config.php';
require_once '../includes/Models.php';

header('Content-Type: application/json');
initSession();

// Verificar autenticación y permisos de DIRECTOR
$user = getCurrentUser();
if (!$user || $user['tipo_usuario'] !== 'director') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Solo directores pueden gestionar administradores']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $usuario_model = new Usuario();
    
    switch ($method) {
        case 'GET':
            $action = $_GET['action'] ?? 'list';
            
            switch ($action) {
                case 'list':
                    // Listar todos los administradores
                    $administradores = $usuario_model->getByType('administrador');
                    echo json_encode(['success' => true, 'data' => $administradores]);
                    break;
                    
                case 'stats':
                    // Estadísticas generales del sistema
                    $db = getDatabase();
                    $stats_query = "SELECT 
                                        (SELECT COUNT(*) FROM usuarios WHERE tipo_usuario = 'director') as total_directores,
                                        (SELECT COUNT(*) FROM usuarios WHERE tipo_usuario = 'administrador') as total_administradores,
                                        (SELECT COUNT(*) FROM usuarios WHERE tipo_usuario = 'docente') as total_docentes,
                                        (SELECT COUNT(*) FROM usuarios WHERE estado = 'activo') as usuarios_activos,
                                        (SELECT COUNT(*) FROM programas_academicos WHERE estado = 'activo') as programas_activos,
                                        (SELECT COUNT(*) FROM grupos WHERE estado = 'activo') as grupos_activos,
                                        (SELECT COUNT(*) FROM asignaciones WHERE estado = 'solicitada') as solicitudes_pendientes";
                    $result = $db->query($stats_query);
                    if (!$result) {
                        throw new Exception('Error al obtener estadísticas: ' . $db->error);
                    }
                    $stats = $result->fetch_assoc();
                    echo json_encode(['success' => true, 'data' => $stats]);
                    break;
                    
                case 'get':
                    $id = $_GET['id'] ?? null;
                    if (!$id) {
                        throw new Exception('ID requerido');
                    }
                    $admin = $usuario_model->getById($id);
                    if (!$admin || $admin['tipo_usuario'] !== 'administrador') {
                        throw new Exception('Administrador no encontrado');
                    }
                    echo json_encode(['success' => true, 'data' => $admin]);
                    break;
                    
                default:
                    throw new Exception('Acción no válida');
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Crear nuevo administrador
            $required = ['nombre', 'apellido', 'email', 'password'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    throw new Exception("Campo $field requerido");
                }
            }
            
            // Validar email
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido');
            }
            
            // Verificar si email ya existe
            if ($usuario_model->getByEmail($input['email'])) {
                throw new Exception('Email ya registrado');
            }
            
            $data = [
                'nombre' => $input['nombre'],
                'apellido' => $input['apellido'],
                'email' => $input['email'],
                'telefono' => $input['telefono'] ?? '',
                'tipo_usuario' => 'administrador',
                'password_hash' => password_hash($input['password'], PASSWORD_DEFAULT),
                'estado' => $input['estado'] ?? 'activo'
            ];
            
            $id = $usuario_model->create($data);
            
            if ($id) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Administrador creado exitosamente',
                    'id' => $id
                ]);
            } else {
                throw new Exception('Error al crear administrador');
            }
            break;
            
        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['usuario_id'] ?? null;
            
            if (!$id) {
                throw new Exception('usuario_id requerido');
            }
            
            // Verificar que existe y es administrador
            $admin = $usuario_model->getById($id);
            if (!$admin || $admin['tipo_usuario'] !== 'administrador') {
                throw new Exception('Administrador no encontrado');
            }
            
            $update_data = [];
            $allowed_fields = ['nombre', 'apellido', 'email', 'telefono', 'estado'];
            
            foreach ($allowed_fields as $field) {
                if (isset($input[$field])) {
                    $update_data[$field] = $input[$field];
                }
            }
            
            // Si se cambia contraseña
            if (!empty($input['password'])) {
                $update_data['password_hash'] = password_hash($input['password'], PASSWORD_DEFAULT);
            }
            
            $success = $usuario_model->update($id, $update_data);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Administrador actualizado' : 'Error al actualizar'
            ]);
            break;
            
        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('ID requerido');
            }
            
            // Verificar que es administrador
            $admin = $usuario_model->getById($id);
            if (!$admin || $admin['tipo_usuario'] !== 'administrador') {
                throw new Exception('Administrador no encontrado');
            }
            
            // En lugar de eliminar, desactivar
            $success = $usuario_model->update($id, ['estado' => 'inactivo']);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Administrador desactivado' : 'Error al desactivar'
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
    
} catch (Exception $e) {
    error_log('[ADMIN API] Error: ' . $e->getMessage());
    error_log('[ADMIN API] Trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'error' => $e->getMessage()]);
}
