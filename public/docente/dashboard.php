<?php
require_once '../../src/config.php';

if (!isAuthenticated() || getCurrentUser()['tipo_usuario'] !== 'docente') {
    redirect(baseUrl('/public/index.html'));
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Dashboard - ClassControl</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">ClassControl Docente</div>
            <ul class="navbar-menu">
                <li><a href="#" onclick="loadSection('inicio')" class="active">Inicio</a></li>
                <li><a href="#" onclick="loadSection('horario')">Mi Horario</a></li>
                <li><a href="#" onclick="loadSection('disponibilidad')">Disponibilidad</a></li>
                <li><a href="#" onclick="loadSection('materias')">Mis Materias</a></li>
            </ul>
            <div class="navbar-user">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($user['nombre'] ?? 'Docente'); ?></div>
                    <div class="user-role">Docente</div>
                </div>
                <button class="btn-logout" onclick="logout()">Cerrar sesión</button>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="container">
        <!-- INICIO SECTION -->
        <div id="inicio" class="section">
            <div class="grid grid-2 mb-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Información Personal</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Nombre:</strong> <span id="info-nombre"><?php echo htmlspecialchars($user['nombre'] ?? ''); ?></span></p>
                        <p><strong>Apellido:</strong> <span id="info-apellido"><?php echo htmlspecialchars($user['apellido'] ?? ''); ?></span></p>
                        <p><strong>Email:</strong> <span id="info-email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></span></p>
                        <p><strong>Teléfono:</strong> <span id="info-telefono">Cargando...</span></p>
                        <p><strong>Especialidad:</strong> <span id="info-especialidad">Cargando...</span></p>
                        <div class="mt-3">
                            <button class="btn btn-primary" onclick="showModal('modal-editar')">Editar Perfil</button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Estadísticas</h3>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-2" style="gap: 12px;">
                            <div class="stat-card primary">
                                <div class="stat-label">Materias</div>
                                <div class="stat-value" id="stat-materias">0</div>
                            </div>
                            <div class="stat-card success">
                                <div class="stat-label">Grupos</div>
                                <div class="stat-value" id="stat-grupos">0</div>
                            </div>
                            <div class="stat-card warning">
                                <div class="stat-label">Horas/Semana</div>
                                <div class="stat-value" id="stat-horas">0</div>
                            </div>
                            <div class="stat-card danger">
                                <div class="stat-label">Estudiantes</div>
                                <div class="stat-value" id="stat-estudiantes">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Acciones Disponibles</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-3 gap-2">
                        <div>
                            <h4 class="mb-2">📅 Horario</h4>
                            <p class="text-muted mb-2">Consulta y gestiona tu horario de clases</p>
                            <button class="btn btn-primary w-100" onclick="loadSection('horario')">Ver Horario</button>
                        </div>
                        <div>
                            <h4 class="mb-2">⏰ Disponibilidad</h4>
                            <p class="text-muted mb-2">Actualiza tus horas disponibles</p>
                            <button class="btn btn-secondary w-100" onclick="loadSection('disponibilidad')">Actualizar</button>
                        </div>
                        <div>
                            <h4 class="mb-2">📚 Materias</h4>
                            <p class="text-muted mb-2">Revisa las materias asignadas</p>
                            <button class="btn btn-success w-100" onclick="loadSection('materias')">Ver Materias</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HORARIO SECTION -->
        <div id="horario" class="section hidden">
            <h2 class="mb-4">Mi Horario Semanal</h2>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Semana del <span id="week-date">Hoy</span></h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Lunes</th>
                                    <th>Martes</th>
                                    <th>Miércoles</th>
                                    <th>Jueves</th>
                                    <th>Viernes</th>
                                </tr>
                            </thead>
                            <tbody id="horario-tbody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Cargando horario...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4 alert alert-info">
                <strong>ℹ️ Información:</strong> 
                Tu horario se actualiza automáticamente cuando el administrador realiza asignaciones. 
                Si tienes conflictos con tu disponibilidad, actualízala en la sección de Disponibilidad.
            </div>
        </div>

        <!-- DISPONIBILIDAD SECTION -->
        <div id="disponibilidad" class="section hidden">
            <h2 class="mb-4">Mi Disponibilidad Horaria</h2>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Horas Disponibles por Día</h3>
                </div>
                <div class="card-body">
                    <form id="form-disponibilidad" onsubmit="saveDisponibilidad(event)">
                        <div class="grid grid-5 gap-3">
                            <div class="form-group">
                                <label>Lunes</label>
                                <div style="display: flex; gap: 10px;">
                                    <input type="time" name="lunes_inicio" value="08:00">
                                    <input type="time" name="lunes_fin" value="20:00">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Martes</label>
                                <div style="display: flex; gap: 10px;">
                                    <input type="time" name="martes_inicio" value="08:00">
                                    <input type="time" name="martes_fin" value="20:00">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Miércoles</label>
                                <div style="display: flex; gap: 10px;">
                                    <input type="time" name="miercoles_inicio" value="08:00">
                                    <input type="time" name="miercoles_fin" value="20:00">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Jueves</label>
                                <div style="display: flex; gap: 10px;">
                                    <input type="time" name="jueves_inicio" value="08:00">
                                    <input type="time" name="jueves_fin" value="20:00">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Viernes</label>
                                <div style="display: flex; gap: 10px;">
                                    <input type="time" name="viernes_inicio" value="08:00">
                                    <input type="time" name="viernes_fin" value="20:00">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label>Máximo de Horas Semanales</label>
                            <input type="number" name="horas_maximas" min="1" max="60" value="40">
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Guardar Disponibilidad</button>
                            <button type="button" class="btn btn-secondary" onclick="resetDisponibilidad()">Restablecer</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4 alert alert-warning">
                <strong>⚠️ Importante:</strong> 
                Estos horarios son indicativos de tus disponibilidades. El sistema los usa para generar tu horario definitivo, 
                pero puede haber variaciones según la disponibilidad de aulas y estudiantes.
            </div>
        </div>

        <!-- MATERIAS SECTION -->
        <div id="materias" class="section hidden">
            <h2 class="mb-4">Mis Materias Asignadas</h2>

            <div class="card">
                <div class="card-body">
                    <table class="table" id="table-materias">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Código</th>
                                <th>Grupo</th>
                                <th>Créditos</th>
                                <th>Estudiantes</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="materias-tbody">
                            <tr><td colspan="7" class="text-center text-muted">Cargando materias...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL EDITAR PERFIL -->
    <div id="modal-editar" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Editar Perfil</h2>
                <button class="modal-close" onclick="closeModal('modal-editar')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-perfil" onsubmit="editarPerfil(event)">
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
                        <label>Teléfono</label>
                        <input type="tel" name="telefono">
                    </div>
                    <div class="form-group">
                        <label>Especialidad</label>
                        <input type="text" name="especialidad">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-editar')">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/utils.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadPersonalInfo();
            generateHorarioVacio();
        });

        function loadSection(section) {
            document.querySelectorAll('.section').forEach(s => s.classList.add('hidden'));
            document.getElementById(section).classList.remove('hidden');
            
            document.querySelectorAll('.navbar-menu a').forEach(a => a.classList.remove('active'));
            event.target.classList.add('active');

            if (section === 'materias') loadMaterias();
            if (section === 'horario') generarHorarioSemanal();
        }

        async function loadPersonalInfo() {
            try {
                // Cargar información del docente desde API
                // Por ahora usamos datos del PHP
                document.getElementById('info-nombre').textContent = '<?php echo htmlspecialchars($user['nombre'] ?? ''); ?>';
                document.getElementById('info-apellido').textContent = '<?php echo htmlspecialchars($user['apellido'] ?? ''); ?>';
                document.getElementById('info-email').textContent = '<?php echo htmlspecialchars($user['email'] ?? ''); ?>';
            } catch (e) {
                console.error('Error cargando información:', e);
            }
        }

        async function loadMaterias() {
            try {
                // Placeholder para cargar materias del docente
                const tbody = document.getElementById('materias-tbody');
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Sin materias asignadas actualmente</td></tr>';
            } catch (e) {
                showAlert('Error al cargar materias', 'danger');
            }
        }

        function generarHorarioSemanal() {
            // Placeholder para horario
            const tbody = document.getElementById('horario-tbody');
            const horas = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
            
            tbody.innerHTML = horas.map(hora => `
                <tr>
                    <td><strong>${hora}</strong></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            `).join('');
        }

        function generateHorarioVacio() {
            generarHorarioSemanal();
        }

        async function saveDisponibilidad(e) {
            e.preventDefault();
            try {
                const formData = getFormData(e.target);
                showAlert('Disponibilidad guardada correctamente', 'success');
                // Aquí iría la llamada a la API
            } catch (e) {
                showAlert('Error al guardar disponibilidad', 'danger');
            }
        }

        function resetDisponibilidad() {
            document.getElementById('form-disponibilidad').reset();
        }

        async function editarPerfil(e) {
            e.preventDefault();
            try {
                const formData = getFormData(e.target);
                showAlert('Perfil actualizado correctamente', 'success');
                closeModal('modal-editar');
                loadPersonalInfo();
            } catch (e) {
                showAlert('Error al actualizar perfil', 'danger');
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
