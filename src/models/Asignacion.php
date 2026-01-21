<?php
/**
 * Modelo Asignación (Docente-Materia-Grupo)
 */

class Asignacion extends Model {
    protected $table = 'asignaciones';
    protected $id_column = 'asignacion_id';
    
    /**
     * Obtener asignaciones de un docente
     */
    public function getByDocente($docente_id) {
        $query = "SELECT a.*, u.nombre as docente_nombre, m.nombre as materia_nombre, 
                         g.codigo as grupo_codigo, au.codigo as aula_codigo
                  FROM {$this->table} a
                  LEFT JOIN docentes d ON a.docente_id = d.docente_id
                  LEFT JOIN usuarios u ON d.usuario_id = u.usuario_id
                  LEFT JOIN materias m ON a.materia_id = m.materia_id
                  LEFT JOIN grupos g ON a.grupo_id = g.grupo_id
                  LEFT JOIN aulas au ON a.aula_id = au.aula_id
                  WHERE a.docente_id = ?
                  ORDER BY m.nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $docente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
    
    /**
     * Obtener asignaciones de una materia
     */
    public function getByMateria($materia_id) {
        $query = "SELECT a.*, u.nombre as docente_nombre, g.codigo as grupo_codigo
                  FROM {$this->table} a
                  LEFT JOIN docentes d ON a.docente_id = d.docente_id
                  LEFT JOIN usuarios u ON d.usuario_id = u.usuario_id
                  LEFT JOIN grupos g ON a.grupo_id = g.grupo_id
                  WHERE a.materia_id = ?
                  ORDER BY g.codigo ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $materia_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
    
    /**
     * Obtener asignaciones pendientes
     */
    public function getPendientes() {
        $query = "SELECT a.*, u.nombre as docente_nombre, m.nombre as materia_nombre, 
                         g.codigo as grupo_codigo
                  FROM {$this->table} a
                  LEFT JOIN docentes d ON a.docente_id = d.docente_id
                  LEFT JOIN usuarios u ON d.usuario_id = u.usuario_id
                  LEFT JOIN materias m ON a.materia_id = m.materia_id
                  LEFT JOIN grupos g ON a.grupo_id = g.grupo_id
                  WHERE a.estado = 'pendiente'
                  ORDER BY m.nombre ASC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Obtener asignaciones confirmadas
     */
    public function getConfirmadas() {
        $query = "SELECT a.*, u.nombre as docente_nombre, m.nombre as materia_nombre, 
                         g.codigo as grupo_codigo
                  FROM {$this->table} a
                  LEFT JOIN docentes d ON a.docente_id = d.docente_id
                  LEFT JOIN usuarios u ON d.usuario_id = u.usuario_id
                  LEFT JOIN materias m ON a.materia_id = m.materia_id
                  LEFT JOIN grupos g ON a.grupo_id = g.grupo_id
                  WHERE a.estado = 'confirmada'
                  ORDER BY m.nombre ASC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Verificar si existe asignación
     */
    public function exists($docente_id, $materia_id, $grupo_id) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE docente_id = ? AND materia_id = ? AND grupo_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iii", $docente_id, $materia_id, $grupo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }
}
