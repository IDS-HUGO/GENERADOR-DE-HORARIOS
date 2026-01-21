<?php
/**
 * Modelo Usuario
 */

class Usuario extends Model {
    protected $table = 'usuarios';
    protected $id_column = 'usuario_id';
    
    /**
     * Obtener usuario por email
     */
    public function getByEmail($email) {
        return $this->find('email', $email);
    }
    
    /**
     * Obtener usuarios por tipo
     */
    public function getByType($tipo_usuario) {
        $query = "SELECT * FROM {$this->table} WHERE tipo_usuario = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $tipo_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
    
    /**
     * Obtener docentes activos
     */
    public function getActiveDocentes() {
        $query = "SELECT u.* FROM {$this->table} u 
                  WHERE u.tipo_usuario = 'docente' AND u.estado = 'activo'";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Crear usuario con validaciones
     */
    public function createWithValidation($data) {
        // Validar email
        if (!validateEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email inválido'];
        }
        
        // Verificar si existe
        if ($this->getByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email ya registrado'];
        }
        
        // Hashear contraseña
        $data['password_hash'] = hashPassword($data['password']);
        unset($data['password']);
        
        $id = $this->create($data);
        return $id ? ['success' => true, 'id' => $id] : ['success' => false, 'message' => 'Error al crear'];
    }
    
    /**
     * Autenticar usuario
     */
    public function authenticate($email, $password) {
        $user = $this->getByEmail($email);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        if ($user['estado'] !== 'activo') {
            return ['success' => false, 'message' => 'Usuario inactivo'];
        }
        
        if (!verifyPassword($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Contraseña incorrecta'];
        }
        
        return ['success' => true, 'user' => $user];
    }
}
