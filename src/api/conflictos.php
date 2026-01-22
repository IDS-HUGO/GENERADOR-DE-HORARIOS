<?php
/**
 * API: Análisis de Conflictos de Horarios
 */

require_once '../config.php';

if (!isAuthenticated()) {
    jsonResponse(false, 'No autenticado', null, 401);
    exit;
}

$db = getDatabase();

// Obtener todos los horarios
$query = "SELECT h.*, d.nombre as docente_nombre, g.nombre as grupo_nombre 
          FROM horarios h
          JOIN docentes d ON h.docente_id = d.id
          JOIN grupos g ON h.grupo_id = g.id
          ORDER BY h.dia, h.hora_inicio";

$result = $db->query($query);
$horarios = [];

while ($row = $result->fetch_assoc()) {
    $horarios[] = $row;
}

// Detectar conflictos
$conflictos = [];

for ($i = 0; $i < count($horarios); $i++) {
    for ($j = $i + 1; $j < count($horarios); $j++) {
        $h1 = $horarios[$i];
        $h2 = $horarios[$j];
        
        // Mismo docente, mismo día, mismo horario
        if ($h1['docente_id'] === $h2['docente_id'] && 
            $h1['dia'] === $h2['dia'] &&
            tiempos_solapados($h1['hora_inicio'], $h1['hora_fin'], $h2['hora_inicio'], $h2['hora_fin'])) {
            
            $conflictos[] = [
                'tipo' => 'Conflicto de Docente',
                'docente' => $h1['docente_nombre'],
                'dia' => $h1['dia'],
                'horario1' => $h1['hora_inicio'] . ' - ' . $h1['hora_fin'],
                'horario2' => $h2['hora_inicio'] . ' - ' . $h2['hora_fin'],
                'grupo1' => $h1['grupo_nombre'],
                'grupo2' => $h2['grupo_nombre']
            ];
        }
        
        // Mismo grupo, mismo día, mismo horario
        if ($h1['grupo_id'] === $h2['grupo_id'] && 
            $h1['dia'] === $h2['dia'] &&
            tiempos_solapados($h1['hora_inicio'], $h1['hora_fin'], $h2['hora_inicio'], $h2['hora_fin'])) {
            
            $conflictos[] = [
                'tipo' => 'Conflicto de Grupo',
                'grupo' => $h1['grupo_nombre'],
                'dia' => $h1['dia'],
                'horario1' => $h1['hora_inicio'] . ' - ' . $h1['hora_fin'],
                'horario2' => $h2['hora_inicio'] . ' - ' . $h2['hora_fin'],
                'docente1' => $h1['docente_nombre'],
                'docente2' => $h2['docente_nombre']
            ];
        }
    }
}

jsonResponse(true, 'Análisis completado', [
    'total_horarios' => count($horarios),
    'conflictos_encontrados' => count($conflictos),
    'conflictos' => $conflictos
]);

function tiempos_solapados($inicio1, $fin1, $inicio2, $fin2) {
    $h1_i = (int)explode(':', $inicio1)[0];
    $h1_f = (int)explode(':', $fin1)[0];
    $h2_i = (int)explode(':', $inicio2)[0];
    $h2_f = (int)explode(':', $fin2)[0];
    
    return ($h1_i < $h2_f) && ($h2_i < $h1_f);
}
?>
