DROP DATABASE IF EXISTS classcontrol;
CREATE DATABASE classcontrol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE classcontrol;

-- =====================================================
-- 1. TABLA USUARIOS (Base para admin y docentes)
-- =====================================================
CREATE TABLE usuarios (
    usuario_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    telefono VARCHAR(15),
    tipo_usuario ENUM('administrador', 'docente') NOT NULL DEFAULT 'docente',
    password_hash VARCHAR(255) NOT NULL,
    estado ENUM('activo', 'inactivo', 'pendiente') DEFAULT 'pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_tipo_usuario (tipo_usuario)
);

-- =====================================================
-- 2. TABLA DOCENTES (Información específica de docentes)
-- =====================================================
CREATE TABLE docentes (
    docente_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL UNIQUE,
    numero_identificacion VARCHAR(20) UNIQUE,
    especialidad VARCHAR(100),
    horas_maximas_semanales INT DEFAULT 40,
    antiguedad INT DEFAULT 0,
    tipo_contrato ENUM('tiempo_completo', 'medio_tiempo', 'por_horas') DEFAULT 'por_horas',
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id) ON DELETE CASCADE,
    INDEX idx_numero_identificacion (numero_identificacion)
);

-- =====================================================
-- 3. TABLA DISPONIBILIDAD HORARIA (Horarios disponibles)
-- =====================================================
CREATE TABLE disponibilidad_horaria (
    disponibilidad_id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    dia_semana ENUM('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    disponible BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (docente_id) REFERENCES docentes(docente_id) ON DELETE CASCADE,
    INDEX idx_docente_dia (docente_id, dia_semana),
    CONSTRAINT chk_horas_validas CHECK (hora_inicio < hora_fin)
);

-- =====================================================
-- 4. TABLA PROGRAMAS ACADÉMICOS
-- =====================================================
CREATE TABLE programas_academicos (
    programa_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    descripcion TEXT,
    coordinador_id INT,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (coordinador_id) REFERENCES usuarios(usuario_id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo)
);

-- =====================================================
-- 5. TABLA MATERIAS / ASIGNATURAS
-- =====================================================
CREATE TABLE materias (
    materia_id INT AUTO_INCREMENT PRIMARY KEY,
    programa_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    descripcion TEXT,
    creditos INT DEFAULT 3,
    horas_semana INT DEFAULT 3,
    tipo_materia ENUM('teorica', 'practica', 'teorico_practica') DEFAULT 'teorica',
    es_obligatoria BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (programa_id) REFERENCES programas_academicos(programa_id) ON DELETE CASCADE,
    INDEX idx_codigo (codigo),
    INDEX idx_programa (programa_id)
);

-- =====================================================
-- 6. TABLA AULAS / ESPACIOS
-- =====================================================
CREATE TABLE aulas (
    aula_id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    capacidad INT NOT NULL,
    tipo_aula ENUM('teorica', 'laboratorio', 'taller', 'hibrida') DEFAULT 'teorica',
    equipamiento TEXT,
    estado ENUM('activa', 'inactiva', 'mantenimiento') DEFAULT 'activa',
    
    INDEX idx_codigo (codigo)
);

-- =====================================================
-- 7. TABLA GRUPOS ACADEMICOS
-- =====================================================
CREATE TABLE grupos (
    grupo_id INT AUTO_INCREMENT PRIMARY KEY,
    programa_id INT NOT NULL,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    semestre INT NOT NULL,
    cantidad_estudiantes INT DEFAULT 30,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    
    FOREIGN KEY (programa_id) REFERENCES programas_academicos(programa_id) ON DELETE CASCADE,
    INDEX idx_programa (programa_id),
    INDEX idx_codigo (codigo)
);

-- =====================================================
-- 8. TABLA ASIGNACIONES (Docente-Materia-Grupo)
-- =====================================================
CREATE TABLE asignaciones (
    asignacion_id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    materia_id INT NOT NULL,
    grupo_id INT NOT NULL,
    aula_id INT,
    estado ENUM('pendiente', 'asignada', 'confirmada', 'cancelada') DEFAULT 'pendiente',
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_confirmacion DATETIME,
    observaciones TEXT,
    
    FOREIGN KEY (docente_id) REFERENCES docentes(docente_id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias(materia_id) ON DELETE CASCADE,
    FOREIGN KEY (grupo_id) REFERENCES grupos(grupo_id) ON DELETE CASCADE,
    FOREIGN KEY (aula_id) REFERENCES aulas(aula_id) ON DELETE SET NULL,
    UNIQUE KEY uk_asignacion (docente_id, materia_id, grupo_id),
    INDEX idx_estado (estado)
);

-- =====================================================
-- 9. TABLA HORARIOS GENERADOS
-- =====================================================
CREATE TABLE horarios (
    horario_id INT AUTO_INCREMENT PRIMARY KEY,
    asignacion_id INT NOT NULL,
    dia_semana ENUM('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    salon_id INT,
    confirmado BOOLEAN DEFAULT FALSE,
    fecha_confirmacion DATETIME,
    
    FOREIGN KEY (asignacion_id) REFERENCES asignaciones(asignacion_id) ON DELETE CASCADE,
    FOREIGN KEY (salon_id) REFERENCES aulas(aula_id) ON DELETE SET NULL,
    INDEX idx_asignacion (asignacion_id),
    INDEX idx_dia_hora (dia_semana, hora_inicio),
    CONSTRAINT chk_horario_valido CHECK (hora_inicio < hora_fin)
);

-- =====================================================
-- 10. TABLA PREFERENCIAS DE DOCENTES
-- =====================================================
CREATE TABLE preferencias_docentes (
    preferencia_id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    materia_id INT NOT NULL,
    prioridad INT DEFAULT 0,
    experiencia_años INT DEFAULT 0,
    es_preferencia BOOLEAN DEFAULT TRUE,
    comentarios TEXT,
    
    FOREIGN KEY (docente_id) REFERENCES docentes(docente_id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias(materia_id) ON DELETE CASCADE,
    UNIQUE KEY uk_preferencia (docente_id, materia_id)
);

-- =====================================================
-- 11. TABLA RESTRICCIONES / CONFLICTOS
-- =====================================================
CREATE TABLE restricciones (
    restriccion_id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT,
    tipo_restriccion ENUM('no_disponible', 'conflicto_horario', 'otra') NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_inicio DATE,
    fecha_fin DATE,
    
    FOREIGN KEY (docente_id) REFERENCES docentes(docente_id) ON DELETE CASCADE,
    INDEX idx_docente (docente_id)
);

-- =====================================================
-- 12. TABLA HISTORIAL DE CAMBIOS (Auditoría)
-- =====================================================
CREATE TABLE historial_cambios (
    cambio_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    tabla_afectada VARCHAR(50) NOT NULL,
    registro_id INT NOT NULL,
    tipo_cambio ENUM('insert', 'update', 'delete') NOT NULL,
    datos_anteriores JSON,
    datos_nuevos JSON,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id) ON DELETE SET NULL,
    INDEX idx_fecha (fecha_cambio),
    INDEX idx_tabla (tabla_afectada)
);

-- =====================================================
-- 13. TABLA CONFIGURACIÓN DEL SISTEMA
-- =====================================================
CREATE TABLE configuracion_sistema (
    config_id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descripcion TEXT,
    tipo_dato ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    
    INDEX idx_clave (clave)
);

-- =====================================================
-- INSERTAR CONFIGURACIONES INICIALES
-- =====================================================
INSERT INTO configuracion_sistema (clave, valor, descripcion, tipo_dato) VALUES
('hora_inicio_clases', '08:00', 'Hora de inicio de clases', 'string'),
('hora_fin_clases', '20:00', 'Hora de fin de clases', 'string'),
('duracion_clase', '60', 'Duración de la clase en minutos', 'integer'),
('intervalo_entre_clases', '10', 'Intervalo entre clases en minutos', 'integer'),
('semestre_actual', '2026-1', 'Semestre actual del sistema', 'string');

-- =====================================================
-- CREAR ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- =====================================================
CREATE INDEX idx_docentes_usuario ON docentes(usuario_id);
CREATE INDEX idx_asignaciones_docente ON asignaciones(docente_id);
CREATE INDEX idx_asignaciones_materia ON asignaciones(materia_id);
CREATE INDEX idx_horarios_dia_hora ON horarios(dia_semana, hora_inicio, hora_fin);

-- =====================================================
-- INSERTAR USUARIO ADMINISTRADOR INICIAL
-- =====================================================
-- Usuario: admin@classcontrol.com
-- Contraseña: GenerateSecure123!
-- Nota: Cambiar contraseña inmediatamente en producción
INSERT INTO usuarios (nombre, apellido, email, telefono, tipo_usuario, password_hash, estado) 
VALUES (
    'Administrador',
    'Principal',
    'admin@classcontrol.com',
    '+57 300 0000000',
    'administrador',
    '$2y$10$K9PvjwKSVxwNWb6f5CuMIeVmLYVRCfMN0cG5j4mKxF0b9d6Q5Hm0C',
    'activo'
);

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
