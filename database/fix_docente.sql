-- Script para verificar y arreglar el docente

-- Ver todos los usuarios tipo docente
SELECT u.usuario_id, u.nombre, u.email, u.tipo_usuario, d.docente_id
FROM usuarios u
LEFT JOIN docentes d ON u.usuario_id = d.usuario_id
WHERE u.tipo_usuario = 'docente';

-- Si tu usuario existe pero no tiene registro en docentes, ejecuta esto:
-- Reemplaza 'TU_EMAIL@gmail.com' con el email que usas para login

SET @mi_usuario_id = (SELECT usuario_id FROM usuarios WHERE email = 'cyo@gmail.com' LIMIT 1);

-- Crear docente si no existe
INSERT INTO docentes (usuario_id, especialidad, antiguedad, horas_maximas_semanales, horas_asignadas, tipo_contrato, estado, fecha_contratacion)
SELECT @mi_usuario_id, 'Programación', 5, 40, 0, 'tiempo_completo', 'activo', '2020-01-15'
WHERE @mi_usuario_id IS NOT NULL
AND NOT EXISTS (SELECT 1 FROM docentes WHERE usuario_id = @mi_usuario_id);

-- Verificar que ahora sí existe
SELECT u.usuario_id, u.nombre, u.email, d.docente_id, d.especialidad
FROM usuarios u
INNER JOIN docentes d ON u.usuario_id = d.usuario_id
WHERE u.email = 'cyo@gmail.com';
