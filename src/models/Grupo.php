<?php
/**
 * Modelo Grupo
 */

class Grupo extends Model {
    protected $table = 'grupos';
    protected $id_column = 'grupo_id';
    
    /**
     * Obtener grupos de un programa
     */
    public function getByPrograma($programa_id) {
        $query = "SELECT * FROM {$this->table} WHERE programa_id = ? AND estado = 'activo' ORDER BY codigo ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $programa_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
    
    /**
     * Obtener grupo por código
     */
    public function getByCodigo($codigo) {
        return $this->find('codigo', $codigo);
    }
    
    /**
     * Obtener grupos activos
     */
    public function getActivos() {
        $query = "SELECT g.*, p.nombre as programa_nombre 
                  FROM {$this->table} g
                  JOIN programas_academicos p ON g.programa_id = p.programa_id
                  WHERE g.estado = 'activo'
                  ORDER BY g.codigo ASC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
