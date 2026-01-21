<?php
/**
 * Modelo DisponibilidadHoraria
 */

class DisponibilidadHoraria extends Model {
    protected $table = 'disponibilidad_horaria';
    protected $id_column = 'disponibilidad_id';
    
    /**
     * Obtener disponibilidad de un docente
     */
    public function getByDocente($docente_id) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE docente_id = ? AND disponible = TRUE
                  ORDER BY 
                    FIELD(dia_semana, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'),
                    hora_inicio ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $docente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
    
    /**
     * Obtener disponibilidad por día
     */
    public function getByDocenteAndDay($docente_id, $dia_semana) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE docente_id = ? AND dia_semana = ? AND disponible = TRUE
                  ORDER BY hora_inicio ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("is", $docente_id, $dia_semana);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
    
    /**
     * Verificar si docente está disponible en un horario
     */
    public function isAvailable($docente_id, $dia_semana, $hora_inicio, $hora_fin) {
        $query = "SELECT COUNT(*) as total FROM {$this->table} 
                  WHERE docente_id = ? 
                  AND dia_semana = ? 
                  AND disponible = TRUE
                  AND hora_inicio <= ? 
                  AND hora_fin >= ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("isss", $docente_id, $dia_semana, $hora_inicio, $hora_fin);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['total'] > 0;
    }
    
    /**
     * Crear disponibilidad inicial para docente
     */
    public function createInitialAvailability($docente_id, $hora_inicio = '08:00', $hora_fin = '20:00') {
        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        
        foreach ($dias as $dia) {
            $this->create([
                'docente_id' => $docente_id,
                'dia_semana' => $dia,
                'hora_inicio' => $hora_inicio,
                'hora_fin' => $hora_fin,
                'disponible' => true
            ]);
        }
        
        return true;
    }
}
