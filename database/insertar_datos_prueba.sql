
-- Primero, verificar si existe el programa académico
INSERT IGNORE INTO programas_academicos (programa_id, codigo, nombre, nivel, duracion_semestres, estado, descripcion)
VALUES (1, 'ISC', 'Ingeniería en Sistemas Computacionales', 'licenciatura', 9, 'activo', 'Programa de Ingeniería en Sistemas');

-- INSERTAR USUARIO DOCENTE (si no existe)
INSERT INTO usuarios (nombre, apellido, email, password_hash, tipo_usuario, estado, fecha_creacion)
VALUES 
('Roberto', 'González', 'cyo@gmail.com', '$2y$10$1oTxvursCs/PBP2KnJ1V0uzQog95Uhe2lI3nmwITFoOuUA2Fj9aKu', 'docente', 'activo', NOW())
ON DUPLICATE KEY UPDATE usuario_id=LAST_INSERT_ID(usuario_id);

SET @usuario_id = LAST_INSERT_ID();

-- INSERTAR DOCENTE
INSERT INTO docentes (usuario_id, especialidad, antiguedad, horas_maximas_semanales, horas_asignadas, tipo_contrato, estado, fecha_contratacion)
VALUES (@usuario_id, 'Programación', 5, 40, 0, 'tiempo_completo', 'activo', '2020-01-15')
ON DUPLICATE KEY UPDATE docente_id=LAST_INSERT_ID(docente_id);

SET @docente_id = LAST_INSERT_ID();

-- INSERTAR MATERIAS
INSERT INTO materias (codigo, nombre, programa_id, creditos, semestre, es_obligatoria, tipo_materia, horas_semana, estado, descripcion)
VALUES 
('ISC-501', 'Programación Avanzada', 1, 8, 5, 1, 'teorica', 4, 'activa', 'Curso de programación avanzada'),
('ISC-502', 'Base de Datos II', 1, 7, 5, 1, 'teorica', 4, 'activa', 'Bases de datos avanzadas'),
('ISC-503', 'Desarrollo Web', 1, 6, 5, 1, 'practica', 4, 'activa', 'Desarrollo de aplicaciones web'),
('ISC-601', 'Inteligencia Artificial', 1, 8, 6, 1, 'teorica', 4, 'activa', 'Introducción a IA'),
('ISC-602', 'Redes de Computadoras', 1, 7, 6, 1, 'teorica', 4, 'activa', 'Redes y comunicaciones')
ON DUPLICATE KEY UPDATE materia_id=LAST_INSERT_ID(materia_id);

-- INSERTAR GRUPOS
INSERT INTO grupos (codigo, nombre, programa_id, semestre, cantidad_estudiantes, jornada, estado)
VALUES 
('5A', 'Grupo 5A', 1, 5, 30, 'matutina', 'activo'),
('5B', 'Grupo 5B', 1, 5, 28, 'vespertina', 'activo'),
('6A', 'Grupo 6A', 1, 6, 25, 'matutina', 'activo')
ON DUPLICATE KEY UPDATE grupo_id=LAST_INSERT_ID(grupo_id);

-- OBTENER IDs DE MATERIAS Y GRUPOS
SET @materia1_id = (SELECT materia_id FROM materias WHERE codigo = 'ISC-501' LIMIT 1);
SET @materia2_id = (SELECT materia_id FROM materias WHERE codigo = 'ISC-502' LIMIT 1);
SET @materia3_id = (SELECT materia_id FROM materias WHERE codigo = 'ISC-503' LIMIT 1);
SET @materia4_id = (SELECT materia_id FROM materias WHERE codigo = 'ISC-601' LIMIT 1);
SET @materia5_id = (SELECT materia_id FROM materias WHERE codigo = 'ISC-602' LIMIT 1);

SET @grupo1_id = (SELECT grupo_id FROM grupos WHERE codigo = '5A' LIMIT 1);
SET @grupo2_id = (SELECT grupo_id FROM grupos WHERE codigo = '5B' LIMIT 1);
SET @grupo3_id = (SELECT grupo_id FROM grupos WHERE codigo = '6A' LIMIT 1);

-- CREAR ASIGNACIONES (Docente + Materia + Grupo)
INSERT INTO asignaciones (docente_id, materia_id, grupo_id, estado, fecha_asignacion, fecha_solicitud, solicitada_por_docente, prioridad_asignacion)
VALUES 
(@docente_id, @materia1_id, @grupo1_id, 'confirmada', NOW(), NOW(), FALSE, 5),
(@docente_id, @materia2_id, @grupo1_id, 'confirmada', NOW(), NOW(), FALSE, 5),
(@docente_id, @materia3_id, @grupo2_id, 'confirmada', NOW(), NOW(), FALSE, 5),
(@docente_id, @materia4_id, @grupo3_id, 'confirmada', NOW(), NOW(), FALSE, 5)
ON DUPLICATE KEY UPDATE asignacion_id=LAST_INSERT_ID(asignacion_id);

-- MENSAJE DE CONFIRMACIÓN
SELECT 
    CONCAT('✅ Usuario docente creado: ', u.nombre, ' ', u.apellido, ' (', u.email, ')') as resultado
FROM usuarios u WHERE u.usuario_id = @usuario_id
UNION ALL
SELECT CONCAT('✅ Docente ID: ', @docente_id, ' - Especialidad: Programación')
UNION ALL
SELECT CONCAT('✅ ', COUNT(*), ' materias creadas') FROM materias WHERE codigo LIKE 'ISC-%'
UNION ALL
SELECT CONCAT('✅ ', COUNT(*), ' grupos creados') FROM grupos WHERE codigo LIKE '5%' OR codigo LIKE '6%'
UNION ALL
SELECT CONCAT('✅ ', COUNT(*), ' asignaciones creadas para el docente') FROM asignaciones WHERE docente_id = @docente_id;

-- MOSTRAR RESUMEN DE ASIGNACIONES
SELECT 
    CONCAT('📚 ', m.nombre, ' - Grupo ', g.codigo, ' (', g.jornada, ')') as 'Asignaciones del Docente'
FROM asignaciones a
INNER JOIN materias m ON a.materia_id = m.materia_id
INNER JOIN grupos g ON a.grupo_id = g.grupo_id
WHERE a.docente_id = @docente_id;
