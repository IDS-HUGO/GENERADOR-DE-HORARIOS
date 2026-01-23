<section id="materias" class="page-section">
    <div class="page-header">
        <h1>Materias</h1>
        <?php if ($tipo !== 'director'): ?>
        <button class="btn btn-primary" onclick="showMateriaModal()">+ Nueva materia</button>
        <?php endif; ?>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table">
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
</section>

<div id="modal-materia" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal-header">
            <h2>Agregar materia</h2>
            <button class="modal-close" onclick="closeMateriaModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-materia" onsubmit="submitMateria(event)">
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
                    <select id="programa-materia" name="programa_id" required>
                        <option value="">Cargando...</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Créditos</label>
                        <input type="number" name="creditos" min="1" max="10" value="3" required>
                    </div>
                    <div class="form-group">
                        <label>Semestre</label>
                        <input type="number" name="semestre" min="1" max="12" value="1" required>
                    </div>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="obligatoria" value="1"> Materia obligatoria</label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeMateriaModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadProgramasForMateria();
        loadMaterias();
    });

    function showMateriaModal() {
        const modal = document.getElementById('modal-materia');
        if (modal) modal.classList.remove('hidden');
    }

    function closeMateriaModal() {
        const modal = document.getElementById('modal-materia');
        if (modal) modal.classList.add('hidden');
    }

    async function loadProgramasForMateria() {
        try {
            const resp = await api('/src/api/programas.php?action=list');
            const select = document.getElementById('programa-materia');
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

    async function loadMaterias() {
        try {
            const [materiasResp, programasResp] = await Promise.all([
                api('/src/api/materias.php?action=list'),
                api('/src/api/programas.php?action=list')
            ]);
            const programasMap = {};
            programasResp.data?.forEach(p => programasMap[p.id || p.programa_id] = p.nombre);
            const tbody = document.getElementById('materias-tbody');
            if (!materiasResp.data || materiasResp.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay materias registradas</td></tr>';
                return;
            }
            tbody.innerHTML = materiasResp.data.map(m => `
                <tr>
                    <td>${htmlEscape(m.codigo)}</td>
                    <td>${htmlEscape(m.nombre)}</td>
                    <td>${htmlEscape(programasMap[m.programa_id] || 'N/A')}</td>
                    <td>${m.creditos}</td>
                    <td>${m.semestre}</td>
                    <td><button class="btn btn-sm btn-danger" onclick="deleteMateria(${m.id || m.materia_id})">Eliminar</button></td>
                </tr>
            `).join('');
        } catch (e) {
            showAlert('error', 'No se pudieron cargar las materias');
        }
    }

    async function submitMateria(event) {
        event.preventDefault();
        const form = event.target;
        const data = Object.fromEntries(new FormData(form));
        try {
            const resp = await api('/src/api/materias.php?action=create', { method: 'POST', body: JSON.stringify(data) });
            if (resp.success) {
                showAlert('success', 'Materia agregada correctamente');
                form.reset();
                closeMateriaModal();
                loadMaterias();
            } else {
                showAlert('error', resp.message || 'Error al guardar');
            }
        } catch (e) {
            showAlert('error', 'Error al guardar');
        }
    }

    async function deleteMateria(id) {
        if (!confirm('¿Eliminar esta materia?')) return;
        try {
            const resp = await api('/src/api/materias.php?action=delete', { method: 'POST', body: JSON.stringify({ id }) });
            if (resp.success) {
                showAlert('success', 'Materia eliminada');
                loadMaterias();
            } else showAlert('error', resp.message || 'Error');
        } catch (e) { 
            showAlert('error', 'Error'); 
        }
    }
</script>
