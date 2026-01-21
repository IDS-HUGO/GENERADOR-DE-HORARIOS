<?php
/**
 * Modelo ProgramaAcademico
 */

class ProgramaAcademico extends Model {
    protected $table = 'programas_academicos';
    protected $id_column = 'programa_id';
    
    /**
     * Obtener programas activos
     */
    public function getActivos() {
        $query = "SELECT * FROM {$this->table} WHERE estado = 'activo' ORDER BY nombre ASC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Obtener programa por código
     */
    public function getByCodigo($codigo) {
        return $this->find('codigo', $codigo);
    }
    
    /**
     * Obtener programa con todos sus datos
     */
    public function getConDetalles($programa_id) {
        $programa = $this->getById($programa_id);
        
        if (!$programa) {
            return null;
        }
        
        $materiasModel = new Materia();
        $gruposModel = new Grupo();
        
        $programa['materias'] = $materiasModel->getByPrograma($programa_id);
        $programa['grupos'] = $gruposModel->getByPrograma($programa_id);
        
        return $programa;
    }
}
