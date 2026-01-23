-- Script para corregir horarios excesivamente largos

-- Ver horarios con más de 4 horas
SELECT 
    h.horario_id,
    m.nombre AS materia,
    g.codigo AS grupo,
    h.dia_semana,
    h.hora_inicio,
    h.hora_fin,
    TIMESTAMPDIFF(HOUR, 
        CONCAT('2000-01-01 ', h.hora_inicio), 
        CONCAT('2000-01-01 ', h.hora_fin)
    ) AS horas_duracion
FROM horarios h
LEFT JOIN asignaciones a ON h.asignacion_id = a.asignacion_id
LEFT JOIN materias m ON a.materia_id = m.materia_id
LEFT JOIN grupos g ON a.grupo_id = g.grupo_id
WHERE TIMESTAMPDIFF(HOUR, 
    CONCAT('2000-01-01 ', h.hora_inicio), 
    CONCAT('2000-01-01 ', h.hora_fin)
) > 4;

-- Corregir Desarrollo Web de 12:00-22:00 a 12:00-14:00
UPDATE horarios h
INNER JOIN asignaciones a ON h.asignacion_id = a.asignacion_id
INNER JOIN materias m ON a.materia_id = m.materia_id
SET h.hora_fin = '14:00:00'
WHERE m.codigo = 'ISC-503' 
  AND h.dia_semana = 'lunes'
  AND h.hora_inicio = '12:00:00'
  AND h.hora_fin = '22:00:00';

-- Verificar corrección
SELECT 
    h.horario_id,
    m.nombre AS materia,
    g.codigo AS grupo,
    h.dia_semana,
    h.hora_inicio,
    h.hora_fin
FROM horarios h
LEFT JOIN asignaciones a ON h.asignacion_id = a.asignacion_id
LEFT JOIN materias m ON a.materia_id = m.materia_id
LEFT JOIN grupos g ON a.grupo_id = g.grupo_id
WHERE m.codigo = 'ISC-503' 
  AND h.dia_semana = 'lunes';
