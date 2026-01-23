<?php
require_once '../../src/config.php';

if (!isAuthenticated() || getCurrentUser()['tipo_usuario'] !== 'docente') {
    redirect(baseUrl('/public/index.html'));
}

$user = getCurrentUser();
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selección de Horarios - Docente</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/utils.js?v=2.1"></script>
    <style>
        .seleccion-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #4CAF50;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .grupos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .grupo-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .grupo-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transform: translateY(-2px);
        }
        
        .grupo-card.selected {
            border: 2px solid #4CAF50;
            background: #f0f9f0;
        }
        
        .materias-list {
            margin-top: 20px;
        }
        
        .materia-item {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 6px;
            border-left: 4px solid #2196F3;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .materia-item.disponible {
            border-left-color: #4CAF50;
        }
        
        .materia-item.asignada {
            border-left-color: #FFC107;
            background: #fffbf0;
        }
        
        .materia-item.mi-solicitud {
            border-left-color: #2196F3;
            background: #f0f7ff;
        }
        
        .solicitudes-panel {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-solicitada { background: #E3F2FD; color: #1976D2; }
        .badge-confirmada { background: #E8F5E9; color: #388E3C; }
        .badge-pendiente { background: #FFF3E0; color: #F57C00; }
        .badge-cancelada { background: #FFEBEE; color: #D32F2F; }
        
        .btn-solicitar {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn-solicitar:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .copyright-footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 0.85rem;
            border-top: 1px solid #e0e0e0;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">🎓 Universidad Maya - Portal Docente</div>
            <ul class="navbar-menu">
                <li><a href="dashboard.php" class="nav-link">Inicio</a></li>
                <li><a href="seleccion-materias.php" class="nav-link active">📚 Seleccionar Materias</a></li>
                <li><a href="mi-horario-semanal.php" class="nav-link">📅 Mi Horario</a></li>
            </ul>
            <div class="navbar-user">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($user['nombre'] ?? 'Docente'); ?></div>
                    <div class="user-role">👨‍🏫 Docente</div>
                </div>
                <button class="btn btn-sm btn-danger" onclick="logout()">Salir</button>
            </div>
        </div>
    </nav>
    
    <div class="seleccion-container">
        <!-- Estadísticas del docente -->
        <div class="stats-grid" id="stats-grid">
            <!-- Se llenará dinámicamente -->
        </div>
        
        <h2>📚 Grupos Disponibles</h2>
        <p style="color: #666; margin-bottom: 20px;">
            Selecciona un grupo para ver las materias disponibles. Tu prioridad está basada en tu antigüedad.
        </p>
        
        <!-- Grid de grupos -->
        <div class="grupos-grid" id="grupos-grid">
            <!-- Se llenará dinámicamente -->
        </div>
        
        <!-- Materias del grupo seleccionado -->
        <div id="materias-section" style="display: none;">
            <h2>📖 Materias Disponibles</h2>
            <div class="materias-list" id="materias-list">
                <!-- Se llenará dinámicamente -->
            </div>
        </div>
        
        <!-- Mis solicitudes -->
        <div class="solicitudes-panel">
            <h2>📋 Mis Solicitudes</h2>
            <div id="solicitudes-list">
                <p style="text-align: center; color: #999;">Cargando...</p>
            </div>
        </div>
        
        <div class="copyright-footer">
            &copy; 2026 Universidad Maya - Sistema de Gestión de Horarios<br>
            Todos los derechos reservados
        </div>
    </div>
    
    <script>
        const API_BASE_URL = '/ClassControl';
        let grupoSeleccionado = null;
        
        async function cargarEstadisticas() {
            try {
                const response = await fetch(`${API_BASE_URL}/src/api/seleccion-docente.php?action=estadisticas`);
                const result = await response.json();
                
                if (result.success) {
                    const stats = result.data;
                    document.getElementById('stats-grid').innerHTML = `
                        <div class="stat-card">
                            <div class="stat-label">Antigüedad</div>
                            <div class="stat-value">${stats.antiguedad || 0}</div>
                            <div class="stat-label">años</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Horas Asignadas</div>
                            <div class="stat-value">${stats.horas_asignadas || 0}</div>
                            <div class="stat-label">de ${stats.horas_maximas_semanales || 40}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Solicitudes Pendientes</div>
                            <div class="stat-value">${stats.solicitudes_pendientes || 0}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Materias Confirmadas</div>
                            <div class="stat-value">${stats.materias_confirmadas || 0}</div>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error cargando estadísticas:', error);
            }
        }
        
        async function cargarGrupos() {
            try {
                const response = await fetch(`${API_BASE_URL}/src/api/seleccion-docente.php?action=grupos`);
                const result = await response.json();
                
                if (result.success) {
                    const grupos = result.data;
                    document.getElementById('grupos-grid').innerHTML = grupos.map(g => `
                        <div class="grupo-card" onclick="seleccionarGrupo(${g.grupo_id})">
                            <h3>${g.nombre}</h3>
                            <p><strong>Código:</strong> ${g.codigo}</p>
                            <p><strong>Programa:</strong> ${g.programa_nombre}</p>
                            <p><strong>Semestre:</strong> ${g.semestre} | <strong>Jornada:</strong> ${g.jornada}</p>
                            <p><strong>Estudiantes:</strong> ${g.cantidad_estudiantes}</p>
                            <p style="color: #666; font-size: 0.9rem;">${g.materias_asignadas || 0} materias asignadas</p>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Error cargando grupos:', error);
            }
        }
        
        async function seleccionarGrupo(grupoId) {
            grupoSeleccionado = grupoId;
            
            // Marcar grupo seleccionado
            document.querySelectorAll('.grupo-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.target.closest('.grupo-card').classList.add('selected');
            
            // Cargar materias
            try {
                const response = await fetch(`${API_BASE_URL}/src/api/seleccion-docente.php?action=materias&grupo_id=${grupoId}`);
                const result = await response.json();
                
                if (result.success) {
                    const materias = result.data;
                    document.getElementById('materias-section').style.display = 'block';
                    document.getElementById('materias-list').innerHTML = materias.map(m => {
                        let claseItem = 'materia-item';
                        let estado = '';
                        let btnDisabled = '';
                        
                        if (m.asignada_a_mi) {
                            claseItem += ' mi-solicitud';
                            estado = '<span class="badge badge-solicitada">Mi solicitud</span>';
                            btnDisabled = 'disabled';
                        } else if (m.ya_asignada) {
                            claseItem += ' asignada';
                            estado = '<span class="badge badge-pendiente">Ya asignada</span>';
                            btnDisabled = 'disabled';
                        } else {
                            claseItem += ' disponible';
                            estado = '<span class="badge badge-confirmada">Disponible</span>';
                            btnDisabled = '';
                        }
                        
                        return `
                            <div class="${claseItem}">
                                <div>
                                    <h4 style="margin: 0 0 8px 0;">${m.nombre}</h4>
                                    <p style="margin: 0; color: #666; font-size: 0.9rem;">
                                        ${m.codigo} | ${m.creditos} créditos | ${m.horas_semana} horas/semana
                                    </p>
                                    ${estado}
                                </div>
                                <button class="btn-solicitar" onclick="solicitarMateria(${m.materia_id}, ${grupoId})" ${btnDisabled}>
                                    Solicitar
                                </button>
                            </div>
                        `;
                    }).join('');
                }
            } catch (error) {
                console.error('Error cargando materias:', error);
            }
        }
        
        async function solicitarMateria(materiaId, grupoId) {
            if (!confirm('¿Confirmas que deseas solicitar esta materia?')) return;
            
            try {
                const response = await fetch(`${API_BASE_URL}/src/api/seleccion-docente.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'solicitar',
                        materia_id: materiaId,
                        grupo_id: grupoId
                    })
                });
                
                const result = await response.json();
                alert(result.message);
                
                if (result.success) {
                    await cargarSolicitudes();
                    await seleccionarGrupo(grupoId);
                    await cargarEstadisticas();
                }
            } catch (error) {
                console.error('Error solicitando materia:', error);
                alert('Error al procesar solicitud');
            }
        }
        
        async function cargarSolicitudes() {
            try {
                const response = await fetch(`${API_BASE_URL}/src/api/seleccion-docente.php?action=mis-solicitudes`);
                const result = await response.json();
                
                if (result.success) {
                    const solicitudes = result.data;
                    if (solicitudes.length === 0) {
                        document.getElementById('solicitudes-list').innerHTML = '<p style="text-align: center; color: #999;">No tienes solicitudes aún</p>';
                    } else {
                        document.getElementById('solicitudes-list').innerHTML = solicitudes.map(s => `
                            <div class="materia-item">
                                <div>
                                    <h4 style="margin: 0 0 8px 0;">${s.materia_nombre}</h4>
                                    <p style="margin: 0; color: #666; font-size: 0.9rem;">
                                        ${s.materia_codigo} | Grupo: ${s.grupo_nombre} (${s.jornada})
                                    </p>
                                    <p style="margin: 5px 0 0 0; font-size: 0.85rem; color: #999;">
                                        Solicitada: ${new Date(s.fecha_solicitud).toLocaleDateString('es-MX')}
                                    </p>
                                </div>
                                <div>
                                    <span class="badge badge-${s.estado}">${s.estado}</span>
                                    ${s.estado === 'solicitada' ? `<button class="btn-solicitar" style="background: #f44336; margin-left: 10px;" onclick="cancelarSolicitud(${s.asignacion_id})">Cancelar</button>` : ''}
                                </div>
                            </div>
                        `).join('');
                    }
                }
            } catch (error) {
                console.error('Error cargando solicitudes:', error);
            }
        }
        
        async function cancelarSolicitud(asignacionId) {
            if (!confirm('¿Confirmas que deseas cancelar esta solicitud?')) return;
            
            try {
                const response = await fetch(`${API_BASE_URL}/src/api/seleccion-docente.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'cancelar',
                        asignacion_id: asignacionId
                    })
                });
                
                const result = await response.json();
                alert(result.message);
                
                if (result.success) {
                    await cargarSolicitudes();
                    await cargarEstadisticas();
                    if (grupoSeleccionado) {
                        await seleccionarGrupo(grupoSeleccionado);
                    }
                }
            } catch (error) {
                console.error('Error cancelando solicitud:', error);
            }
        }
        
        async function logout() {
            if (confirm('¿Desea cerrar sesión?')) {
                try {
                    const res = await fetch(`${API_BASE_URL}/src/api/auth/logout.php`, { method: 'POST' });
                    const data = await res.json();
                    window.location.href = data.redirect || '/ClassControl/public/index.html';
                } catch(e) {
                    window.location.href = '/ClassControl/public/index.html';
                }
            }
        }
        
        // Inicializar
        window.addEventListener('DOMContentLoaded', async () => {
            await cargarEstadisticas();
            await cargarGrupos();
            await cargarSolicitudes();
        });
    </script>
</body>
</html>
