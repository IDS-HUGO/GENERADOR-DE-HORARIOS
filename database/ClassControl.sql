-- ==================================================================
-- ClassControl - Sistema de Gestión de Horarios Académicos
-- Universidad Maya - Todos los derechos reservados
-- Copyright (c) 2026 Universidad Maya
-- Versión: 2.1 - Gestión completa de horarios y aprobación final
-- ==================================================================

DROP DATABASE IF EXISTS classcontrol;
CREATE DATABASE classcontrol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE classcontrol;

-- USUARIOS (Directores, Administradores, Docentes)
CREATE TABLE usuarios (
    usuario_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    telefono VARCHAR(15),
    tipo_usuario ENUM('director', 'administrador', 'docente') NOT NULL DEFAULT 'docente',
    password_hash VARCHAR(255) NOT NULL,
    estado ENUM('activo', 'inactivo', 'pendiente') DEFAULT 'pendiente',
    ultimo_acceso DATETIME,
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta DATETIME,
    notas TEXT COMMENT 'Observaciones administrativas',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_tipo_usuario (tipo_usuario),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DOCENTES (Con campos mejorados)
CREATE TABLE docentes (
    docente_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL UNIQUE,
    numero_identificacion VARCHAR(20) UNIQUE,
    especialidad VARCHAR(100),
    horas_asignadas INT DEFAULT 0,
    horas_maximas_semanales INT DEFAULT 40,
    antiguedad INT DEFAULT 0 COMMENT 'Años de antigüedad en la institución',
    tipo_contrato ENUM('tiempo_completo', 'medio_tiempo', 'por_horas') DEFAULT 'por_horas',
    foto_perfil VARCHAR(255),
    fecha_contratacion DATE,
    estado ENUM('activo', 'inactivo', 'licencia', 'jubilado') DEFAULT 'activo',
    prioridad_seleccion INT DEFAULT 0 COMMENT 'Mayor antigüedad = mayor prioridad',
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id) ON DELETE CASCADE,
    INDEX idx_numero_identificacion (numero_identificacion),
    INDEX idx_estado (estado),
    INDEX idx_antiguedad (antiguedad DESC),
    INDEX idx_prioridad (prioridad_seleccion DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DISPONIBILIDAD HORARIA
CREATE TABLE disponibilidad_horaria (
    disponibilidad_id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    dia_semana ENUM('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    disponible BOOLEAN DEFAULT TRUE,
    tipo_disponibilidad ENUM('preferente', 'disponible', 'no_disponible') DEFAULT 'disponible',
    
    FOREIGN KEY (docente_id) REFERENCES docentes(docente_id) ON DELETE CASCADE,
    INDEX idx_docente_dia (docente_id, dia_semana),
    INDEX idx_disponible (disponible),
    CONSTRAINT chk_horas_validas CHECK (hora_inicio < hora_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PROGRAMAS ACADEMICOS
CREATE TABLE programas_academicos (
    programa_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    descripcion TEXT,
    coordinador_id INT,
    duracion_semestres INT DEFAULT 8,
    modalidad ENUM('presencial', 'virtual', 'hibrida') DEFAULT 'presencial',
    nivel ENUM('tecnico', 'profesional', 'especializacion', 'maestria') DEFAULT 'profesional',
    estado ENUM('activo', 'inactivo', 'suspendido') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (coordinador_id) REFERENCES usuarios(usuario_id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MATERIAS
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
    semestre INT,
    prerequisito_id INT,
    estado ENUM('activa', 'inactiva', 'suspendida') DEFAULT 'activa',
    
    FOREIGN KEY (programa_id) REFERENCES programas_academicos(programa_id) ON DELETE CASCADE,
    FOREIGN KEY (prerequisito_id) REFERENCES materias(materia_id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_programa (programa_id),
    INDEX idx_semestre (semestre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AULAS
CREATE TABLE aulas (
    aula_id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    edificio VARCHAR(50),
    piso INT,
    capacidad INT NOT NULL,
    tipo_aula ENUM('teorica', 'laboratorio', 'taller', 'hibrida', 'auditorio') DEFAULT 'teorica',
    equipamiento TEXT,
    recurso_especial TEXT,
    estado ENUM('activa', 'inactiva', 'mantenimiento') DEFAULT 'activa',
    
    INDEX idx_codigo (codigo),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- GRUPOS
CREATE TABLE grupos (
    grupo_id INT AUTO_INCREMENT PRIMARY KEY,
    programa_id INT NOT NULL,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    semestre INT NOT NULL,
    cantidad_estudiantes INT DEFAULT 30,
    jornada ENUM('matutina', 'vespertina', 'nocturna') DEFAULT 'matutina',
    estado ENUM('activo', 'inactivo', 'suspendido') DEFAULT 'activo',
    
    FOREIGN KEY (programa_id) REFERENCES programas_academicos(programa_id) ON DELETE CASCADE,
    INDEX idx_programa (programa_id),
    INDEX idx_codigo (codigo),
    INDEX idx_semestre (semestre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ASIGNACIONES (Mejorado con solicitudes de docentes)
CREATE TABLE asignaciones (
    asignacion_id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    materia_id INT NOT NULL,
    grupo_id INT NOT NULL,
    aula_id INT,
    numero_horas INT DEFAULT 3,
    estado ENUM('solicitada', 'pendiente', 'asignada', 'confirmada', 'cancelada', 'completada') DEFAULT 'pendiente',
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_confirmacion DATETIME,
    fecha_solicitud DATETIME COMMENT 'Fecha en que el docente solicitó esta asignación',
    solicitada_por_docente BOOLEAN DEFAULT FALSE COMMENT 'TRUE si el docente eligió esta materia/grupo',
    aprobada_por_admin BOOLEAN DEFAULT FALSE,
    prioridad_asignacion INT DEFAULT 0 COMMENT 'Basada en antigüedad del docente',
    observaciones TEXT,
    calificacion DECIMAL(3,2),
    
    FOREIGN KEY (docente_id) REFERENCES docentes(docente_id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias(materia_id) ON DELETE CASCADE,
    FOREIGN KEY (grupo_id) REFERENCES grupos(grupo_id) ON DELETE CASCADE,
    FOREIGN KEY (aula_id) REFERENCES aulas(aula_id) ON DELETE SET NULL,
    UNIQUE KEY uk_asignacion (docente_id, materia_id, grupo_id),
    INDEX idx_estado (estado),
    INDEX idx_docente (docente_id),
    INDEX idx_solicitada_por_docente (solicitada_por_docente),
    INDEX idx_grupo (grupo_id),
    INDEX idx_prioridad (prioridad_asignacion DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- HORARIOS (con aprobación final por Admin/Director)
CREATE TABLE horarios (
    horario_id INT AUTO_INCREMENT PRIMARY KEY,
    asignacion_id INT NOT NULL,
    dia_semana ENUM('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    aula_id INT,
    estado_horario ENUM('borrador', 'pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    confirmado BOOLEAN DEFAULT FALSE,
    fecha_confirmacion DATETIME,
    aprobado_por INT NULL,
    aprobado_rol ENUM('director', 'administrador') NULL,
    observaciones TEXT,
    conflicto_detectado BOOLEAN DEFAULT FALSE,
    
    FOREIGN KEY (asignacion_id) REFERENCES asignaciones(asignacion_id) ON DELETE CASCADE,
    FOREIGN KEY (aula_id) REFERENCES aulas(aula_id) ON DELETE SET NULL,
    FOREIGN KEY (aprobado_por) REFERENCES usuarios(usuario_id) ON DELETE SET NULL,
    INDEX idx_asignacion (asignacion_id),
    INDEX idx_dia_hora (dia_semana, hora_inicio),
    INDEX idx_estado_horario (estado_horario),
    INDEX idx_confirmado (confirmado),
    INDEX idx_aprobado_por (aprobado_por),
    CONSTRAINT chk_horario_valido CHECK (hora_inicio < hora_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PREFERENCIAS DOCENTES
CREATE TABLE preferencias_docentes (
    preferencia_id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    materia_id INT NOT NULL,
    prioridad INT DEFAULT 0,
    experiencia_anos INT DEFAULT 0,
    es_preferencia BOOLEAN DEFAULT TRUE,
    nivel_dominio ENUM('basico', 'intermedio', 'avanzado', 'experto') DEFAULT 'basico',
    comentarios TEXT,
    
    FOREIGN KEY (docente_id) REFERENCES docentes(docente_id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias(materia_id) ON DELETE CASCADE,
    UNIQUE KEY uk_preferencia (docente_id, materia_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- RESTRICCIONES
CREATE TABLE restricciones (
    restriccion_id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT,
    tipo_restriccion ENUM('no_disponible', 'conflicto_horario', 'licencia', 'capacitacion', 'otra') NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('activa', 'completada', 'cancelada') DEFAULT 'activa',
    
    FOREIGN KEY (docente_id) REFERENCES docentes(docente_id) ON DELETE CASCADE,
    INDEX idx_docente (docente_id),
    INDEX idx_fechas (fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- HISTORIAL CAMBIOS
CREATE TABLE historial_cambios (
    cambio_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    tabla_afectada VARCHAR(50) NOT NULL,
    registro_id INT NOT NULL,
    tipo_cambio ENUM('insert', 'update', 'delete') NOT NULL,
    datos_anteriores JSON,
    datos_nuevos JSON,
    descripcion TEXT,
    ip_address VARCHAR(45),
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id) ON DELETE SET NULL,
    INDEX idx_fecha (fecha_cambio),
    INDEX idx_tabla (tabla_afectada),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CONFIGURACION
CREATE TABLE configuracion_sistema (
    config_id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descripcion TEXT,
    tipo_dato ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    editable BOOLEAN DEFAULT TRUE,
    
    INDEX idx_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NOTIFICACIONES
CREATE TABLE notificaciones (
    notificacion_id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo ENUM('info', 'exito', 'advertencia', 'error') DEFAULT 'info',
    leida BOOLEAN DEFAULT FALSE,
    enlace VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(usuario_id) ON DELETE CASCADE,
    INDEX idx_usuario_leida (usuario_id, leida),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CONFIG INICIAL (Universidad Maya)
INSERT INTO configuracion_sistema (clave, valor, descripcion, tipo_dato, editable) VALUES
('hora_inicio_clases', '08:00', 'Hora de inicio de clases', 'string', TRUE),
('hora_fin_clases', '20:00', 'Hora de fin de clases', 'string', TRUE),
('duracion_clase', '60', 'Duración de la clase en minutos', 'integer', TRUE),
('intervalo_entre_clases', '10', 'Intervalo entre clases en minutos', 'integer', TRUE),
('semestre_actual', '2026-1', 'Semestre actual del sistema', 'string', TRUE),
('max_horas_docente', '40', 'Máximo de horas por docente por semana', 'integer', TRUE),
('nombre_institucion', 'Universidad Maya', 'Nombre de la institución', 'string', TRUE),
('email_institucion', 'info@universidadmaya.edu', 'Email institucional', 'string', TRUE),
('telefono_institucion', '+52 999 000 0000', 'Teléfono institucional', 'string', TRUE),
('prioridad_por_antiguedad', 'true', 'Activar prioridad por años de antigüedad', 'boolean', TRUE),
('max_asignaciones_por_docente', '6', 'Máximo de materias que puede elegir un docente', 'integer', TRUE);

-- INDICES ADICIONALES PARA PERFORMANCE
CREATE INDEX idx_docentes_usuario ON docentes(usuario_id);
CREATE INDEX idx_asignaciones_docente ON asignaciones(docente_id);
CREATE INDEX idx_asignaciones_materia ON asignaciones(materia_id);
CREATE INDEX idx_asignaciones_grupo ON asignaciones(grupo_id);
CREATE INDEX idx_horarios_dia_hora ON horarios(dia_semana, hora_inicio, hora_fin);
CREATE INDEX idx_usuarios_email_estado ON usuarios(email, estado);
CREATE INDEX idx_docentes_estado_usuario ON docentes(estado, usuario_id);

-- ==================================================================
-- DATOS INICIALES
-- ==================================================================

-- DIRECTOR GENERAL (director@universidadmaya.edu / director123)
INSERT INTO usuarios (nombre, apellido, email, telefono, tipo_usuario, password_hash, estado) 
VALUES (
    'Director',
    'General',
    'director@universidadmaya.edu',
    '+52 999 000 0001',
    'director',
    '$2y$10$W9gTzf7scl/rT0gf6myvjOgVQkHVwffA1hMtPsd/wKeaHfG5FYVlS',
    'activo'
);

-- ADMIN (admin@universidadmaya.edu / admin123)
INSERT INTO usuarios (nombre, apellido, email, telefono, tipo_usuario, password_hash, estado) 
VALUES (
    'Administrador',
    'Principal',
    'admin@universidadmaya.edu',
    '+52 999 000 0002',
    'administrador',
    '$2y$10$1oTxvursCs/PBP2KnJ1V0uzQog95Uhe2lI3nmwITFoOuUA2Fj9aKu',
    'activo'
);

-- DATOS EJEMPLO
INSERT INTO programas_academicos (nombre, codigo, descripcion, duracion_semestres, modalidad, nivel, estado) 
VALUES (
    'Desarrollo de Software',
    'DS-01',
    'Programa de formación en desarrollo de aplicaciones web y móvil',
    8,
    'presencial',
    'profesional',
    'activo'
);

INSERT INTO materias (programa_id, nombre, codigo, descripcion, creditos, horas_semana, tipo_materia, es_obligatoria, semestre) 
VALUES 
(1, 'Fundamentos de Programación', 'FP-01', 'Introducción a la lógica de programación', 3, 3, 'teorico_practica', TRUE, 1),
(1, 'Bases de Datos', 'BD-01', 'Diseño y administración de bases de datos', 3, 3, 'teorico_practica', TRUE, 2),
(1, 'Desarrollo Web', 'DW-01', 'Creación de aplicaciones web modernas', 3, 3, 'teorico_practica', TRUE, 3),
(1, 'Aplicaciones Móvil', 'AM-01', 'Desarrollo de aplicaciones para dispositivos móviles', 3, 3, 'teorico_practica', TRUE, 4);

INSERT INTO aulas (codigo, nombre, edificio, piso, capacidad, tipo_aula, equipamiento, estado) 
VALUES 
('A-101', 'Aula Teoría 101', 'Edificio A', 1, 40, 'teorica', 'Proyector, computador, pizarra', 'activa'),
('A-102', 'Aula Teoría 102', 'Edificio A', 1, 35, 'teorica', 'Proyector, computador', 'activa'),
('B-201', 'Laboratorio 1', 'Edificio B', 2, 30, 'laboratorio', '30 computadores, servidor, switch', 'activa'),
('B-202', 'Laboratorio 2', 'Edificio B', 2, 25, 'laboratorio', '25 computadores, servidor', 'activa');

INSERT INTO grupos (programa_id, codigo, nombre, semestre, cantidad_estudiantes, jornada, estado) 
VALUES 
(1, 'G-101', 'Grupo 101 - Desarrollo Software Semestre I', 1, 35, 'matutina', 'activo'),
(1, 'G-102', 'Grupo 102 - Desarrollo Software Semestre II', 2, 32, 'vespertina', 'activo'),
(1, 'G-103', 'Grupo 103 - Desarrollo Software Semestre III', 3, 30, 'matutina', 'activo'),
(1, 'G-104', 'Grupo 104 - Desarrollo Software Semestre IV', 4, 28, 'nocturna', 'activo');

-- VERIFICACION
SELECT 
    'Usuarios' as entidad, COUNT(*) as cantidad FROM usuarios
UNION ALL
SELECT 'Programas', COUNT(*) FROM programas_academicos
UNION ALL
SELECT 'Materias', COUNT(*) FROM materias
UNION ALL
SELECT 'Aulas', COUNT(*) FROM aulas
UNION ALL
SELECT 'Grupos', COUNT(*) FROM grupos;
