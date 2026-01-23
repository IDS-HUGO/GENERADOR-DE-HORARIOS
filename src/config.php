<?php
/**
 * CONFIGURACIÓN CENTRAL - ClassControl v1.0
 * Sistema Profesional de Gestión de Horarios
 * Entorno: PRODUCCIÓN
 */

// =====================================================
// CARGAR VARIABLES DE ENTORNO (.env)
// =====================================================
function loadEnvFile() {
    $envPath = dirname(__DIR__) . '/.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') === false || strpos($line, '#') === 0) continue;
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!isset($_ENV[$key]) && !isset($_SERVER[$key])) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}
loadEnvFile();

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

// Detectar URL dinámicamente basado en la carpeta actual
function getAppUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    
    // Obtener la carpeta raíz del proyecto
    // Desde /ClassControl/src/api/auth/login.php -> extrae /ClassControl
    // Desde /GENERADOR-DE-HORARIOS/src/api/... -> extrae /GENERADOR-DE-HORARIOS
    
    $pathParts = explode('/', trim($uri, '/'));
    
    // El primer elemento es la carpeta del proyecto
    $projectFolder = isset($pathParts[0]) && !empty($pathParts[0]) ? $pathParts[0] : '';
    
    // Si el primer elemento es "src", significa que estamos accediendo directamente
    // Si el primer elemento NO es "src", "public", "admin", "docente", significa que es la carpeta del proyecto
    $nonProjectPaths = ['src', 'public', 'admin', 'docente', 'database', 'logs', 'scripts'];
    
    if ($projectFolder && !in_array($projectFolder, $nonProjectPaths)) {
        // Es el nombre de la carpeta del proyecto
        return $protocol . '://' . $host . '/' . $projectFolder;
    }
    
    return $protocol . '://' . $host;
}

define('APP_URL', getAppUrl());

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
// CONFIGURACIÓN DE EMAIL (GMAIL)
// =====================================================
define('MAIL_DRIVER', 'smtp');
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', $_ENV['GMAIL_EMAIL'] ?? '');  // Gmail: tu-email@gmail.com
define('MAIL_PASSWORD', $_ENV['GMAIL_PASSWORD'] ?? '');  // Gmail: Contraseña de aplicación (App Password)
define('MAIL_FROM_NAME', 'ClassControl');
define('MAIL_FROM_ADDRESS', $_ENV['GMAIL_EMAIL'] ?? '');

