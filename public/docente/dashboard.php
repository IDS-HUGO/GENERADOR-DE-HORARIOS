<?php
require_once '../../src/config.php';

if (!isAuthenticated() || getCurrentUser()['tipo_usuario'] !== 'docente') {
    redirect(baseUrl('/public/index.html'));
}

$user = getCurrentUser();
$docente = (new Docente())->getByUserId($user['usuario_id']) ?? [];
$docenteId = $docente['docente_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Docente - Universidad Maya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/utils.js?v=2.1"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">🎓 Universidad Maya - Portal Docente</div>
            <ul class="navbar-menu">
                <li><a href="#" class="nav-link active" data-target="inicio">Inicio</a></li>
                <li><a href="seleccion-materias.php" class="nav-link">📚 Seleccionar Materias</a></li>
                <li><a href="#" class="nav-link" data-target="horario">Mi Horario</a></li>
                <li><a href="#" class="nav-link" data-target="disponibilidad">Disponibilidad</a></li>
                <li><a href="#" class="nav-link" data-target="materias">Materias</a></li>
            </ul>
            <div class="navbar-user">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($user['nombre'] ?? 'Docente'); ?></div>
                    <div class="user-role">👨‍🏫 Docente</div>
                </div>
                <button class="btn btn-sm btn-danger" id="btn-logout">Salir</button>
            </div>
        </div>
    </nav>

    <main class="container">
        <div id="alert-container"></div>

        <section id="inicio" class="section">
            <div class="grid grid-2" style="gap: 16px;">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Información personal</h3></div>
                    <div class="card-body">
                        <p><strong>Nombre:</strong> <span id="info-nombre"><?php echo htmlspecialchars($user['nombre'] ?? ''); ?></span></p>
                        <p><strong>Apellido:</strong> <span id="info-apellido"><?php echo htmlspecialchars($user['apellido'] ?? ''); ?></span></p>
                        <p><strong>Email:</strong> <span id="info-email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></span></p>
                        <p><strong>Teléfono:</strong> <span id="info-telefono">-</span></p>
                        <p><strong>Especialidad:</strong> <span id="info-especialidad">-</span></p>
                        <button class="btn btn-primary mt-2" onclick="openModal('modal-perfil')">Editar perfil</button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3 class="card-title">Resumen</h3></div>
                    <div class="card-body grid grid-2" style="gap: 12px;">
                        <div class="stat-card primary"><div class="stat-label">Materias</div><div class="stat-value" id="stat-materias">0</div></div>
                        <div class="stat-card success"><div class="stat-label">Grupos</div><div class="stat-value" id="stat-grupos">0</div></div>
                        <div class="stat-card warning"><div class="stat-label">Clases</div><div class="stat-value" id="stat-clases">0</div></div>
                        <div class="stat-card info"><div class="stat-label">Horas máx.</div><div class="stat-value" id="stat-horas"><?php echo htmlspecialchars($docente['horas_maximas_semanales'] ?? '0'); ?></div></div>
                    </div>
                </div>
            </div>
        </section>

        <section id="horario" class="section hidden">
            <div class="page-header"><h1>Mi horario</h1></div>
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Día</th>
                                <th>Hora inicio</th>
                                <th>Hora fin</th>
                                <th>Materia</th>
                                <th>Grupo</th>
                                <th>Aula</th>
                            </tr>
                        </thead>
                        <tbody id="horario-tbody">
                            <tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="disponibilidad" class="section hidden">
            <div class="page-header"><h1>Mi disponibilidad</h1></div>
            <div class="card">
                <div class="card-body">
                    <form id="form-disponibilidad">
                        <div class="grid grid-3" style="gap: 12px;">
                            <?php
                            $dias = ['lunes','martes','miercoles','jueves','viernes'];
                            foreach ($dias as $dia): ?>
                                <div class="form-group">
                                    <label><?php echo ucfirst($dia); ?></label>
                                    <div style="display:flex;gap:8px;">
                                        <input type="time" name="<?php echo $dia; ?>_inicio" value="08:00">
                                        <input type="time" name="<?php echo $dia; ?>_fin" value="20:00">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-group mt-3">
                            <label>Máximo de horas semanales</label>
                            <input type="number" name="horas_maximas" min="1" max="60" value="<?php echo htmlspecialchars($docente['horas_maximas_semanales'] ?? 40); ?>">
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Guardar disponibilidad</button>
                            <button type="button" class="btn btn-secondary" id="btn-reset-dispo">Restablecer</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <section id="materias" class="section hidden">
            <div class="page-header"><h1>Mis materias</h1></div>
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Materia</th>
                                <th>Grupo</th>
                                <th>Día</th>
                                <th>Horario</th>
                                <th>Aula</th>
                            </tr>
                        </thead>
                        <tbody id="materias-tbody">
                            <tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <div id="modal-perfil" class="modal-overlay hidden">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Editar perfil</h2>
                <button class="modal-close" data-close="modal-perfil">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-perfil">
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
                        <button type="button" class="btn btn-secondary" data-close="modal-perfil">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const DOCENTE_ID = <?php echo (int)$docenteId; ?>;
        const API_BASE = '<?php echo rtrim(baseUrl("src/api"), '/'); ?>';
        let horariosCache = [];

        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => link.addEventListener('click', (e) => {
            e.preventDefault();
            const target = link.dataset.target;
            loadSection(target);
        }));

        document.getElementById('btn-logout').addEventListener('click', handleLogout);

        document.querySelectorAll('[data-close]').forEach(btn => btn.addEventListener('click', () => closeModal(btn.dataset.close)));

        document.getElementById('form-perfil').addEventListener('submit', editarPerfil);
        document.getElementById('form-disponibilidad').addEventListener('submit', saveDisponibilidad);
        document.getElementById('btn-reset-dispo').addEventListener('click', resetDisponibilidad);

        document.addEventListener('DOMContentLoaded', () => {
            loadPerfil();
            loadDisponibilidad();
            loadHorario();
        });

        async function apiJson(path, options = {}) {
            const url = path.startsWith('http') ? path : `${API_BASE}${path.startsWith('/') ? '' : '/'}${path}`;
            const response = await fetch(url, {
                credentials: 'include',
                headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
                ...options
            });
            return response.json();
        }

        function loadSection(id) {
            document.querySelectorAll('.section').forEach(s => s.classList.add('hidden'));
            const section = document.getElementById(id);
            if (section) section.classList.remove('hidden');
            navLinks.forEach(l => l.classList.toggle('active', l.dataset.target === id));

            if (id === 'horario') loadHorario();
            if (id === 'disponibilidad') loadDisponibilidad();
            if (id === 'materias') renderMateriasDesdeHorario();
        }

        function showAlert(message, type = 'info', timeout = 2500) {
            const container = document.getElementById('alert-container');
            if (!container) return;
            const div = document.createElement('div');
            div.className = `alert alert-${type}`;
            div.textContent = message;
            container.appendChild(div);
            setTimeout(() => div.remove(), timeout);
        }

        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) modal.classList.remove('hidden');
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) modal.classList.add('hidden');
        }

        async function handleLogout() {
            if (!confirm('¿Deseas cerrar sesión?')) return;
            const res = await apiJson(`${API_BASE}/auth/logout.php`, { method: 'POST' });
            if (res.success) {
                localStorage.clear();
                sessionStorage.clear();
                const redirectUrl = res.data?.redirect || '<?php echo baseUrl("public/index.html"); ?>';
                window.location.href = redirectUrl;
            } else {
                showAlert(res.message || 'No se pudo cerrar sesión', 'danger');
            }
        }

        async function loadPerfil() {
            const res = await apiJson(`${API_BASE}/docentes.php?action=profile`);
            if (!res.success || !res.data) {
                showAlert(res.message || 'No se pudo cargar el perfil', 'danger');
                return;
            }

            const { usuario, docente } = res.data;
            document.getElementById('info-nombre').textContent = usuario?.nombre || '';
            document.getElementById('info-apellido').textContent = usuario?.apellido || '';
            document.getElementById('info-email').textContent = usuario?.email || '';
            document.getElementById('info-telefono').textContent = usuario?.telefono || '-';
            document.getElementById('info-especialidad').textContent = docente?.especialidad || '-';
            document.getElementById('stat-horas').textContent = docente?.horas_maximas_semanales || 0;

            const form = document.getElementById('form-perfil');
            form.nombre.value = usuario?.nombre || '';
            form.apellido.value = usuario?.apellido || '';
            form.telefono.value = usuario?.telefono || '';
            form.especialidad.value = docente?.especialidad || '';
        }

        async function editarPerfil(e) {
            e.preventDefault();
            const form = e.target;
            const payload = {
                nombre: form.nombre.value,
                apellido: form.apellido.value,
                telefono: form.telefono.value,
                especialidad: form.especialidad.value
            };
            const res = await apiJson(`${API_BASE}/docentes.php?action=update_self`, {
                method: 'POST',
                body: JSON.stringify(payload)
            });

            if (res.success) {
                showAlert('Perfil actualizado', 'success');
                closeModal('modal-perfil');
                loadPerfil();
            } else {
                showAlert(res.message || 'No se pudo actualizar', 'danger');
            }
        }

        async function loadDisponibilidad() {
            const res = await apiJson(`${API_BASE}/disponibilidad.php?action=list`);
            if (!res.success) {
                showAlert(res.message || 'No se pudo cargar disponibilidad', 'danger');
                return;
            }

            const form = document.getElementById('form-disponibilidad');
            const dias = res.data || [];
            dias.forEach(d => {
                const inicio = form[`${d.dia_semana}_inicio`];
                const fin = form[`${d.dia_semana}_fin`];
                if (inicio && fin) {
                    inicio.value = d.hora_inicio;
                    fin.value = d.hora_fin;
                }
            });
        }

        async function saveDisponibilidad(e) {
            e.preventDefault();
            const form = e.target;
            const dias = ['lunes','martes','miercoles','jueves','viernes'].map(dia => ({
                dia_semana: dia,
                hora_inicio: form[`${dia}_inicio`].value,
                hora_fin: form[`${dia}_fin`].value,
                disponible: 1,
                tipo_disponibilidad: 'disponible'
            }));

            const payload = {
                dias,
                horas_maximas: form.horas_maximas.value
            };

            const res = await apiJson(`${API_BASE}/disponibilidad.php?action=update`, {
                method: 'POST',
                body: JSON.stringify(payload)
            });

            if (res.success) {
                showAlert('Disponibilidad actualizada', 'success');
                loadDisponibilidad();
            } else {
                showAlert(res.message || 'No se pudo actualizar', 'danger');
            }
        }

        function resetDisponibilidad() {
            document.getElementById('form-disponibilidad').reset();
            loadDisponibilidad();
        }

        async function loadHorario() {
            const res = await apiJson(`${API_BASE}/horarios.php?action=list&docente_id=${DOCENTE_ID}`);
            if (!res.success) {
                showAlert(res.message || 'No se pudo cargar horario', 'danger');
                return;
            }
            horariosCache = res.data || [];
            renderHorario(horariosCache);
        }

        function renderHorario(horarios) {
            const tbody = document.getElementById('horario-tbody');
            if (!horarios || horarios.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Sin clases programadas</td></tr>';
                return;
            }

            tbody.innerHTML = horarios.map(h => `
                <tr>
                    <td>${h.dia_semana || ''}</td>
                    <td>${h.hora_inicio || ''}</td>
                    <td>${h.hora_fin || ''}</td>
                    <td>${h.materia_nombre || ''}</td>
                    <td>${h.grupo_codigo || ''}</td>
                    <td>${h.aula_codigo || ''}</td>
                </tr>
            `).join('');

            renderMateriasDesdeHorario();
        }

        function renderMateriasDesdeHorario() {
            const tbody = document.getElementById('materias-tbody');
            if (!horariosCache || horariosCache.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Sin materias asignadas</td></tr>';
                return;
            }

            const materiasMap = {};
            horariosCache.forEach(h => {
                const key = `${h.materia_id || ''}-${h.grupo_id || ''}`;
                if (!materiasMap[key]) {
                    materiasMap[key] = [];
                }
                materiasMap[key].push(h);
            });

            const rows = Object.values(materiasMap).map(arr => {
                const base = arr[0];
                const horariosTexto = arr.map(h => `${h.dia_semana} ${h.hora_inicio}-${h.hora_fin}`).join(', ');
                return `
                    <tr>
                        <td>${base.materia_nombre || ''}</td>
                        <td>${base.grupo_codigo || ''}</td>
                        <td>${arr[0].dia_semana || ''}</td>
                        <td>${horariosTexto}</td>
                        <td>${base.aula_codigo || ''}</td>
                    </tr>`;
            });

            tbody.innerHTML = rows.join('');

            document.getElementById('stat-materias').textContent = Object.keys(materiasMap).length;
            document.getElementById('stat-grupos').textContent = Object.keys(materiasMap).length;
            document.getElementById('stat-clases').textContent = horariosCache.length;
        }
    </script>
</body>
</html>
