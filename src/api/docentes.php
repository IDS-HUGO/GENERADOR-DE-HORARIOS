<?php
/**
 * API de Docentes - Gestión completa
 */

require_once '../config.php';
require_once INCLUDES_PATH . '/Models.php';

if (!isAuthenticated()) {
    jsonResponse(false, 'No autorizado', null, 401);
}

$action = $_GET['action'] ?? '';
$user = getCurrentUser();

try {
    switch ($action) {
        case 'list':
            $docentes = (new Docente())->getActivosConUsuario();
            jsonResponse(true, '', $docentes);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            $docente = (new Docente())->getById($id);
            if (!$docente) jsonResponse(false, 'Docente no encontrado', null, 404);
            
            jsonResponse(true, '', $docente);
            break;

        case 'profile':
            // Perfil del docente autenticado
            if ($user['tipo_usuario'] !== 'docente') {
                jsonResponse(false, 'Solo docentes pueden consultar este perfil', null, 403);
            }

            $docenteModel = new Docente();
            $usuarioModel = new Usuario();
            $docente = $docenteModel->getByUserId($user['usuario_id']);

            if (!$docente) {
                jsonResponse(false, 'Perfil de docente no encontrado', null, 404);
            }

            $usuario = $usuarioModel->getById($user['usuario_id']);
            if ($usuario) {
                unset($usuario['password_hash'], $usuario['intentos_fallidos'], $usuario['bloqueado_hasta']);
            }

            jsonResponse(true, '', [
                'usuario' => $usuario,
                'docente' => $docente
            ]);
            break;

        case 'create':
            if ($user['tipo_usuario'] !== 'administrador') {
                jsonResponse(false, 'Solo administradores pueden crear docentes', null, 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validar datos requeridos
            $campos_requeridos = ['nombre', 'apellido', 'email', 'especialidad'];
            foreach ($campos_requeridos as $campo) {
                if (empty($data[$campo])) {
                    jsonResponse(false, "El campo $campo es requerido", null, 400);
                }
            }
            
            // Validar email válido
            if (!validateEmail($data['email'])) {
                jsonResponse(false, 'Email inválido', null, 400);
            }
            
            // Validar email único
            $existingUser = (new Usuario())->getByEmail($data['email']);
            if ($existingUser) {
                jsonResponse(false, 'El email ya está registrado', null, 409);
            }
            
            // Generar contraseña temporal
            $tempPassword = generateTemporaryPassword(12);
            
            
            try {
                // 1. CREAR USUARIO (para login)
                $usuarioId = (new Usuario())->create([
                    'email' => $data['email'],
                    'password_hash' => hashPassword($tempPassword),
                    'tipo_usuario' => 'docente',
                    'estado' => 'activo',
                    'nombre' => $data['nombre'],
                    'apellido' => $data['apellido']
                ]);
                
                if (!$usuarioId) {
                    jsonResponse(false, 'Error al crear usuario', null, 500);
                }
                
                // 2. CREAR PERFIL DOCENTE (con todos los campos)
                $docenteData = [
                    'usuario_id' => $usuarioId,
                    'numero_identificacion' => $data['numero_identificacion'] ?? null,
                    'especialidad' => $data['especialidad'],
                    'horas_asignadas' => $data['horas_asignadas'] ?? 20,
                    'horas_maximas_semanales' => $data['horas_maximas_semanales'] ?? 40,
                    'antiguedad' => $data['antiguedad'] ?? 0,
                    'tipo_contrato' => $data['tipo_contrato'] ?? 'por_horas',
                    'foto_perfil' => $data['foto_perfil'] ?? null,
                    'fecha_contratacion' => $data['fecha_contratacion'] ?? date('Y-m-d'),
                    'estado' => 'activo'
                ];
                
                $docenteId = (new Docente())->create($docenteData);
                
                if (!$docenteId) {
                    jsonResponse(false, 'Error al crear perfil docente', null, 500);
                }
                
                // 3. Crear disponibilidad inicial (no bloqueante)
                try {
                    (new DisponibilidadHoraria())->createInitialAvailability($docenteId);
                } catch (Exception $e) {
                    // No fallar si la disponibilidad no se crea
                }
                
                // 4. ENVIAR EMAIL CON CREDENCIALES
                $emailEnviado = false;
                $mensajeEmail = '';
                
                if (!empty($_ENV['GMAIL_EMAIL'])) {
                    $emailEnviado = enviarEmailCredenciales(
                        $data['email'],
                        $data['nombre'],
                        $data['apellido'],
                        $tempPassword
                    );
                    $mensajeEmail = $emailEnviado 
                        ? "✅ Email enviado a {$data['email']}"
                        : "⚠️ No se pudo enviar email (XAMPP sin SMTP configurado)";
                }
                
                // Mensaje final
                $mensaje = "✅ Docente {$data['nombre']} creado exitosamente | " . $mensajeEmail;
                
                jsonResponse(true, $mensaje, [
                    'docente_id' => $docenteId,
                    'usuario_id' => $usuarioId,
                    'email' => $data['email'],
                    'nombre' => $data['nombre'],
                    'apellido' => $data['apellido'],
                    'password_temporal' => $tempPassword,
                    'email_enviado' => $emailEnviado,
                    'especialidad' => $data['especialidad']
                ]);
                
            } catch (Exception $e) {
                logError('DOCENTE_CREATE_ERROR', $e->getMessage());
                jsonResponse(false, 'Error: ' . $e->getMessage(), null, 500);
            }
            break;

        case 'update':
            if ($user['tipo_usuario'] !== 'administrador') {
                jsonResponse(false, 'Solo administradores pueden editar docentes', null, 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            $docente = (new Docente())->getById($id);
            if (!$docente) jsonResponse(false, 'Docente no encontrado', null, 404);
            
            // Actualizar datos en usuarios (nombre, apellido, email opcional)
            $usuarioModel = new Usuario();
            $usuarioUpdate = array_filter([
                'nombre' => $data['nombre'] ?? null,
                'apellido' => $data['apellido'] ?? null,
                'email' => $data['email'] ?? null
            ], fn($v) => $v !== null);

            if (!empty($usuarioUpdate)) {
                $usuarioModel->update($docente['usuario_id'], $usuarioUpdate);
            }

            // Actualizar datos en docentes (todos los campos propios)
            $docenteUpdate = array_filter([
                'numero_identificacion' => $data['numero_identificacion'] ?? null,
                'especialidad' => $data['especialidad'] ?? null,
                'horas_asignadas' => $data['horas_asignadas'] ?? null,
                'horas_maximas_semanales' => $data['horas_maximas_semanales'] ?? null,
                'antiguedad' => $data['antiguedad'] ?? null,
                'tipo_contrato' => $data['tipo_contrato'] ?? null,
                'foto_perfil' => $data['foto_perfil'] ?? null,
                'fecha_contratacion' => $data['fecha_contratacion'] ?? null,
                'estado' => $data['estado'] ?? null
            ], fn($v) => $v !== null);

            if (!empty($docenteUpdate)) {
                (new Docente())->update($id, $docenteUpdate);
            }

            jsonResponse(true, 'Docente actualizado correctamente');
            break;

        case 'asignaciones_confirmadas':
            // Obtener asignaciones confirmadas del docente con horarios si existen
            if ($user['tipo_usuario'] !== 'docente') {
                jsonResponse(false, 'Solo docentes pueden consultar asignaciones', null, 403);
            }

            $docenteModel = new Docente();
            $docente = $docenteModel->getByUserId($user['usuario_id']);

            if (!$docente) {
                jsonResponse(false, 'Perfil de docente no encontrado', null, 404);
            }

            $docente_id = $docente['docente_id'];
            $db = getDatabase();
            
            // Obtener asignaciones confirmadas con horarios si existen
            // Incluye estados: confirmada, asignada y cualquier otra que tenga un docente_id
            $query = "SELECT 
                        a.asignacion_id,
                        a.docente_id,
                        a.materia_id,
                        a.grupo_id,
                        a.estado as asignacion_estado,
                        m.nombre as materia_nombre,
                        m.codigo as materia_codigo,
                        m.creditos,
                        m.horas_semana,
                        g.nombre as grupo_nombre,
                        g.codigo as grupo_codigo,
                        g.semestre,
                        g.jornada,
                        h.horario_id,
                        h.dia_semana,
                        h.hora_inicio,
                        h.hora_fin,
                        h.estado_horario,
                        au.codigo as aula_codigo,
                        p.nombre as programa_nombre
                      FROM asignaciones a
                      INNER JOIN materias m ON a.materia_id = m.materia_id
                      INNER JOIN grupos g ON a.grupo_id = g.grupo_id
                      LEFT JOIN programas_academicos p ON g.programa_id = p.programa_id
                      LEFT JOIN horarios h ON a.asignacion_id = h.asignacion_id
                      LEFT JOIN aulas au ON h.aula_id = au.aula_id
                      WHERE a.docente_id = ?
                      ORDER BY g.codigo, COALESCE(h.dia_semana, ''), COALESCE(h.hora_inicio, '')";
            
            $stmt = $db->prepare($query);
            $stmt->bind_param("i", $docente_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $asignaciones = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            jsonResponse(true, '', $asignaciones);
            break;

        case 'crear_horario':
            // Permitir que el docente cree sus propios horarios
            if ($user['tipo_usuario'] !== 'docente') {
                jsonResponse(false, 'Solo docentes pueden crear horarios', null, 403);
            }

            $data = json_decode(file_get_contents('php://input'), true);
            $docenteModel = new Docente();
            $docente = $docenteModel->getByUserId($user['usuario_id']);

            if (!$docente) {
                jsonResponse(false, 'Perfil de docente no encontrado', null, 404);
            }

            $docente_id = $docente['docente_id'];
            
            // Validar campos requeridos
            $campos = ['asignacion_id', 'dia_semana', 'hora_inicio', 'hora_fin'];
            foreach ($campos as $campo) {
                if (empty($data[$campo])) {
                    jsonResponse(false, "Campo $campo requerido", null, 400);
                }
            }

            $db = getDatabase();
            
            // Verificar que la asignación pertenece al docente
            $checkQuery = "SELECT * FROM asignaciones WHERE asignacion_id = ? AND docente_id = ?";
            $stmt = $db->prepare($checkQuery);
            $stmt->bind_param("ii", $data['asignacion_id'], $docente_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                $stmt->close();
                jsonResponse(false, 'No tienes permiso para crear horarios en esta asignación', null, 403);
            }
            $stmt->close();

            // Verificar conflictos de horario
            $horarioModel = new Horario();
            if ($horarioModel->hayConflicto($docente_id, $data['dia_semana'], $data['hora_inicio'], $data['hora_fin'])) {
                jsonResponse(false, 'Ya tienes un horario asignado en ese día y hora', null, 409);
            }

            // Crear horario
            $horarioId = $horarioModel->create([
                'asignacion_id' => $data['asignacion_id'],
                'dia_semana' => $data['dia_semana'],
                'hora_inicio' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
                'aula_id' => $data['aula_id'] ?? null,
                'estado_horario' => 'pendiente',
                'confirmado' => 0,
                'conflicto_detectado' => 0
            ]);

            jsonResponse(true, 'Horario creado correctamente', ['horario_id' => $horarioId]);
            break;

        case 'update_self':
            // Permitir que el docente actual edite su información básica
            if ($user['tipo_usuario'] !== 'docente') {
                jsonResponse(false, 'Solo docentes pueden editar su perfil', null, 403);
            }

            $docenteModel = new Docente();
            $usuarioModel = new Usuario();
            $docente = $docenteModel->getByUserId($user['usuario_id']);

            if (!$docente) {
                jsonResponse(false, 'Perfil de docente no encontrado', null, 404);
            }

            $data = json_decode(file_get_contents('php://input'), true);

            $usuarioUpdate = array_filter([
                'nombre' => $data['nombre'] ?? null,
                'apellido' => $data['apellido'] ?? null,
                'telefono' => $data['telefono'] ?? null
            ], fn($v) => $v !== null);

            if (!empty($usuarioUpdate)) {
                $usuarioModel->update($user['usuario_id'], $usuarioUpdate);
            }

            $docenteUpdate = array_filter([
                'especialidad' => $data['especialidad'] ?? null,
                'horas_maximas_semanales' => $data['horas_maximas_semanales'] ?? null,
                'horas_asignadas' => $data['horas_asignadas'] ?? null,
                'tipo_contrato' => $data['tipo_contrato'] ?? null
            ], fn($v) => $v !== null);

            if (!empty($docenteUpdate)) {
                $docenteModel->update($docente['docente_id'], $docenteUpdate);
            }

            $usuario = $usuarioModel->getById($user['usuario_id']);
            if ($usuario) {
                unset($usuario['password_hash'], $usuario['intentos_fallidos'], $usuario['bloqueado_hasta']);
            }

            $docenteRefrescado = $docenteModel->getByUserId($user['usuario_id']);

            jsonResponse(true, 'Perfil actualizado', [
                'usuario' => $usuario,
                'docente' => $docenteRefrescado
            ]);
            break;

        case 'delete':
            if ($user['tipo_usuario'] !== 'administrador') {
                jsonResponse(false, 'Solo administradores pueden eliminar docentes', null, 403);
            }
            
            $id = $_GET['id'] ?? null;
            if (!$id) jsonResponse(false, 'ID requerido', null, 400);
            
            $docente = (new Docente())->getById($id);
            if (!$docente) jsonResponse(false, 'Docente no encontrado', null, 404);

            (new Docente())->update($id, ['estado' => 'inactivo']);
            // también desactivar usuario relacionado
            if (!empty($docente['usuario_id'])) {
                (new Usuario())->update($docente['usuario_id'], ['estado' => 'inactivo']);
            }
            jsonResponse(true, 'Docente desactivado correctamente');
            break;

        default:
            jsonResponse(false, 'Acción no válida', null, 400);
    }
} catch (Exception $e) {
    logError('DOCENTES_API', $e->getMessage());
    jsonResponse(false, 'Error en servidor', null, 500);
}
