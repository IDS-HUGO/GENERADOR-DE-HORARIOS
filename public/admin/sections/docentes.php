<section id="docentes" class="page-section">
    <div class="page-header">
        <h1>Docentes</h1>
        <button class="btn btn-primary" onclick="showDocenteModal()">+ Nuevo docente</button>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Especialidad</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="docentes-tbody">
                    <tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div id="modal-docente" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal-header">
            <h2>Registrar nuevo docente</h2>
            <button class="modal-close" onclick="closeDocenteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-docente" onsubmit="submitDocente(event)">
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
                    <label>Horas asignadas</label>
                    <input type="number" name="horas_asignadas" min="1" max="50" value="20">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDocenteModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadDocentes();
    });

    function showDocenteModal() {
        const modal = document.getElementById('modal-docente');
        if (modal) modal.classList.remove('hidden');
    }

    function closeDocenteModal() {
        const modal = document.getElementById('modal-docente');
        if (modal) modal.classList.add('hidden');
    }

    async function loadDocentes() {
        try {
            const resp = await api('/src/api/docentes.php?action=list');
            const tbody = document.getElementById('docentes-tbody');
            if (!resp.data || resp.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay docentes registrados</td></tr>';
                return;
            }
            tbody.innerHTML = resp.data.map(d => `
                <tr>
                    <td>${htmlEscape(d.nombre)} ${htmlEscape(d.apellido)}</td>
                    <td>${htmlEscape(d.email)}</td>
                    <td>${htmlEscape(d.especialidad || 'N/A')}</td>
                    <td>${htmlEscape(d.telefono || 'N/A')}</td>
                    <td><button class="btn btn-sm btn-danger" onclick="deleteDocente(${d.id || d.docente_id})">Eliminar</button></td>
                </tr>
            `).join('');
        } catch (e) {
            showAlert('error', 'No se pudieron cargar los docentes');
        }
    }

    async function submitDocente(event) {
        event.preventDefault();
        const form = event.target;
        const data = Object.fromEntries(new FormData(form));
        try {
            const resp = await api('/src/api/docentes.php?action=create', { method: 'POST', body: JSON.stringify(data) });
            if (resp.success) {
                showAlert('success', 'Docente registrado correctamente');
                form.reset();
                closeDocenteModal();
                loadDocentes();
            } else {
                showAlert('error', resp.message || 'Error al guardar');
            }
        } catch (e) {
            showAlert('error', 'Error al guardar');
        }
    }

    async function deleteDocente(id) {
        if (!confirm('¿Eliminar este docente?')) return;
        try {
            const resp = await api('/src/api/docentes.php?action=delete', { method: 'POST', body: JSON.stringify({ id }) });
            if (resp.success) {
                showAlert('success', 'Docente eliminado');
                loadDocentes();
            } else showAlert('error', resp.message || 'Error');
        } catch (e) { 
            showAlert('error', 'Error'); 
        }
    }
</script>
