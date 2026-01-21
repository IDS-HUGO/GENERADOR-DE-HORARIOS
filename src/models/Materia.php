<?php
/**
 * Modelo Materia
 */

class Materia extends Model {
    protected $table = 'materias';
    protected $id_column = 'materia_id';
    
    /**
     * Obtener materias de un programa
     */
    public function getByPrograma($programa_id) {
        $query = "SELECT * FROM {$this->table} WHERE programa_id = ? ORDER BY nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $programa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
    
    /**
     * Obtener materia por código
     */
    public function getByCodigo($codigo) {
        return $this->find('codigo', $codigo);
    }
    
    /**
     * Obtener materias con programa
     */
    public function getConPrograma() {
        $query = "SELECT m.*, p.nombre as programa_nombre 
                  FROM {$this->table} m
                  JOIN programas_academicos p ON m.programa_id = p.programa_id
                  ORDER BY p.nombre, m.nombre";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Obtener materias obligatorias de un programa
     */
    public function getObligatoriasByPrograma($programa_id) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE programa_id = ? AND es_obligatoria = TRUE
                  ORDER BY nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $programa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
}
