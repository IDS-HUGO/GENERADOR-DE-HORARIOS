<section id="grupos" class="page-section">
    <div class="page-header">
        <h1>Grupos</h1>
        <button class="btn btn-primary" onclick="showGrupoModal()">+ Nuevo grupo</button>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table">
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
</section>

<div id="modal-grupo" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal-header">
            <h2>Crear grupo</h2>
            <button class="modal-close" onclick="closeGrupoModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-grupo" onsubmit="submitGrupo(event)">
                <div class="form-group">
                    <label>Código</label>
                    <input type="text" name="codigo" required>
                </div>
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>
                </div>
                <div class="form-group">
                    <label>Programa</label>
                    <select id="programa-grupo" name="programa_id" required>
                        <option value="">Cargando...</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Semestre</label>
                        <input type="number" name="semestre" min="1" max="12" value="1" required>
                    </div>
                    <div class="form-group">
                        <label>Cantidad de estudiantes</label>
                        <input type="number" name="cantidad_estudiantes" min="1" max="100" value="25">
                    </div>
                </div>
                <div class="form-group">
                    <label>Jornada</label>
                    <select name="jornada" required>
                        <option value="matutina">Matutina</option>
                        <option value="vespertina">Vespertina</option>
                        <option value="nocturna">Nocturna</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeGrupoModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadProgramasForGrupo();
        loadGrupos();
    });

    function showGrupoModal() {
        const modal = document.getElementById('modal-grupo');
        if (modal) modal.classList.remove('hidden');
    }

    function closeGrupoModal() {
        const modal = document.getElementById('modal-grupo');
        if (modal) modal.classList.add('hidden');
    }

    async function loadProgramasForGrupo() {
        try {
            const resp = await api('/src/api/programas.php?action=list');
            const select = document.getElementById('programa-grupo');
            select.innerHTML = '<option value="">Seleccionar programa...</option>';
            (resp.data || []).forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id || p.programa_id;
                opt.textContent = p.nombre;
                select.appendChild(opt);
            });
        } catch (e) {
            console.error('Error cargando programas', e);
        }
    }

    async function loadGrupos() {
        try {
            const [gruposResp, programasResp] = await Promise.all([
                api('/src/api/grupos.php?action=list'),
                api('/src/api/programas.php?action=list')
            ]);
            const programasMap = {};
            programasResp.data?.forEach(p => programasMap[p.id || p.programa_id] = p.nombre);
            const tbody = document.getElementById('grupos-tbody');
            if (!gruposResp.data || gruposResp.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay grupos registrados</td></tr>';
                return;
            }
            tbody.innerHTML = gruposResp.data.map(g => `
                <tr>
                    <td>${htmlEscape(g.codigo)}</td>
                    <td>${htmlEscape(g.nombre)}</td>
                    <td>${htmlEscape(programasMap[g.programa_id] || 'N/A')}</td>
                    <td>${g.semestre}</td>
                    <td>${g.cantidad_estudiantes || 0}</td>
                    <td><button class="btn btn-sm btn-danger" onclick="deleteGrupo(${g.id || g.grupo_id})">Eliminar</button></td>
                </tr>
            `).join('');
        } catch (e) {
            showAlert('error', 'No se pudieron cargar los grupos');
        }
    }

    async function submitGrupo(event) {
        event.preventDefault();
        const form = event.target;
        const data = Object.fromEntries(new FormData(form));
        try {
            const resp = await api('/src/api/grupos.php?action=create', { method: 'POST', body: JSON.stringify(data) });
            if (resp.success) {
                showAlert('success', 'Grupo creado correctamente');
                form.reset();
                closeGrupoModal();
                loadGrupos();
            } else {
                showAlert('error', resp.message || 'Error al guardar');
            }
        } catch (e) {
            showAlert('error', 'Error al guardar');
        }
    }

    async function deleteGrupo(id) {
        if (!confirm('¿Eliminar este grupo?')) return;
        try {
            const resp = await api('/src/api/grupos.php?action=delete', { method: 'POST', body: JSON.stringify({ id }) });
            if (resp.success) {
                showAlert('success', 'Grupo eliminado');
                loadGrupos();
            } else showAlert('error', resp.message || 'Error');
        } catch (e) { 
            showAlert('error', 'Error'); 
        }
    }
</script>
