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
            <div id="docente-info" class="alert alert-info hidden" style="margin-bottom: 20px;">
                <strong>✅ ¡Docente creado!</strong>
                <p style="margin: 8px 0 0 0;">La contraseña temporal y las credenciales han sido enviadas al email del docente.</p>
            </div>
            <form id="form-docente" onsubmit="submitDocente(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre <span style="color: red;">*</span></label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Apellido <span style="color: red;">*</span></label>
                        <input type="text" name="apellido" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email <span style="color: red;">*</span></label>
                    <input type="email" name="email" required placeholder="ejemplo@gmail.com">
                    <small style="color: #666;">Se usará para enviar las credenciales de acceso</small>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Número de identificación</label>
                        <input type="text" name="numero_identificacion" placeholder="CC/NIT">
                    </div>
                    <div class="form-group">
                        <label>Especialidad <span style="color: red;">*</span></label>
                        <input type="text" name="especialidad" required placeholder="Ej: Matemáticas, Inglés">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Horas asignadas</label>
                        <input type="number" name="horas_asignadas" min="1" max="50" value="20">
                    </div>
                    <div class="form-group">
                        <label>Horas máximas semanales</label>
                        <input type="number" name="horas_maximas_semanales" min="1" max="60" value="40">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Antigüedad (años)</label>
                        <input type="number" name="antiguedad" min="0" max="50" value="0">
                    </div>
                    <div class="form-group">
                        <label>Tipo de contrato</label>
                        <select name="tipo_contrato">
                            <option value="por_horas">Por horas</option>
                            <option value="medio_tiempo">Medio tiempo</option>
                            <option value="tiempo_completo">Tiempo completo</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha de contratación</label>
                        <input type="date" name="fecha_contratacion" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Foto (URL opcional)</label>
                        <input type="text" name="foto_perfil" placeholder="http://...jpg">
                    </div>
                </div>
                <div class="alert alert-warning">
                    <strong>ℹ️ Nota:</strong> La contraseña se genera automáticamente. Si el correo falla se mostrará en la alerta.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDocenteModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Docente</button>
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
        if (modal) {
            modal.classList.remove('hidden');
            document.getElementById('docente-info').classList.add('hidden');
        }
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
            showAlert('Error cargando docentes', 'danger');
            console.error(e);
        }
    }

    async function submitDocente(event) {
        event.preventDefault();
        const form = event.target;
        const data = Object.fromEntries(new FormData(form));
        
        try {
            showLoading(true);
            const resp = await api('/src/api/docentes.php?action=create', { 
                method: 'POST', 
                body: JSON.stringify(data) 
            });
            
            if (resp.success) {
                const c = resp.data || {};
                const pass = c.password_temporal || c.temp_password || 'N/A';
                const emailStatus = c.email_enviado ? '✉️ Email enviado' : '⚠️ Email NO enviado (comparte la contraseña manualmente)';

                showAlert(`✅ Docente creado\n📧 ${c.email}\n🔐 ${pass}\n${emailStatus}`, 'success', 4000);
                console.log('📊 Docente creado con credenciales:', c);

                form.reset();
                loadDocentes();
                closeDocenteModal();
            } else {
                showAlert('❌ ' + (resp.message || 'Error al guardar'), 'danger', 4000);
            }
        } catch (e) {
            console.error(e);
            showAlert('❌ Error: ' + e.message, 'danger', 3000);
        } finally {
            showLoading(false);
        }
    }

    async function deleteDocente(id) {
        if (!confirm('¿Eliminar este docente?')) return;
        try {
            showLoading(true);
            const resp = await api('/src/api/docentes.php?action=delete', { 
                method: 'POST', 
                body: JSON.stringify({ id }) 
            });
            if (resp.success) {
                showAlert('✅ Docente eliminado', 'success', 2000);
                loadDocentes();
            } else {
                showAlert('❌ ' + (resp.message || 'Error'), 'danger', 3000);
            }
        } catch (e) { 
            showAlert('❌ Error', 'danger', 3000);
            console.error(e);
        } finally {
            showLoading(false);
        }
    }
</script>

