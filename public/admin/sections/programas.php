<section id="programas" class="page-section">
    <div class="page-header">
        <h1>Programas académicos</h1>
        <?php if ($tipo !== 'director'): ?>
        <button class="btn btn-primary" onclick="showProgramaModal()">+ Nuevo programa</button>
        <?php endif; ?>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Nivel</th>
                        <th>Duración</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="programas-tbody">
                    <tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div id="modal-programa" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal-header">
            <h2>Crear programa académico</h2>
            <button class="modal-close" onclick="closeProgramaModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-programa" onsubmit="submitPrograma(event)">
                <div class="form-group">
                    <label>Código</label>
                    <input type="text" name="codigo" required>
                </div>
                <div class="form-group">
                    <label>Nombre</label>
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
                    <button type="button" class="btn btn-secondary" onclick="closeProgramaModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadProgramas();
    });

    function showProgramaModal() {
        const modal = document.getElementById('modal-programa');
        if (modal) modal.classList.remove('hidden');
    }

    function closeProgramaModal() {
        const modal = document.getElementById('modal-programa');
        if (modal) modal.classList.add('hidden');
    }

    async function loadProgramas() {
        try {
            const resp = await api('/src/api/programas.php?action=list');
            const tbody = document.getElementById('programas-tbody');
            if (!resp.data || resp.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay programas registrados</td></tr>';
                return;
            }
            tbody.innerHTML = resp.data.map(p => `
                <tr>
                    <td>${htmlEscape(p.codigo)}</td>
                    <td>${htmlEscape(p.nombre)}</td>
                    <td>${htmlEscape(p.nivel)}</td>
                    <td>${p.duracion_semestres} semestres</td>
                    <td><button class="btn btn-sm btn-danger" onclick="deletePrograma(${p.id || p.programa_id})">Eliminar</button></td>
                </tr>
            `).join('');
        } catch (e) {
            showAlert('error', 'No se pudieron cargar los programas');
        }
    }

    async function submitPrograma(event) {
        event.preventDefault();
        const form = event.target;
        const data = Object.fromEntries(new FormData(form));
        try {
            const resp = await api('/src/api/programas.php?action=create', { method: 'POST', body: JSON.stringify(data) });
            if (resp.success) {
                showAlert('success', 'Programa creado correctamente');
                form.reset();
                closeProgramaModal();
                loadProgramas();
            } else {
                showAlert('error', resp.message || 'Error al guardar');
            }
        } catch (e) {
            showAlert('error', 'Error al guardar');
        }
    }

    async function deletePrograma(id) {
        if (!confirm('¿Eliminar este programa?')) return;
        try {
            const resp = await api('/src/api/programas.php?action=delete', { method: 'POST', body: JSON.stringify({ id }) });
            if (resp.success) {
                showAlert('success', 'Programa eliminado');
                loadProgramas();
            } else showAlert('error', resp.message || 'Error');
        } catch (e) { 
            showAlert('error', 'Error'); 
        }
    }
</script>
