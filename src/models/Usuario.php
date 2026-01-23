<?php
/**
 * Modelo Usuario
 * Sistema de Gestión de Horarios - Universidad Maya
 * Copyright (c) 2026 Universidad Maya - Todos los derechos reservados
 */

class Usuario extends Model {
    protected $table = 'usuarios';
    protected $id_column = 'usuario_id';
    
    // Tipos de usuario
    const TIPO_DIRECTOR = 'director';
    const TIPO_ADMINISTRADOR = 'administrador';
    const TIPO_DOCENTE = 'docente';
    
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
        $query = "SELECT * FROM {$this->table} WHERE tipo_usuario = ? ORDER BY nombre, apellido";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $tipo_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
    
    /**
     * Obtener directores activos
     */
    public function getActiveDirectores() {
        return $this->getByType(self::TIPO_DIRECTOR);
    }
    
    /**
     * Obtener administradores activos
     */
    public function getActiveAdministradores() {
        return $this->getByType(self::TIPO_ADMINISTRADOR);
    }
    
    /**
     * Verificar si es director
     */
    public function isDirector($usuario_id) {
        $user = $this->getById($usuario_id);
        return $user && $user['tipo_usuario'] === self::TIPO_DIRECTOR;
    }
    
    /**
     * Verificar si es administrador
     */
    public function isAdministrador($usuario_id) {
        $user = $this->getById($usuario_id);
        return $user && $user['tipo_usuario'] === self::TIPO_ADMINISTRADOR;
    }
    
    /**
     * Verificar si es docente
     */
    public function isDocente($usuario_id) {
        $user = $this->getById($usuario_id);
        return $user && $user['tipo_usuario'] === self::TIPO_DOCENTE;
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
        
        // Actualizar último acceso
        $this->update($user['usuario_id'], ['ultimo_acceso' => date('Y-m-d H:i:s')]);
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Obtener todos los usuarios con rol y estado
     */
    public function getAllWithDetails() {
        $query = "SELECT 
                    u.usuario_id,
                    u.nombre,
                    u.apellido,
                    u.email,
                    u.telefono,
                    u.tipo_usuario,
                    u.estado,
                    u.ultimo_acceso,
                    u.fecha_creacion,
                    CASE 
                        WHEN u.tipo_usuario = 'director' THEN 'Director'
                        WHEN u.tipo_usuario = 'administrador' THEN 'Administrador'
                        WHEN u.tipo_usuario = 'docente' THEN 'Docente'
                    END as rol_nombre
                  FROM {$this->table} u
                  ORDER BY 
                    FIELD(u.tipo_usuario, 'director', 'administrador', 'docente'),
                    u.nombre, u.apellido";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
