<?php
/**
 * API de Reportes - Generación y exportación
 */

require_once '../config.php';

if (!isAuthenticated()) {
    jsonResponse(false, 'No autorizado', null, 401);
}

$action = $_GET['action'] ?? '';
$user = getCurrentUser();

try {
    switch ($action) {
        case 'generar':
            $tipo = $_GET['tipo'] ?? null;
            if (!$tipo) jsonResponse(false, 'Tipo requerido', null, 400);

            $reporteador = new Reporteador();
            $datos = null;

            switch($tipo) {
                case 'docentes':
                    $datos = $reporteador->reporteDocentes();
                    break;
                case 'horarios':
                    $datos = $reporteador->reporteHorarios();
                    break;
                case 'grupos':
                    $datos = $reporteador->reporteGrupos();
                    break;
                case 'conflictos':
                    $datos = $reporteador->reporteConflictos();
                    break;
                default:
                    jsonResponse(false, 'Tipo de reporte no soportado', null, 400);
            }

            jsonResponse(true, 'Reporte generado', $datos);
            break;

        case 'resumen':
            // Resumen ejecutivo
            $reporteador = new Reporteador();
            $resumen = [
                'total_docentes' => (new Docente())->contar(),
                'total_grupos' => (new Grupo())->contar(),
                'total_materias' => (new Materia())->contar(),
                'horarios_activos' => (new Horario())->contar(['estado' => 'activo']),
                'conflictos' => (new Conflictos())->detectarTodos()
            ];

            jsonResponse(true, '', $resumen);
            break;

        case 'exportar_pdf':
            // Exportar a PDF
            $grupoId = $_GET['grupo_id'] ?? null;
            if (!$grupoId) jsonResponse(false, 'grupo_id requerido', null, 400);

            $reporteador = new Reporteador();
            $pdf = $reporteador->generarPDF($grupoId);

            jsonResponse(true, '', base64_encode($pdf));
            break;

        case 'compartir':
            // Compartir reporte por email
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['reporte_id']) || empty($data['emails'])) {
                jsonResponse(false, 'Datos incompletos', null, 400);
            }

            // Aquí iría lógica de envío de emails
            // Por ahora solo confirmamos
            notificaciones_log('Reporte compartido', $user['id']);

            jsonResponse(true, 'Reporte compartido con éxito');
            break;

        case 'programar':
            // Programar reporte automático
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['tipo']) || empty($data['frecuencia'])) {
                jsonResponse(false, 'Datos incompletos', null, 400);
            }

            // Guardar programación
            // SELECT * FROM reportes_programados WHERE usuario_id = ? ...

            jsonResponse(true, 'Reporte programado', ['id' => uniqid()]);
            break;

        default:
            jsonResponse(false, 'Acción no válida', null, 400);
    }
} catch (Exception $e) {
    logError($e);
    jsonResponse(false, 'Error en servidor', null, 500);
}

/**
 * Clase auxiliar para generar reportes
 */
class Reporteador {
    public function reporteDocentes() {
        return (new Docente())->getActivosConUsuario();
    }

    public function reporteHorarios() {
        return (new Horario())->getActivos();
    }

    public function reporteGrupos() {
        return (new Grupo())->getActivos();
    }

    public function reporteConflictos() {
        // Detectar todos los conflictos
        $horarios = (new Horario())->getActivos();
        $conflictos = [];

        foreach ($horarios as $h1) {
            foreach ($horarios as $h2) {
                if ($h1['id'] != $h2['id'] && 
                    $h1['docente_id'] == $h2['docente_id'] && 
                    $h1['dia_semana'] == $h2['dia_semana'] &&
                    $this->tiemposSuperponen($h1['hora_inicio'], $h1['hora_fin'], 
                                             $h2['hora_inicio'], $h2['hora_fin'])) {
                    $conflictos[] = [
                        'horario1' => $h1,
                        'horario2' => $h2,
                        'tipo' => 'docente'
                    ];
                }
            }
        }

        return $conflictos;
    }

    private function tiemposSuperponen($ini1, $fin1, $ini2, $fin2) {
        return strtotime($ini1) < strtotime($fin2) && strtotime($ini2) < strtotime($fin1);
    }

    public function generarPDF($grupoId) {
        // Aquí iría TCPDF o similar
        // Por ahora retornamos un placeholder
        return "PDF para grupo $grupoId";
    }
}

function notificaciones_log($mensaje, $usuarioId) {
    // Guardar notificación en BD
}
