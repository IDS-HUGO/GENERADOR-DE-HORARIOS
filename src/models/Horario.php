<?php
/**
 * Modelo Horario
 * Sistema de Gestión de Horarios - Universidad Maya
 */

class Horario extends Model {
    protected $table = 'horarios';
    protected $id_column = 'horario_id';
    
    /**
     * Obtener horarios de una asignación
     */
    public function getByAsignacion($asignacion_id) {
        $query = "SELECT h.*, au.codigo as aula_codigo, au.nombre as aula_nombre
                  FROM {$this->table} h
                  LEFT JOIN aulas au ON h.aula_id = au.aula_id
                  WHERE h.asignacion_id = ?
                  ORDER BY h.dia_semana ASC, h.hora_inicio ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $asignacion_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
    
    /**
     * Obtener horarios de un docente
     */
        public function getByDocente($docente_id) {
                $query = "SELECT h.*, a.materia_id, a.grupo_id, m.nombre as materia_nombre, 
                                        g.codigo as grupo_codigo, au.codigo as aula_codigo,
                                        u.nombre as docente_nombre, u.apellido as docente_apellido
                                    FROM {$this->table} h
                                    JOIN asignaciones a ON h.asignacion_id = a.asignacion_id
                                    JOIN docentes d ON a.docente_id = d.docente_id
                                    JOIN usuarios u ON d.usuario_id = u.usuario_id
                                    LEFT JOIN materias m ON a.materia_id = m.materia_id
                                    LEFT JOIN grupos g ON a.grupo_id = g.grupo_id
                                    LEFT JOIN aulas au ON h.aula_id = au.aula_id
                                    WHERE a.docente_id = ?
                                    ORDER BY h.dia_semana ASC, h.hora_inicio ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $docente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
    
    /**
     * Verificar conflictos de horario
     */
    public function hayConflicto($docente_id, $dia_semana, $hora_inicio, $hora_fin, $exclude_horario_id = null) {
        $query = "SELECT COUNT(*) as total FROM {$this->table} h
                  JOIN asignaciones a ON h.asignacion_id = a.asignacion_id
                  WHERE a.docente_id = ?
                  AND h.dia_semana = ?
                  AND (
                    (h.hora_inicio < ? AND h.hora_fin > ?)
                    OR
                    (h.hora_inicio >= ? AND h.hora_inicio < ?)
                  )";
        
        if ($exclude_horario_id) {
            $query .= " AND h.horario_id != ?";
        }
        
        $stmt = $this->db->prepare($query);
        
        if ($exclude_horario_id) {
            $stmt->bind_param("issssi", $docente_id, $dia_semana, $hora_fin, $hora_inicio, $hora_inicio, $hora_fin, $exclude_horario_id);
        } else {
            $stmt->bind_param("isssss", $docente_id, $dia_semana, $hora_fin, $hora_inicio, $hora_inicio, $hora_fin);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['total'] > 0;
    }

    /**
     * Obtener horarios activos con filtros opcionales
     */
    public function getActivos($filtros = []) {
        $where = ['1 = 1'];
        $params = [];
        $types = '';

        if (!empty($filtros['docente_id'])) {
            $where[] = 'a.docente_id = ?';
            $params[] = (int)$filtros['docente_id'];
            $types .= 'i';
        }

        if (!empty($filtros['grupo_id'])) {
            $where[] = 'a.grupo_id = ?';
            $params[] = (int)$filtros['grupo_id'];
            $types .= 'i';
        }

        $sql = "SELECT h.*, a.docente_id, a.materia_id, a.grupo_id,
                   m.nombre AS materia_nombre,
                   g.codigo AS grupo_codigo,
                   au.codigo AS aula_codigo,
                   u.nombre AS docente_nombre,
                   u.apellido AS docente_apellido,
                   u.email AS docente_email
                FROM {$this->table} h
                JOIN asignaciones a ON h.asignacion_id = a.asignacion_id
            JOIN docentes d ON a.docente_id = d.docente_id
            JOIN usuarios u ON d.usuario_id = u.usuario_id
                LEFT JOIN materias m ON a.materia_id = m.materia_id
                LEFT JOIN grupos g ON a.grupo_id = g.grupo_id
                LEFT JOIN aulas au ON h.aula_id = au.aula_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY FIELD(h.dia_semana, 'sabado','domingo','lunes','martes','miercoles','jueves'), h.hora_inicio";

        if ($types) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $data;
        }

        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Obtener horarios confirmados
     */
    public function getConfirmados() {
        $query = "SELECT h.*, a.docente_id, m.nombre as materia_nombre, 
                         g.codigo as grupo_codigo, au.codigo as aula_codigo,
                         u.nombre as docente_nombre, u.apellido as docente_apellido
                  FROM {$this->table} h
                  JOIN asignaciones a ON h.asignacion_id = a.asignacion_id
                  JOIN docentes d ON a.docente_id = d.docente_id
                  JOIN usuarios u ON d.usuario_id = u.usuario_id
                  LEFT JOIN materias m ON a.materia_id = m.materia_id
                  LEFT JOIN grupos g ON a.grupo_id = g.grupo_id
                  LEFT JOIN aulas au ON h.aula_id = au.aula_id
                  WHERE h.confirmado = TRUE AND h.estado_horario = 'aprobado'
                  ORDER BY h.dia_semana ASC, h.hora_inicio ASC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Aprobar o rechazar horario por director/administrador
     */
    public function aprobarHorario($horario_id, $usuario_id, $rol_aprobador, $estado = 'aprobado', $observaciones = null) {
        $estado = ($estado === 'rechazado') ? 'rechazado' : 'aprobado';
        $confirmado = $estado === 'aprobado' ? 1 : 0;

        $data = [
            'estado_horario' => $estado,
            'confirmado' => $confirmado,
            'fecha_confirmacion' => date('Y-m-d H:i:s'),
            'aprobado_por' => $usuario_id,
            'aprobado_rol' => $rol_aprobador,
        ];

        if ($observaciones !== null) {
            $data['observaciones'] = $observaciones;
        }

        return $this->update($horario_id, $data);
    }

    /**
     * Obtener horario con contexto completo
     */
    public function getDetalle($horario_id) {
        $query = "SELECT h.*, a.docente_id, a.materia_id, a.grupo_id,
                         m.nombre AS materia_nombre,
                         g.codigo AS grupo_codigo,
                         au.codigo AS aula_codigo,
                         u.nombre AS docente_nombre,
                         u.apellido AS docente_apellido,
                         u.email AS docente_email
                  FROM {$this->table} h
                  JOIN asignaciones a ON h.asignacion_id = a.asignacion_id
                  JOIN docentes d ON a.docente_id = d.docente_id
                  JOIN usuarios u ON d.usuario_id = u.usuario_id
                  LEFT JOIN materias m ON a.materia_id = m.materia_id
                  LEFT JOIN grupos g ON a.grupo_id = g.grupo_id
                  LEFT JOIN aulas au ON h.aula_id = au.aula_id
                  WHERE h.horario_id = ?
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $horario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }
}
