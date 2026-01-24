<?php
/**
 * Modelo Docente
 * Sistema de Gestión de Horarios - Universidad Maya
 * Copyright (c) 2026 Universidad Maya - Todos los derechos reservados
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
    
    /**
     * Obtener grupos disponibles para selección (docente puede elegir)
     */
    public function getGruposDisponibles($docente_id) {
        $query = "SELECT 
                    g.grupo_id,
                    g.codigo,
                    g.nombre,
                    g.semestre,
                    g.cantidad_estudiantes,
                    g.jornada,
                    p.nombre as programa_nombre,
                    COUNT(a.asignacion_id) as materias_asignadas
                  FROM grupos g
                  INNER JOIN programas_academicos p ON g.programa_id = p.programa_id
                  LEFT JOIN asignaciones a ON g.grupo_id = a.grupo_id
                  WHERE g.estado = 'activo'
                  GROUP BY g.grupo_id
                  ORDER BY g.semestre, g.codigo";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Obtener materias disponibles para un grupo específico
     */
    public function getMateriasDisponiblesPorGrupo($grupo_id, $docente_id) {
        $query = "SELECT 
                    m.materia_id,
                    m.nombre,
                    m.codigo,
                    m.creditos,
                    m.horas_semana,
                    m.tipo_materia,
                    m.semestre,
                    g.nombre as grupo_nombre,
                    CASE WHEN a.asignacion_id IS NOT NULL THEN 1 ELSE 0 END as ya_asignada,
                    CASE WHEN a.docente_id = ? THEN 1 ELSE 0 END as asignada_a_mi
                  FROM materias m
                  INNER JOIN grupos g ON m.programa_id = g.programa_id AND m.semestre = g.semestre
                  LEFT JOIN asignaciones a ON m.materia_id = a.materia_id AND g.grupo_id = a.grupo_id
                  WHERE g.grupo_id = ? AND m.estado = 'activa'
                  ORDER BY m.codigo";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $docente_id, $grupo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
    
    /**
     * Solicitar asignación (docente elige materia + grupo)
     */
    public function solicitarAsignacion($docente_id, $materia_id, $grupo_id) {
        // Verificar que el docente existe
        $docente = $this->getById($docente_id);
        if (!$docente) {
            return ['success' => false, 'message' => 'Docente no encontrado'];
        }
        
        // Calcular prioridad basada en antigüedad
        $prioridad = $docente['antiguedad'] ?? 0;
        
        // Verificar si ya existe la asignación
        $check_query = "SELECT asignacion_id FROM asignaciones 
                        WHERE docente_id = ? AND materia_id = ? AND grupo_id = ?";
        $stmt = $this->db->prepare($check_query);
        $stmt->bind_param("iii", $docente_id, $materia_id, $grupo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Ya solicitaste esta materia/grupo'];
        }
        $stmt->close();
        
        // Crear solicitud de asignación
        $insert_query = "INSERT INTO asignaciones 
                        (docente_id, materia_id, grupo_id, estado, fecha_solicitud, 
                         solicitada_por_docente, prioridad_asignacion) 
                        VALUES (?, ?, ?, 'solicitada', NOW(), TRUE, ?)";
        $stmt = $this->db->prepare($insert_query);
        $stmt->bind_param("iiii", $docente_id, $materia_id, $grupo_id, $prioridad);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success ? 
            ['success' => true, 'message' => 'Solicitud enviada correctamente'] : 
            ['success' => false, 'message' => 'Error al crear solicitud'];
    }
    
    /**
     * Obtener mis solicitudes pendientes
     */
    public function getMisSolicitudes($docente_id) {
        $query = "SELECT 
                    a.asignacion_id,
                    a.estado,
                    a.fecha_solicitud,
                    a.prioridad_asignacion,
                    m.nombre as materia_nombre,
                    m.codigo as materia_codigo,
                    m.horas_semana,
                    g.nombre as grupo_nombre,
                    g.codigo as grupo_codigo,
                    g.jornada
                  FROM asignaciones a
                  INNER JOIN materias m ON a.materia_id = m.materia_id
                  INNER JOIN grupos g ON a.grupo_id = g.grupo_id
                  WHERE a.docente_id = ? AND a.solicitada_por_docente = TRUE
                  ORDER BY a.estado, a.fecha_solicitud DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $docente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    /**
     * Obtener materias asignadas al docente con sus horarios
     */
    public function getMateriasAsignadas($docente_id) {
        $query = "SELECT 
                    a.asignacion_id,
                    m.materia_id,
                    m.nombre,
                    m.codigo,
                    m.creditos,
                    m.horas_semana,
                    g.grupo_id,
                    g.nombre as grupo_nombre,
                    g.codigo as grupo_codigo,
                    g.semestre,
                    g.jornada,
                    g.cantidad_estudiantes,
                    p.nombre as programa_nombre,
                    a.estado,
                    a.prioridad_asignacion,
                    a.fecha_asignacion,
                    GROUP_CONCAT(CONCAT(h.dia_semana, ' ', h.hora_inicio, '-', h.hora_fin, ' (Aula: ', COALESCE(au.nombre, 'Por definir'), ')') ORDER BY FIELD(h.dia_semana, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'), h.hora_inicio SEPARATOR ' | ') as horarios
                  FROM asignaciones a
                  INNER JOIN materias m ON a.materia_id = m.materia_id
                  INNER JOIN grupos g ON a.grupo_id = g.grupo_id
                  INNER JOIN programas_academicos p ON g.programa_id = p.programa_id
                  LEFT JOIN horarios h ON a.asignacion_id = h.asignacion_id
                  LEFT JOIN aulas au ON h.aula_id = au.aula_id
                  WHERE a.docente_id = ?
                  GROUP BY a.asignacion_id
                  ORDER BY g.semestre, m.codigo";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $docente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }
}
