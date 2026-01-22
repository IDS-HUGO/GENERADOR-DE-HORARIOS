<?php
/**
 * ClassControl - Database Status Check
 * Verificar que la base de datos está conectada y funciona
 */

require_once '../config.php';

header('Content-Type: application/json');

try {
    $db = getDatabase();
    
    if (!$db) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    // Verificar tablas principales
    $tables = ['usuarios', 'docentes', 'materias', 'grupos', 'horarios'];
    $status = [];
    
    foreach ($tables as $table) {
        $result = $db->query("SELECT COUNT(*) as count FROM $table");
        $row = $result->fetch_assoc();
        $status[$table] = [
            'exists' => true,
            'records' => $row['count']
        ];
    }
    
    // Verificar usuario admin
    $adminCheck = $db->query("SELECT * FROM usuarios WHERE email = 'admin@classcontrol.com' AND tipo_usuario = 'administrador'");
    $adminUser = $adminCheck->fetch_assoc();
    
    jsonResponse([
        'status' => 'success',
        'database' => 'classcontrol',
        'connection' => 'OK',
        'tables' => $status,
        'admin_exists' => $adminUser ? true : false,
        'admin_email' => $adminUser ? 'admin@classcontrol.com' : null,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    jsonResponse([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], 500);
}
?>
