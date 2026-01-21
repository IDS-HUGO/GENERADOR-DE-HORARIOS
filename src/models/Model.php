<?php
/**
 * Clase Base para Modelos
 * Proporciona funcionalidad CRUD común
 */

class Model {
    protected $db;
    protected $table;
    protected $id_column = 'id';
    
    public function __construct() {
        $this->db = getDatabase();
    }
    
    /**
     * Obtener todos los registros
     */
    public function getAll($limit = null, $offset = 0) {
        $query = "SELECT * FROM {$this->table}";
        
        if ($limit) {
            $query .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }
        
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Obtener por ID
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE {$this->id_column} = ?";
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            logError('Query Error', $this->db->error);
            return null;
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        return $data;
    }
    
    /**
     * Crear registro
     */
    public function create($data) {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $columnStr = implode(', ', $columns);
        $placeholderStr = implode(', ', $placeholders);
        $types = $this->getParamTypes($values);
        
        $query = "INSERT INTO {$this->table} ({$columnStr}) VALUES ({$placeholderStr})";
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            logError('Query Error', $this->db->error);
            return false;
        }
        
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        
        if ($result) {
            $lastId = $this->db->insert_id;
            $stmt->close();
            return $lastId;
        }
        
        $stmt->close();
        return false;
    }
    
    /**
     * Actualizar registro
     */
    public function update($id, $data) {
        $columns = array_keys($data);
        $values = array_values($data);
        $values[] = $id;
        
        $setClause = implode(', ', array_map(fn($col) => "$col = ?", $columns));
        $types = $this->getParamTypes($values);
        
        $query = "UPDATE {$this->table} SET {$setClause} WHERE {$this->id_column} = ?";
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            logError('Query Error', $this->db->error);
            return false;
        }
        
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Eliminar registro
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE {$this->id_column} = ?";
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            logError('Query Error', $this->db->error);
            return false;
        }
        
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Contar registros
     */
    public function count($where = null) {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if ($where) {
            $query .= " WHERE " . $where;
        }
        
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        
        return intval($row['total']);
    }
    
    /**
     * Buscar con condiciones
     */
    public function find($column, $value) {
        $query = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        $stmt = $this->db->prepare($query);
        
        if (!$stmt) {
            logError('Query Error', $this->db->error);
            return null;
        }
        
        $type = $this->getParamType($value);
        $stmt->bind_param($type, $value);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        return $data;
    }
    
    /**
     * Obtener tipos de parámetros para bind
     */
    protected function getParamTypes($values) {
        $types = '';
        foreach ($values as $value) {
            $types .= $this->getParamType($value);
        }
        return $types;
    }
    
    /**
     * Obtener tipo de parámetro individual
     */
    protected function getParamType($value) {
        if (is_int($value)) {
            return 'i';
        } elseif (is_float($value)) {
            return 'd';
        } else {
            return 's';
        }
    }
    
    /**
     * Ejecutar consulta personalizada
     */
    public function query($sql) {
        return $this->db->query($sql);
    }
    
    /**
     * Obtener últimas filas afectadas
     */
    public function affectedRows() {
        return $this->db->affected_rows;
    }
}
