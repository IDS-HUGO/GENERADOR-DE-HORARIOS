<?php
require_once '../../src/config.php';

if (!isAuthenticated() || getCurrentUser()['tipo_usuario'] !== 'docente') {
    redirect(baseUrl('/public/index.html'));
}

$user = getCurrentUser();
$docente = (new Docente())->getByUserId($user['usuario_id']) ?? [];
$docenteId = $docente['docente_id'] ?? 0;

// Debug: Si no hay docente, mostrar error
if ($docenteId === 0) {
    echo "<!-- DEBUG: No se encontró docente_id para usuario_id: " . $user['usuario_id'] . " -->";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Horario Semanal - Universidad Maya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/utils.js?v=2.1"></script>
    <style>
        .calendar-container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .calendar-header h1 {
            color: #1a237e;
            margin: 0;
        }
        
        .week-calendar {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(26, 35, 126, 0.15);
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: 80px repeat(6, 1fr);
            gap: 1px;
            background: #e8eaf6;
        }
        
        .calendar-header-cell {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            color: white;
            padding: 15px 10px;
            text-align: center;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .time-cell {
            background: #f3f3f3;
            padding: 10px;
            text-align: center;
            font-size: 0.85rem;
            color: #333;
            font-weight: 500;
            border-right: 2px solid #e8eaf6;
        }
        
        .calendar-cell {
            background: white;
            min-height: 60px;
            padding: 5px;
            cursor: pointer;
            transition: background 0.2s;
            position: relative;
        }
        
        .calendar-cell:hover:not(.has-class) {
            background: #f0f7ff;
            border: 2px dashed #2196F3;
        }
        
        .calendar-cell.has-class {
            cursor: default;
            background: #fafafa;
        }
        
        .calendar-cell.selected {
            background: #e3f2fd;
            border: 2px solid #2196F3;
        }
        
        .class-block {
            color: white;
            padding: 8px;
            border-radius: 6px;
            font-size: 0.85rem;
            margin: 2px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            position: relative;
            font-weight: 500;
        }
        
        .class-block::before {
            content: '🗑️';
            position: absolute;
            top: 4px;
            right: 6px;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.9rem;
        }
        
        .class-block:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.3);
        }
        
        .class-block:hover::before {
            opacity: 1;
        }
        
        /* Colores para cada clase por índice de asignación */
        .class-color-0 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .class-color-1 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .class-color-2 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .class-color-3 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .class-color-4 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .class-color-5 { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
        .class-color-6 { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
        .class-color-7 { background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%); }
        
        .class-block-title {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .class-block-group {
            font-size: 0.75rem;
            opacity: 0.9;
        }
        
        .quick-add-panel {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            border-left: 5px solid #1a237e;
        }
        
        .quick-add-panel h3 {
            color: #1a237e;
            margin-bottom: 10px;
        }
        
        .quick-add-panel > p {
            color: #1a237e !important;
            background: rgba(255,255,255,0.6);
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        
        .quick-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 10px;
            align-items: end;
        }
        
        .form-group-inline {
            display: flex;
            flex-direction: column;
        }
        
        .form-group-inline label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group-inline select,
        .form-group-inline input {
            padding: 10px;
            border: 2px solid #1a237e;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s;
            background: white;
        }
        
        .form-group-inline select:focus,
        .form-group-inline input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-add-quick {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-add-quick:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-add-quick:active {
            transform: translateY(0);
        }
        
        .legend {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            padding: 15px;
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            border-radius: 8px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: white;
            font-weight: 500;
        }
        
        .legend-color {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            border: 2px solid white;
        }
        
        @media (max-width: 1200px) {
            .quick-form {
                grid-template-columns: 1fr;
            }
            
            .calendar-grid {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">🎓 Universidad Maya - Portal Docente</div>
            <ul class="navbar-menu">
                <li><a href="dashboard.php" class="nav-link">Inicio</a></li>
                <li><a href="seleccion-materias.php" class="nav-link">📚 Seleccionar Materias</a></li>
                <li><a href="mi-horario-semanal.php" class="nav-link active">📅 Mi Horario</a></li>
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

    <div class="calendar-container">
        <div class="calendar-header">
            <h1>📅 Mi Horario Semanal</h1>
            <div>
                <button class="btn btn-secondary" onclick="location.reload()">🔄 Recargar</button>
            </div>
        </div>

        <!-- Panel de Agregar Rápido -->
        <div class="quick-add-panel">
            <h3 style="margin-bottom: 10px;">➕ Agregar Horario Rápido</h3>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 15px;">
                💡 <strong>Tip:</strong> Haz click en cualquier celda vacía del calendario para auto-completar el día y hora
            </p>
            <form class="quick-form" onsubmit="agregarHorarioRapido(event)">
                <div class="form-group-inline">
                    <label>Asignación (Materia - Grupo)</label>
                    <select id="quick-asignacion" required>
                        <option value="">Cargando...</option>
                    </select>
                </div>
                <div class="form-group-inline">
                    <label>Día</label>
                    <select id="quick-dia" required>
                        <option value="lunes">Lunes</option>
                        <option value="martes">Martes</option>
                        <option value="miercoles">Miércoles</option>
                        <option value="jueves">Jueves</option>
                        <option value="viernes">Viernes</option>
                        <option value="sabado">Sábado</option>
                    </select>
                </div>
                <div class="form-group-inline">
                    <label>Hora Inicio</label>
                    <input type="time" id="quick-hora-inicio" value="08:00" required step="3600">
                </div>
                <div class="form-group-inline">
                    <label>Hora Fin</label>
                    <input type="time" id="quick-hora-fin" value="10:00" required step="3600">
                </div>
                <button type="submit" class="btn-add-quick">+ Agregar</button>
            </form>
        </div>

        <!-- Calendario Semanal -->
        <div class="week-calendar">
            <div class="calendar-grid" id="calendar-grid">
                <!-- Se generará dinámicamente -->
            </div>
        </div>

        <!-- Leyenda -->
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                <span>Clase 1</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                <span>Clase 2</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"></div>
                <span>Clase 3</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);"></div>
                <span>Clase 4+</span>
            </div>
            <div class="legend-item" style="margin-left: auto;">
                <span>🗑️ = Eliminar | Click = Detalles</span>
            </div>
        </div>
    </div>

    <script>
        const DOCENTE_ID = <?php echo (int)$docenteId; ?>;
        const API_BASE = '/ClassControl/src/api';
        
        const dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        const horasInicio = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];
        let asignacionesData = [];
        let horariosData = [];

        async function apiJson(path, options = {}) {
            try {
                const url = path.startsWith('http') ? path : `${API_BASE}${path.startsWith('/') ? '' : '/'}${path}`;
                const response = await fetch(url, {
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
                    ...options
                });
                
                if (!response.ok) {
                    console.error('Error en respuesta:', response.status, await response.text());
                    return { success: false, message: 'Error en la petición' };
                }
                
                return await response.json();
            } catch (error) {
                console.error('Error en apiJson:', error);
                return { success: false, message: error.message };
            }
        }

        async function logout() {
            if (confirm('¿Desea cerrar sesión?')) {
                try {
                    const res = await fetch(`${API_BASE}/auth/logout.php`, { method: 'POST' });
                    const data = await res.json();
                    window.location.href = data.redirect || '/ClassControl/public/index.html';
                } catch(e) {
                    window.location.href = '/ClassControl/public/index.html';
                }
            }
        }

        async function cargarAsignaciones() {
            try {
                console.log('Cargando asignaciones...');
                const res = await apiJson(`/docentes.php?action=asignaciones_confirmadas`);
                console.log('Respuesta:', res);
                
                if (res && res.success && res.data) {
                    asignacionesData = res.data;
                    horariosData = res.data;
                    actualizarSelectAsignaciones();
                    renderizarCalendario();
                } else {
                    console.error('Error al cargar asignaciones:', res);
                    alert('Error al cargar datos. Por favor recarga la página.');
                }
            } catch (error) {
                console.error('Error en cargarAsignaciones:', error);
                alert('Error al cargar datos: ' + error.message);
            }
        }

        function actualizarSelectAsignaciones() {
            const select = document.getElementById('quick-asignacion');
            const asignacionesUnicas = {};
            
            asignacionesData.forEach(item => {
                const key = item.asignacion_id;
                if (!asignacionesUnicas[key]) {
                    asignacionesUnicas[key] = {
                        id: item.asignacion_id,
                        materia: item.materia_nombre,
                        grupo: item.grupo_codigo
                    };
                }
            });
            
            const options = ['<option value="">Seleccione materia-grupo</option>'];
            Object.values(asignacionesUnicas).forEach(asig => {
                options.push(`<option value="${asig.id}">${asig.materia} - ${asig.grupo}</option>`);
            });
            
            select.innerHTML = options.join('');
        }

        function renderizarCalendario() {
            const grid = document.getElementById('calendar-grid');
            let html = '<div class="calendar-header-cell">Hora</div>';
            
            // Crear mapa de colores por materia
            const coloresPorMateria = {};
            let colorIndex = 0;
            
            // Asignar colores a cada materia única
            asignacionesData.forEach(item => {
                if (item.materia_nombre && !coloresPorMateria[item.materia_nombre]) {
                    coloresPorMateria[item.materia_nombre] = colorIndex % 8;
                    colorIndex++;
                }
            });
            
            console.log('[CALENDARIO] Colores asignados:', coloresPorMateria);
            
            // Headers de días
            dias.forEach(dia => {
                html += `<div class="calendar-header-cell">${dia.charAt(0).toUpperCase() + dia.slice(1)}</div>`;
            });
            
            // Filas de horarios
            horasInicio.forEach(hora => {
                const [h, m] = hora.split(':');
                const horaFin = `${String(parseInt(h) + 2).padStart(2, '0')}:${m}`;
                html += `<div class="time-cell">${hora}<br>-<br>${horaFin}</div>`;
                
                dias.forEach(dia => {
                    const clases = horariosData.filter(h => 
                        h.dia_semana === dia && 
                        h.hora_inicio && h.hora_fin &&
                        h.hora_inicio.substring(0, 5) >= hora && 
                        h.hora_inicio.substring(0, 5) < horaFin
                    );
                    
                    let cellContent = '';
                    if (clases.length > 0) {
                        clases.forEach(clase => {
                            const colorClass = `class-color-${coloresPorMateria[clase.materia_nombre] || 0}`;
                            cellContent += `
                                <div class="class-block ${colorClass}" onclick="editarHorario(${clase.horario_id})">
                                    <div class="class-block-title">${clase.materia_nombre || 'Sin nombre'}</div>
                                    <div class="class-block-group">Grupo ${clase.grupo_codigo || '-'}</div>
                                    <div class="class-block-group">${clase.hora_inicio.substring(0,5)}-${clase.hora_fin.substring(0,5)}</div>
                                </div>
                            `;
                        });
                        html += `<div class="calendar-cell has-class">${cellContent}</div>`;
                    } else {
                        html += `<div class="calendar-cell" onclick="clickCelda('${dia}', '${hora}', '${horaFin}')"></div>`;
                    }
                });
            });
            
            grid.innerHTML = html;
        }

        function clickCelda(dia, horaInicio, horaFin) {
            document.getElementById('quick-dia').value = dia;
            document.getElementById('quick-hora-inicio').value = horaInicio;
            document.getElementById('quick-hora-fin').value = horaFin;
            document.getElementById('quick-asignacion').focus();
        }

        async function agregarHorarioRapido(e) {
            e.preventDefault();
            
            // Normalizar horas a formato HH:MM
            const horaInicio = document.getElementById('quick-hora-inicio').value;
            const horaFin = document.getElementById('quick-hora-fin').value;
            
            const payload = {
                asignacion_id: parseInt(document.getElementById('quick-asignacion').value),
                dia_semana: document.getElementById('quick-dia').value,
                hora_inicio: horaInicio.length === 5 ? horaInicio : horaInicio.padStart(5, '0'),
                hora_fin: horaFin.length === 5 ? horaFin : horaFin.padStart(5, '0')
            };

            console.log('[VALIDACION] Intentando agregar:', payload);

            // Validar conflictos: solo hay conflicto si los horarios SE SOLAPAN
            // Permitimos clases consecutivas (ej: 10:00-12:00 y 12:00-14:00)
            const conflicto = horariosData.find(h => {
                if (h.dia_semana !== payload.dia_semana || !h.hora_inicio || !h.hora_fin) {
                    return false;
                }
                
                // Normalizar horas existentes a formato HH:MM
                const existenteInicio = h.hora_inicio.substring(0, 5);
                const existenteFin = h.hora_fin.substring(0, 5);
                
                console.log('[VALIDACION] Comparando con:', {
                    materia: h.materia_nombre,
                    existente: `${existenteInicio}-${existenteFin}`,
                    nuevo: `${payload.hora_inicio}-${payload.hora_fin}`
                });
                
                // Hay solapamiento si:
                // - El nuevo empieza ANTES de que termine el existente Y
                // - El nuevo termina DESPUÉS de que empiece el existente
                return payload.hora_inicio < existenteFin && payload.hora_fin > existenteInicio;
            });

            if (conflicto) {
                const mensaje = `⚠️ CONFLICTO DETECTADO\n\n` +
                    `Los horarios se traslapan con:\n` +
                    `${conflicto.materia_nombre} (${conflicto.grupo_codigo})\n` +
                    `${conflicto.dia_semana} ${conflicto.hora_inicio.substring(0,5)}-${conflicto.hora_fin.substring(0,5)}\n\n` +
                    `Nota: Puedes tener clases consecutivas (ej: 12:00-14:00 y 14:00-16:00)`;
                alert(mensaje);
                return;
            }

            const res = await apiJson(`/docentes.php?action=crear_horario`, {
                method: 'POST',
                body: JSON.stringify(payload)
            });

            if (res.success) {
                alert('✅ Horario agregado exitosamente');
                await cargarAsignaciones();
                e.target.reset();
                document.getElementById('quick-hora-inicio').value = '08:00';
                document.getElementById('quick-hora-fin').value = '10:00';
            } else {
                alert('❌ ' + (res.message || 'Error al agregar horario'));
            }
        }

        function editarHorario(horarioId) {
            const horario = horariosData.find(h => h.horario_id === horarioId);
            if (!horario) return;
            
            const mensaje = `📋 HORARIO ACTUAL\n\n` +
                `Materia: ${horario.materia_nombre}\n` +
                `Grupo: ${horario.grupo_codigo}\n` +
                `Día: ${horario.dia_semana}\n` +
                `Horario: ${horario.hora_inicio} - ${horario.hora_fin}\n` +
                `Aula: ${horario.aula_nombre || 'Sin asignar'}\n\n` +
                `¿Deseas eliminar este horario?`;
            
            if (confirm(mensaje)) {
                eliminarHorario(horarioId);
            }
        }

        async function eliminarHorario(horarioId) {
            const res = await apiJson(`/horarios.php?action=delete&horario_id=${horarioId}`, {
                method: 'DELETE'
            });

            if (res.success) {
                alert('✅ Horario eliminado exitosamente');
                await cargarAsignaciones();
            } else {
                alert('❌ ' + (res.message || 'Error al eliminar horario'));
            }
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOCENTE_ID:', DOCENTE_ID);
            console.log('API_BASE:', API_BASE);
            
            if (DOCENTE_ID === 0) {
                alert('⚠️ Error: No se encontró tu información de docente. Contacta al administrador.');
                return;
            }
            
            cargarAsignaciones();
        });
    </script>
</body>
</html>