<?php
/**
 * Modelo Horario
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
                  LEFT JOIN aulas au ON h.salon_id = au.aula_id
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
                         g.codigo as grupo_codigo, au.codigo as aula_codigo
                  FROM {$this->table} h
                  JOIN asignaciones a ON h.asignacion_id = a.asignacion_id
                  LEFT JOIN materias m ON a.materia_id = m.materia_id
                  LEFT JOIN grupos g ON a.grupo_id = g.grupo_id
                  LEFT JOIN aulas au ON h.salon_id = au.aula_id
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
     * Obtener horarios confirmados
     */
    public function getConfirmados() {
        $query = "SELECT h.*, a.docente_id, m.nombre as materia_nombre, 
                         g.codigo as grupo_codigo, au.codigo as aula_codigo
                  FROM {$this->table} h
                  JOIN asignaciones a ON h.asignacion_id = a.asignacion_id
                  LEFT JOIN materias m ON a.materia_id = m.materia_id
                  LEFT JOIN grupos g ON a.grupo_id = g.grupo_id
                  LEFT JOIN aulas au ON h.salon_id = au.aula_id
                  WHERE h.confirmado = TRUE
                  ORDER BY h.dia_semana ASC, h.hora_inicio ASC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
