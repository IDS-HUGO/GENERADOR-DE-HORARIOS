<?php
require_once '../../src/config.php';

if (!isAuthenticated() || getCurrentUser()['tipo_usuario'] !== 'administrador') {
    redirect(baseUrl('/public/index.html'));
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - ClassControl</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">ClassControl Admin</div>
            <ul class="navbar-menu">
                <li><a href="#" onclick="loadSection('dashboard')" class="active">Dashboard</a></li>
                <li><a href="#" onclick="loadSection('docentes')">Docentes</a></li>
                <li><a href="#" onclick="loadSection('programas')">Programas</a></li>
                <li><a href="#" onclick="loadSection('materias')">Materias</a></li>
                <li><a href="#" onclick="loadSection('grupos')">Grupos</a></li>
            </ul>
            <div class="navbar-user">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($user['nombre'] ?? 'Admin'); ?></div>
                    <div class="user-role">Administrador</div>
                </div>
                <button class="btn-logout" onclick="logout()">Cerrar sesión</button>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="container">
        <!-- DASHBOARD SECTION -->
        <div id="dashboard" class="section">
            <h2 class="mb-4">Panel de Control</h2>
            
            <div class="grid grid-4 mb-5">
                <div class="stat-card primary">
                    <div class="stat-label">Docentes Activos</div>
                    <div class="stat-value" id="stat-docentes">0</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-label">Programas</div>
                    <div class="stat-value" id="stat-programas">0</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-label">Materias</div>
                    <div class="stat-value" id="stat-materias">0</div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-label">Grupos</div>
                    <div class="stat-value" id="stat-grupos">0</div>
                </div>
            </div>

            <div class="grid grid-2 mb-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Acciones Rápidas</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2" style="flex-direction: column;">
                            <button class="btn btn-primary w-100" onclick="showModal('modal-docente')">
                                ➕ Registrar Docente
                            </button>
                            <button class="btn btn-secondary w-100" onclick="showModal('modal-programa')">
                                ➕ Crear Programa
                            </button>
                            <button class="btn btn-success w-100" onclick="showModal('modal-materia')">
                                ➕ Agregar Materia
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Información del Sistema</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Versión:</strong> ClassControl 1.0</p>
                        <p><strong>Estado:</strong> <span class="text-success text-bold">Producción</span></p>
                        <p><strong>Última actualización:</strong> <span id="last-update">Hoy</span></p>
                        <p class="text-muted">Contacto: admin@classcontrol.com</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- DOCENTES SECTION -->
        <div id="docentes" class="section hidden">
            <div class="d-flex flex-between items-center mb-4">
                <h2>Gestión de Docentes</h2>
                <button class="btn btn-primary" onclick="showModal('modal-docente')">➕ Nuevo Docente</button>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table" id="table-docentes">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Especialidad</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="docentes-tbody">
                            <tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PROGRAMAS SECTION -->
        <div id="programas" class="section hidden">
            <div class="d-flex flex-between items-center mb-4">
                <h2>Programas Académicos</h2>
                <button class="btn btn-primary" onclick="showModal('modal-programa')">➕ Nuevo Programa</button>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table" id="table-programas">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Nivel</th>
                                <th>Duración</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="programas-tbody">
                            <tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MATERIAS SECTION -->
        <div id="materias" class="section hidden">
            <div class="d-flex flex-between items-center mb-4">
                <h2>Materias y Cursos</h2>
                <button class="btn btn-primary" onclick="showModal('modal-materia')">➕ Nueva Materia</button>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table" id="table-materias">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Programa</th>
                                <th>Créditos</th>
                                <th>Semestre</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="materias-tbody">
                            <tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- GRUPOS SECTION -->
        <div id="grupos" class="section hidden">
            <div class="d-flex flex-between items-center mb-4">
                <h2>Grupos de Estudiantes</h2>
                <button class="btn btn-primary" onclick="showModal('modal-grupo')">➕ Nuevo Grupo</button>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table" id="table-grupos">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Programa</th>
                                <th>Semestre</th>
                                <th>Estudiantes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="grupos-tbody">
                            <tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- MODALES -->

    <!-- Modal Docente -->
    <div id="modal-docente" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Registrar Nuevo Docente</h2>
                <button class="modal-close" onclick="closeModal('modal-docente')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-docente" onsubmit="submitForm(event, 'docentes')">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label>Apellido</label>
                            <input type="text" name="apellido" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Especialidad</label>
                            <input type="text" name="especialidad" required>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="tel" name="telefono">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Horas Asignadas</label>
                        <input type="number" name="horas_asignadas" min="1" max="50" value="20">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-docente')">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Docente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Programa -->
    <div id="modal-programa" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Crear Programa Académico</h2>
                <button class="modal-close" onclick="closeModal('modal-programa')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-programa" onsubmit="submitForm(event, 'programas')">
                    <div class="form-group">
                        <label>Código del Programa</label>
                        <input type="text" name="codigo" required>
                    </div>
                    <div class="form-group">
                        <label>Nombre del Programa</label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nivel</label>
                            <select name="nivel" required>
                                <option value="">Seleccionar...</option>
                                <option value="tecnico">Técnico</option>
                                <option value="pregrado">Pregrado</option>
                                <option value="posgrado">Posgrado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Duración (semestres)</label>
                            <input type="number" name="duracion_semestres" min="2" max="12" value="8">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" placeholder="Descripción del programa..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-programa')">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Programa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Materia -->
    <div id="modal-materia" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Agregar Materia</h2>
                <button class="modal-close" onclick="closeModal('modal-materia')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-materia" onsubmit="submitForm(event, 'materias')">
                    <div class="form-group">
                        <label>Código de Materia</label>
                        <input type="text" name="codigo" required>
                    </div>
                    <div class="form-group">
                        <label>Nombre de Materia</label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Programa</label>
                        <select name="programa_id" id="programa-select" required>
                            <option value="">Cargando programas...</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Créditos</label>
                            <input type="number" name="creditos" min="1" max="6" value="3">
                        </div>
                        <div class="form-group">
                            <label>Semestre</label>
                            <input type="number" name="semestre" min="1" max="12" value="1">
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="obligatoria" id="obligatoria" checked>
                        <label for="obligatoria">Materia Obligatoria</label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-materia')">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Materia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/utils.js"></script>
    <script>
        // Cargar datos al iniciar
        document.addEventListener('DOMContentLoaded', () => {
            loadDashboardData();
            loadProgramas();
        });

        function loadSection(section) {
            // Ocultar todas las secciones
            document.querySelectorAll('.section').forEach(s => s.classList.add('hidden'));
            // Mostrar sección seleccionada
            document.getElementById(section).classList.remove('hidden');
            
            // Actualizar navbar
            document.querySelectorAll('.navbar-menu a').forEach(a => a.classList.remove('active'));
            event.target.classList.add('active');

            // Cargar datos de la sección
            if (section === 'docentes') loadDocentes();
            else if (section === 'programas') loadProgramas();
            else if (section === 'materias') loadMaterias();
            else if (section === 'grupos') loadGrupos();
        }

        async function loadDashboardData() {
            try {
                const docentes = await api('/src/api/docentes.php?action=list');
                const programas = await api('/src/api/programas.php?action=list');
                const materias = await api('/src/api/materias.php?action=list');
                const grupos = await api('/src/api/grupos.php?action=list');

                document.getElementById('stat-docentes').textContent = docentes.data?.length || 0;
                document.getElementById('stat-programas').textContent = programas.data?.length || 0;
                document.getElementById('stat-materias').textContent = materias.data?.length || 0;
                document.getElementById('stat-grupos').textContent = grupos.data?.length || 0;
            } catch (e) {
                console.error('Error cargando estadísticas:', e);
            }
        }

        async function loadDocentes() {
            try {
                const response = await api('/src/api/docentes.php?action=list');
                const tbody = document.getElementById('docentes-tbody');
                
                if (!response.data || response.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay docentes registrados</td></tr>';
                    return;
                }

                tbody.innerHTML = response.data.map(d => `
                    <tr>
                        <td><strong>${d.nombre} ${d.apellido}</strong></td>
                        <td>${d.email}</td>
                        <td>${d.especialidad || 'N/A'}</td>
                        <td>${d.telefono || 'N/A'}</td>
                        <td><span class="text-success">${d.estado}</span></td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="editDocente(${d.docente_id})">Editar</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteDocente(${d.docente_id})">Eliminar</button>
                        </td>
                    </tr>
                `).join('');
            } catch (e) {
                showAlert('Error al cargar docentes', 'danger');
            }
        }

        async function loadProgramas() {
            try {
                const response = await api('/src/api/programas.php?action=list');
                const tbody = document.getElementById('programas-tbody');
                const select = document.getElementById('programa-select');
                
                if (!response.data || response.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay programas registrados</td></tr>';
                    select.innerHTML = '<option value="">Ningún programa disponible</option>';
                    return;
                }

                tbody.innerHTML = response.data.map(p => `
                    <tr>
                        <td><strong>${p.codigo}</strong></td>
                        <td>${p.nombre}</td>
                        <td>${p.nivel}</td>
                        <td>${p.duracion_semestres} semestres</td>
                        <td><span class="text-success">${p.estado}</span></td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="editPrograma(${p.programa_id})">Editar</button>
                            <button class="btn btn-sm btn-danger" onclick="deletePrograma(${p.programa_id})">Eliminar</button>
                        </td>
                    </tr>
                `).join('');

                select.innerHTML = '<option value="">Seleccionar programa...</option>' + 
                    response.data.map(p => `<option value="${p.programa_id}">${p.nombre}</option>`).join('');
            } catch (e) {
                showAlert('Error al cargar programas', 'danger');
            }
        }

        async function loadMaterias() {
            try {
                const response = await api('/src/api/materias.php?action=list');
                const tbody = document.getElementById('materias-tbody');
                
                if (!response.data || response.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay materias registradas</td></tr>';
                    return;
                }

                tbody.innerHTML = response.data.map(m => `
                    <tr>
                        <td><strong>${m.codigo}</strong></td>
                        <td>${m.nombre}</td>
                        <td>${m.programa_nombre || 'N/A'}</td>
                        <td>${m.creditos}</td>
                        <td>${m.semestre}</td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="editMateria(${m.materia_id})">Editar</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteMateria(${m.materia_id})">Eliminar</button>
                        </td>
                    </tr>
                `).join('');
            } catch (e) {
                showAlert('Error al cargar materias', 'danger');
            }
        }

        async function loadGrupos() {
            try {
                const response = await api('/src/api/grupos.php?action=list');
                const tbody = document.getElementById('grupos-tbody');
                
                if (!response.data || response.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay grupos registrados</td></tr>';
                    return;
                }

                tbody.innerHTML = response.data.map(g => `
                    <tr>
                        <td><strong>${g.codigo}</strong></td>
                        <td>${g.nombre}</td>
                        <td>${g.programa_nombre || 'N/A'}</td>
                        <td>${g.semestre}</td>
                        <td>${g.cantidad_estudiantes}</td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="editGrupo(${g.grupo_id})">Editar</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteGrupo(${g.grupo_id})">Eliminar</button>
                        </td>
                    </tr>
                `).join('');
            } catch (e) {
                showAlert('Error al cargar grupos', 'danger');
            }
        }

        async function submitForm(e, type) {
            e.preventDefault();
            const formData = getFormData(e.target);
            
            try {
                const response = await api(`/src/api/${type}.php?action=create`, {
                    method: 'POST',
                    body: JSON.stringify(formData)
                });

                if (response.success) {
                    showAlert(response.message, 'success');
                    closeModal(`modal-${type.slice(0, -1)}`);
                    e.target.reset();
                    loadDashboardData();
                    if (type === 'docentes') loadDocentes();
                    else if (type === 'programas') loadProgramas();
                    else if (type === 'materias') loadMaterias();
                    else if (type === 'grupos') loadGrupos();
                }
            } catch (e) {
                showAlert(e.message || 'Error al guardar', 'danger');
            }
        }

        function logout() {
            if (confirm('¿Desea cerrar sesión?')) {
                window.location.href = '<?php echo baseUrl('src/api/auth/logout.php'); ?>';
            }
        }
    </script>
</body>
</html>
