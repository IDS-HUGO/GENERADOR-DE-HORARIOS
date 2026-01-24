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
    <title>Mis Materias Asignadas - Docente</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/utils.js?v=2.1"></script>
    <style>
        .container-materias {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .header-section {
            margin-bottom: 30px;
        }
        
        .header-section h1 {
            color: #333;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header-section p {
            color: #666;
            margin: 0;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #4CAF50;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4CAF50;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .materias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .materia-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .materia-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .materia-card-header {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 15px;
            border-bottom: 4px solid #2E7D32;
        }
        
        .materia-card-header h3 {
            margin: 0 0 5px 0;
            font-size: 1.1rem;
            word-break: break-word;
        }
        
        .materia-code {
            font-size: 0.85rem;
            opacity: 0.9;
            font-weight: 600;
        }
        
        .materia-card-body {
            padding: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.9rem;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 8px;
        }
        
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
            color: #333;
            min-width: 80px;
        }
        
        .info-value {
            color: #666;
            text-align: right;
            flex: 1;
        }
        
        .horarios-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #f0f0f0;
        }
        
        .horarios-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .horario-item {
            background: #f9f9f9;
            padding: 8px 10px;
            margin-bottom: 6px;
            border-radius: 4px;
            font-size: 0.85rem;
            border-left: 3px solid #2196F3;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #f9f9f9;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .empty-state-text {
            color: #999;
            font-size: 1.1rem;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: 5px;
        }
        
        .badge-activo {
            background: #E8F5E9;
            color: #2E7D32;
        }
        
        .badge-pendiente {
            background: #FFF3E0;
            color: #F57C00;
        }
        
        .loading-spinner {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4CAF50;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                <li><a href="seleccion-materias.php" class="nav-link active">📚 Mis Materias</a></li>
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
    
    <div class="container-materias">
        <!-- Encabezado -->
        <div class="header-section">
            <h1>📚 Mis Materias Asignadas</h1>
            <p>Vista de solo lectura de las materias asignadas en tu carga académica</p>
        </div>
        
        <!-- Estadísticas -->
        <div class="stats-container" id="stats-container">
            <div class="loading-spinner">
                <div class="spinner"></div>
                Cargando información...
            </div>
        </div>
        
        <!-- Listado de materias -->
        <div id="materias-container">
            <div class="loading-spinner">
                <div class="spinner"></div>
                Cargando materias...
            </div>
        </div>
        
        <div class="copyright-footer">
            &copy; 2026 Universidad Maya - Sistema de Gestión de Horarios<br>
            Todos los derechos reservados
        </div>
    </div>
    
    <script>
        async function cargarMaterias() {
            try {
                const result = await api('/src/api/seleccion-docente.php?action=asignadas');
                
                if (result.success) {
                    const materias = result.data.materias || [];
                    const estadisticas = result.data.estadisticas || {};
                    
                    // Mostrar estadísticas
                    mostrarEstadisticas(estadisticas);
                    
                    // Mostrar materias
                    if (materias.length === 0) {
                        document.getElementById('materias-container').innerHTML = `
                            <div class="empty-state">
                                <div class="empty-state-icon">📭</div>
                                <div class="empty-state-text">No tienes materias asignadas en este momento</div>
                            </div>
                        `;
                    } else {
                        document.getElementById('materias-container').innerHTML = `
                            <div class="materias-grid">
                                ${materias.map(materia => generarTarjetaMateria(materia)).join('')}
                            </div>
                        `;
                    }
                } else {
                    document.getElementById('materias-container').innerHTML = `
                        <div class="empty-state">
                            <div class="empty-state-icon">⚠️</div>
                            <div class="empty-state-text">${result.message || 'Error al cargar las materias'}</div>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error cargando materias:', error);
                document.getElementById('materias-container').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">❌</div>
                        <div class="empty-state-text">Error al conectar con el servidor</div>
                    </div>
                `;
            }
        }
        
        function mostrarEstadisticas(stats) {
            const html = `
                <div class="stat-card">
                    <div class="stat-label">📚 Total de Materias</div>
                    <div class="stat-value">${stats.total_materias || 0}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">👥 Total de Grupos</div>
                    <div class="stat-value">${stats.total_grupos || 0}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">⭐ Total de Créditos</div>
                    <div class="stat-value">${stats.total_creditos || 0}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">⏰ Total de Horas</div>
                    <div class="stat-value">${stats.total_horas || 0}</div>
                </div>
            `;
            document.getElementById('stats-container').innerHTML = html;
        }
        
        function generarTarjetaMateria(materia) {
            const horarios = materia.horarios || [];
            const estadoClase = materia.estado === 'activo' ? 'badge-activo' : 'badge-pendiente';
            const estadoTexto = materia.estado === 'activo' ? 'Activo' : 'Pendiente';
            
            let horariosHtml = '';
            if (horarios.length > 0) {
                horariosHtml = `
                    <div class="horarios-section">
                        <div class="horarios-title">📅 Horarios Asignados</div>
                        ${horarios.map(h => `
                            <div class="horario-item">
                                ${h.dia}: ${h.hora_inicio} - ${h.hora_fin} (${h.aula || 'Aula no asignada'})
                            </div>
                        `).join('')}
                    </div>
                `;
            }
            
            return `
                <div class="materia-card">
                    <div class="materia-card-header">
                        <h3>${escapeHtml(materia.nombre)}</h3>
                        <div class="materia-code">${escapeHtml(materia.codigo)}</div>
                    </div>
                    <div class="materia-card-body">
                        <div class="info-row">
                            <span class="info-label">Grupo:</span>
                            <span class="info-value">${escapeHtml(materia.grupo_nombre)}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Programa:</span>
                            <span class="info-value">${escapeHtml(materia.programa_nombre)}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Semestre:</span>
                            <span class="info-value">${materia.semestre || 'N/A'}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Jornada:</span>
                            <span class="info-value">${escapeHtml(materia.jornada || 'N/A')}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Créditos:</span>
                            <span class="info-value">${materia.creditos || 0}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Horas/Semana:</span>
                            <span class="info-value">${materia.horas_semana || 0}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Estudiantes:</span>
                            <span class="info-value">${materia.cantidad_estudiantes || 0}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Estado:</span>
                            <span class="info-value">
                                <span class="badge ${estadoClase}">${estadoTexto}</span>
                            </span>
                        </div>
                        ${horariosHtml}
                    </div>
                </div>
            `;
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        async function logout() {
            if (confirm('¿Desea cerrar sesión?')) {
                try {
                    await api('/src/api/auth/logout.php', { method: 'POST' });
                    window.location.href = '/GENERADOR-DE-HORARIOS/public/index.html';
                } catch(e) {
                    window.location.href = '/GENERADOR-DE-HORARIOS/public/index.html';
                }
            }
        }
        
        // Inicializar al cargar la página
        window.addEventListener('DOMContentLoaded', () => {
            cargarMaterias();
        });
    </script>
</body>
</html>
