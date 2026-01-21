<?php
/**
 * API: Register Docente
 */

require_once '../../config.php';
require_once INCLUDES_PATH . '/Models.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método no permitido', null, 405);
}

$data = json_decode(file_get_contents('php://input'), true);

// Validaciones
$required = ['nombre', 'apellido', 'email', 'password'];
foreach ($required as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        jsonResponse(false, ucfirst($field) . ' es requerido');
    }
}

if (!validateEmail($data['email'])) {
    jsonResponse(false, 'Email inválido');
}

if (!validatePassword($data['password'])) {
    jsonResponse(false, 'Contraseña debe tener: mínimo 8 caracteres, una mayúscula, una minúscula y un número');
}

$usuarioModel = new Usuario();

// Verificar si email existe
if ($usuarioModel->getByEmail($data['email'])) {
    jsonResponse(false, 'Email ya registrado');
}

// Crear usuario como docente pendiente de aprobación
$usuarioData = [
    'nombre' => sanitize($data['nombre']),
    'apellido' => sanitize($data['apellido']),
    'email' => sanitize($data['email']),
    'telefono' => sanitize($data['telefono'] ?? ''),
    'tipo_usuario' => 'docente',
    'password_hash' => hashPassword($data['password']),
    'estado' => 'pendiente'
];

$usuarioId = $usuarioModel->create($usuarioData);

if (!$usuarioId) {
    logError('Register Error', 'Failed to create user');
    jsonResponse(false, 'Error al registrar usuario');
}

// Crear perfil de docente
$docenteModel = new Docente();
$docenteModel->create([
    'usuario_id' => $usuarioId,
    'especialidad' => '',
    'horas_maximas_semanales' => 40,
    'tipo_contrato' => 'por_horas'
]);

// Crear disponibilidad horaria inicial
$disponibilidadModel = new DisponibilidadHoraria();
$disponibilidadModel->createInitialAvailability($usuarioId);

logInfo('New docente registered', ['email' => $data['email'], 'usuario_id' => $usuarioId]);

jsonResponse(true, 'Registro exitoso. Por favor espera la aprobación del administrador', [
    'usuario_id' => $usuarioId
]);
