<?php
/**
 * Modelo Docente
 */

class Docente extends Model {
    protected $table = 'docentes';
    protected $id_column = 'docente_id';
    
    /**
     * Obtener docente por usuario_id
     */
    public function getByUserId($usuario_id) {
        return $this->find('usuario_id', $usuario_id);
    }
    
    /**
     * Obtener docentes activos con datos de usuario
     */
    public function getActivosConUsuario() {
        $query = "SELECT d.*, u.nombre, u.apellido, u.email, u.telefono 
                  FROM {$this->table} d
                  JOIN usuarios u ON d.usuario_id = u.usuario_id
                  WHERE u.estado = 'activo'
                  ORDER BY u.nombre ASC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Obtener docentes por especialidad
     */
    public function getByEspecialidad($especialidad) {
        $query = "SELECT d.*, u.nombre, u.apellido, u.email 
                  FROM {$this->table} d
                  JOIN usuarios u ON d.usuario_id = u.usuario_id
                  WHERE d.especialidad = ? AND u.estado = 'activo'";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $especialidad);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
    
    /**
     * Obtener disponibilidad total de docente
     */
    public function getHorasDisponibles($docente_id) {
        $docente = $this->getById($docente_id);
        return $docente['horas_maximas_semanales'] ?? 40;
    }
}
