<?php
require_once '../../src/config.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$user = getCurrentUser();
$docente = (new Docente())->getByUserId($user['usuario_id']);

echo json_encode([
    'usuario' => $user,
    'docente' => $docente,
    'tiene_docente_id' => isset($docente['docente_id']),
    'docente_id' => $docente['docente_id'] ?? null
], JSON_PRETTY_PRINT);