// =====================================================
// CARGAR MODELOS
// =====================================================
require_once INCLUDES_PATH . '/Models.php';

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
    
    // Si se pasa un array como primer argumento (formato antiguo)
    if (is_array($success)) {
        http_response_code(is_array($message) && isset($message[0]) ? $message[0] : $statusCode);
        echo json_encode($success, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    // Formato correcto: (bool, string, array, int)
    http_response_code($statusCode);
    
    $response = [
        'success' => (bool)$success,
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
            // Usar false para preservar los datos de sesión al cambiar el ID
            session_regenerate_id(false);
        } elseif (time() - $_SESSION['last_regenerate'] > 3600) {
            $_SESSION['last_regenerate'] = time();
            // Usar false para preservar los datos de sesión al cambiar el ID
            session_regenerate_id(false);
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

/**
 * Generar contraseña temporal segura
 * Formato: Xx123456!
 */
function generateTemporaryPassword($length = 10) {
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $symbols = '!@#$%&*';
    
    $password = '';
    $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
    $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
    $password .= $numbers[rand(0, strlen($numbers) - 1)];
    $password .= $symbols[rand(0, strlen($symbols) - 1)];
    
    $all = $uppercase . $lowercase . $numbers . $symbols;
    for ($i = 4; $i < $length; $i++) {
        $password .= $all[rand(0, strlen($all) - 1)];
    }
    
    return str_shuffle($password);
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

/**
 * Validar configuración SMTP antes de enviar
 */
function ensureMailConfigReady() {
    $user = $_ENV['GMAIL_EMAIL'] ?? '';
    $pass = $_ENV['GMAIL_PASSWORD'] ?? '';

    if (empty($user) || empty($pass)) {
        logError('SMTP_CONFIG', 'Falta GMAIL_EMAIL o GMAIL_PASSWORD en .env');
        return false;
    }

    if (!extension_loaded('openssl')) {
        logError('SMTP_CONFIG', 'La extensión openssl es requerida para SMTP');
        return false;
    }

    return true;
}

/**
 * Enviar email con credenciales vía SMTP Gmail (SSL 465 → STARTTLS 587)
 */
function enviarEmailCredenciales($email, $nombre, $apellido, $password) {
    if (!ensureMailConfigReady()) {
        return false;
    }

    $user = $_ENV['GMAIL_EMAIL'];
    $pass = $_ENV['GMAIL_PASSWORD'];

    if (!validateEmail($email)) {
        logError('SMTP_EMAIL', 'Email destino inválido: ' . $email);
        return false;
    }

    $asunto = 'ClassControl - Credenciales de Acceso';
    $from = $user;
    $body = "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif'>"
        . "<h2>Hola $nombre $apellido,</h2>"
        . "<p>Tu cuenta en ClassControl ha sido creada.</p>"
        . "<p><strong>Usuario:</strong> $email<br><strong>Contraseña temporal:</strong> $password</p>"
        . "<p>Cambia tu contraseña en el primer acceso.</p>"
        . "</body></html>";

    try {
        $sent = smtpSendGmail($from, $pass, $email, $asunto, $body);
        if ($sent) {
            logInfo('SMTP_SENT', ['to' => $email]);
        }
        return $sent;
    } catch (Exception $e) {
        logError('EMAIL_ERROR', $e->getMessage());
        return false;
    }
}

/**
 * Envío SMTP sencillo con fallback y trazas detalladas
 */
function smtpSendGmail($fromEmail, $fromPass, $toEmail, $subject, $html) {
    $host = 'smtp.gmail.com';
    $timeout = 30;
    $lastTrace = [];

    // Intentar SSL 465 primero, luego STARTTLS 587
    $attempts = [
        ['scheme' => 'ssl', 'port' => 465],
        ['scheme' => 'tcp', 'port' => 587]
    ];

    foreach ($attempts as $try) {
        $trace = [];
        $socket = @stream_socket_client("{$try['scheme']}://$host:{$try['port']}", $errno, $errstr, $timeout);
        if (!$socket) {
            $trace[] = "CONNECT FAIL {$try['scheme']}:{$try['port']} => $errno $errstr";
            logError('SMTP_CONNECT', end($trace));
            $lastTrace = $trace;
            continue;
        }

        stream_set_timeout($socket, $timeout);

        $readAll = function() use ($socket, &$trace, $try) {
            $lines = [];
            while (($line = fgets($socket, 515)) !== false) {
                $line = trim($line);
                $trace[] = "{$try['scheme']}:{$try['port']} << $line";
                $lines[] = $line;
                if (strlen($line) >= 4 && $line[3] !== '-') {
                    break;
                }
            }
            return end($lines) ?: '';
        };

        $write = function($data) use ($socket, &$trace, $try) {
            $trace[] = "{$try['scheme']}:{$try['port']} >> $data";
            fwrite($socket, $data . "\r\n");
        };

        $banner = $readAll();
        if (strpos($banner, '220') !== 0) {
            logError('SMTP_BANNER', $banner);
            fclose($socket);
            $lastTrace = $trace;
            continue;
        }

        $write('EHLO localhost');
        $readAll();

        if ($try['port'] === 587) {
            $write('STARTTLS');
            $tlsResp = $readAll();
            if (strpos($tlsResp, '220') !== 0 || !@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                logError('SMTP_TLS', $tlsResp ?: 'Fallo STARTTLS');
                fclose($socket);
                $lastTrace = $trace;
                continue;
            }
            $write('EHLO localhost');
            $readAll();
        }

        $write('AUTH LOGIN');
        $readAll();
        $write(base64_encode($fromEmail));
        $readAll();
        $write(base64_encode($fromPass));
        $authResp = $readAll();
        if (strpos($authResp, '235') !== 0) {
            logError('SMTP_AUTH', $authResp);
            fclose($socket);
            $lastTrace = $trace;
            continue;
        }

        $write('MAIL FROM: <' . $fromEmail . '>');
        $readAll();
        $write('RCPT TO: <' . $toEmail . '>');
        $readAll();
        $write('DATA');
        $readAll();

        $headers = [
            'From: ' . $fromEmail,
            'To: ' . $toEmail,
            'Subject: ' . $subject,
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        ];

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $html . "\r\n.\r\n";
        $write($message);
        $resp = $readAll();
        $write('QUIT');
        fclose($socket);

        if (strpos($resp, '250') === 0) {
            logInfo('SMTP_OK', ['port' => $try['port']]);
            return true;
        }

        logError('SMTP_SEND', $resp ?: 'Respuesta vacía');
        $lastTrace = $trace;
    }

    if (!empty($lastTrace)) {
        logError('SMTP_TRACE', json_encode($lastTrace));
    }

    return false;
}

set_exception_handler(function($e) {
    logError('Exception', $e->getMessage());
    if (!PRODUCTION) {
        echo "<pre>" . $e . "</pre>";
    } else {
        jsonResponse(false, 'Error del sistema', null, 500);
    }
});
?>
