<?php
/**
 * CONFIGURACIÓN CENTRAL - ClassControl v1.0
 * Sistema Profesional de Gestión de Horarios
 * Entorno: PRODUCCIÓN
 */

// =====================================================
// CONFIGURACIÓN DE BASE DE DATOS
// =====================================================
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'classcontrol');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

// =====================================================
// CONFIGURACIÓN DE APLICACIÓN
// =====================================================
define('PRODUCTION', true);
define('APP_NAME', 'ClassControl');
define('APP_VERSION', '1.0');
define('APP_URL', 'http://localhost/ClassControl');

// =====================================================
// CONFIGURACIÓN DE SEGURIDAD
// =====================================================
define('SESSION_LIFETIME', 8 * 3600);
define('SESSION_NAME', 'classcontrol_session');
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 10);
define('ENABLE_CSRF', true);

// =====================================================
// RUTAS DEL SISTEMA
// =====================================================
define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', __DIR__);
define('PUBLIC_PATH', BASE_PATH . '/public');
define('LOG_PATH', BASE_PATH . '/logs');
define('VIEWS_PATH', SRC_PATH . '/views');
define('INCLUDES_PATH', SRC_PATH . '/includes');

// =====================================================
// CONFIGURACIÓN DE LOGS
// =====================================================
define('LOG_ENABLED', true);
define('LOG_LEVEL', 'debug');

// =====================================================
// COLORES (Paleta Alegreya Ubuntu)
// =====================================================
define('COLORS', [
    'primary_light' => '#ADD8E6',
    'secondary_light' => '#D3D3D3',
    'accent_pink' => '#FFB6C1',
    'accent_salmon' => '#FFCBA4',
    'dark_bg' => '#1a1a1a',
    'light_bg' => '#f5f5f5',
    'text_dark' => '#2c3e50',
    'text_light' => '#ecf0f1',
    'success' => '#27ae60',
    'danger' => '#e74c3c',
    'warning' => '#f39c12',
    'info' => '#3498db'
]);

// =====================================================
// ZONA HORARIA
// =====================================================
date_default_timezone_set('America/Bogota');

// =====================================================
// FUNCIONES GLOBALES
// =====================================================

function getDatabase() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception('Error de conexión a BD: ' . $conn->connect_error);
            }
            
            $conn->set_charset(DB_CHARSET);
            
        } catch (Exception $e) {
            logError('Database Connection Error', $e->getMessage());
            die('Error de conexión a la base de datos');
        }
    }
    
    return $conn;
}

function jsonResponse($success, $message = '', $data = null, $statusCode = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
        
        if (!isset($_SESSION['last_regenerate'])) {
            $_SESSION['last_regenerate'] = time();
            session_regenerate_id(true);
        } elseif (time() - $_SESSION['last_regenerate'] > 3600) {
            $_SESSION['last_regenerate'] = time();
            session_regenerate_id(true);
        }
    }
}

function isAuthenticated() {
    initSession();
    return isset($_SESSION['usuario_id']) && isset($_SESSION['tipo_usuario']);
}

function getCurrentUser() {
    initSession();
    if (!isAuthenticated()) {
        return null;
    }
    
    return [
        'usuario_id' => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['nombre'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'tipo_usuario' => $_SESSION['tipo_usuario']
    ];
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function baseUrl($path = '') {
    return APP_URL . '/' . ltrim($path, '/');
}

function logError($type, $message) {
    if (!LOG_ENABLED) return;
    
    $logFile = LOG_PATH . '/' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [ERROR] $type - $message";
    
    if (!is_dir(LOG_PATH)) {
        mkdir(LOG_PATH, 0755, true);
    }
    
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}

function logInfo($message, $data = null) {
    if (!LOG_ENABLED) return;
    
    $logFile = LOG_PATH . '/' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [INFO] $message";
    if ($data) {
        $logMessage .= " | " . json_encode($data);
    }
    
    if (!is_dir(LOG_PATH)) {
        mkdir(LOG_PATH, 0755, true);
    }
    
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    logError('PHP Error', "$errstr in $errfile:$errline");
    return true;
});

set_exception_handler(function($e) {
    logError('Exception', $e->getMessage());
    if (!PRODUCTION) {
        echo "<pre>" . $e . "</pre>";
    } else {
        jsonResponse(false, 'Error del sistema', null, 500);
    }
});
?>
