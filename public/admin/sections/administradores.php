<?php
/**
 * Sección: Gestión de Administradores (Solo para Directores)
 * Universidad Maya - Sistema de Gestión de Horarios
 */

// Verificar que el usuario es director
$user = getCurrentUser();
if ($user['tipo_usuario'] !== 'director') {
    echo "<div class='alert alert-danger'>⛔ Solo los directores pueden acceder a esta sección</div>";
    exit;
}
?>

<div class="section-header">
    <h1>👥 Gestión de Administradores</h1>
    <button class="btn btn-primary" onclick="abrirModalAdmin()">➕ Nuevo Administrador</button>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Administradores del Sistema</h3>
    </div>
    <div class="card-body">
        <table class="table" id="tabla-administradores">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Último Acceso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="8" style="text-align: center;">Cargando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Crear/Editar Administrador -->
<div id="modal-admin" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Nuevo Administrador</h2>
            <button class="modal-close" onclick="cerrarModalAdmin()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-admin">
                <input type="hidden" id="admin-id" name="usuario_id">
                
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" id="admin-nombre" name="nombre" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Apellido *</label>
                    <input type="text" id="admin-apellido" name="apellido" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" id="admin-email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" id="admin-telefono" name="telefono" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Contraseña <span id="password-hint">(Requerida para nuevo admin)</span></label>
                    <input type="password" id="admin-password" name="password" class="form-control">
                    <small class="text-muted">Mínimo 8 caracteres. Dejar vacío para no cambiar al editar.</small>
                </div>
                
                <div class="form-group">
                    <label>Estado *</label>
                    <select id="admin-estado" name="estado" class="form-control">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModalAdmin()">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarAdmin()">Guardar</button>
        </div>
    </div>
</div>

<style>
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    
    .modal-content {
        background: white;
        border-radius: 8px;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .modal-header {
        padding: 20px;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-footer {
        padding: 20px;
        border-top: 1px solid #e0e0e0;
        text-align: right;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #999;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
    }
    
    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }
    
    .badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .badge-activo { background: #E8F5E9; color: #388E3C; }
    .badge-inactivo { background: #FFEBEE; color: #D32F2F; }
    .badge-pendiente { background: #FFF3E0; color: #F57C00; }
</style>

<script>
let editandoAdmin = false;

async function cargarAdministradores() {
    try {
        const response = await fetch(`${API_BASE_URL}/src/api/administradores.php?action=list`);
        const result = await response.json();
        
        if (result.success) {
            const admins = result.data;
            const tbody = document.querySelector('#tabla-administradores tbody');
            
            if (admins.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">No hay administradores registrados</td></tr>';
            } else {
                tbody.innerHTML = admins.map(admin => `
                    <tr>
                        <td>${admin.usuario_id}</td>
                        <td>${admin.nombre}</td>
                        <td>${admin.apellido}</td>
                        <td>${admin.email}</td>
                        <td>${admin.telefono || '-'}</td>
                        <td><span class="badge badge-${admin.estado}">${admin.estado}</span></td>
                        <td>${admin.ultimo_acceso ? new Date(admin.ultimo_acceso).toLocaleString('es-MX') : 'Nunca'}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editarAdmin(${admin.usuario_id})">✏️ Editar</button>
                            ${admin.estado === 'activo' ? 
                                `<button class="btn btn-sm btn-danger" onclick="desactivarAdmin(${admin.usuario_id})">🚫 Desactivar</button>` :
                                `<button class="btn btn-sm btn-success" onclick="activarAdmin(${admin.usuario_id})">✅ Activar</button>`
                            }
                        </td>
                    </tr>
                `).join('');
            }
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error cargando administradores:', error);
        alert('Error al cargar administradores');
    }
}

function abrirModalAdmin() {
    editandoAdmin = false;
    document.getElementById('modal-title').textContent = 'Nuevo Administrador';
    document.getElementById('form-admin').reset();
    document.getElementById('admin-id').value = '';
    document.getElementById('admin-password').required = true;
    document.getElementById('password-hint').textContent = '(Requerida)';
    document.getElementById('modal-admin').style.display = 'flex';
}

async function editarAdmin(id) {
    try {
        const response = await fetch(`${API_BASE_URL}/src/api/administradores.php?action=get&id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            editandoAdmin = true;
            const admin = result.data;
            document.getElementById('modal-title').textContent = 'Editar Administrador';
            document.getElementById('admin-id').value = admin.usuario_id;
            document.getElementById('admin-nombre').value = admin.nombre;
            document.getElementById('admin-apellido').value = admin.apellido;
            document.getElementById('admin-email').value = admin.email;
            document.getElementById('admin-telefono').value = admin.telefono || '';
            document.getElementById('admin-estado').value = admin.estado;
            document.getElementById('admin-password').required = false;
            document.getElementById('admin-password').value = '';
            document.getElementById('password-hint').textContent = '(Opcional - dejar vacío para no cambiar)';
            document.getElementById('modal-admin').style.display = 'flex';
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar administrador');
    }
}

function cerrarModalAdmin() {
    document.getElementById('modal-admin').style.display = 'none';
}

async function guardarAdmin() {
    const form = document.getElementById('form-admin');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    // Validar contraseña si es nuevo admin
    if (!editandoAdmin && (!data.password || data.password.length < 8)) {
        alert('La contraseña debe tener al menos 8 caracteres');
        return;
    }
    
    try {
        const url = `${API_BASE_URL}/src/api/administradores.php`;
        const method = editandoAdmin ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        alert(result.message);
        
        if (result.success) {
            cerrarModalAdmin();
            await cargarAdministradores();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar administrador');
    }
}

async function desactivarAdmin(id) {
    if (!confirm('¿Confirmas desactivar este administrador? Ya no podrá acceder al sistema.')) return;
    
    try {
        const response = await fetch(`${API_BASE_URL}/src/api/administradores.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        alert(result.message);
        
        if (result.success) {
            await cargarAdministradores();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al desactivar administrador');
    }
}

async function activarAdmin(id) {
    try {
        const response = await fetch(`${API_BASE_URL}/src/api/administradores.php`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ usuario_id: id, estado: 'activo' })
        });
        
        const result = await response.json();
        alert(result.message);
        
        if (result.success) {
            await cargarAdministradores();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al activar administrador');
    }
}

// Cargar al iniciar
window.addEventListener('DOMContentLoaded', cargarAdministradores);
</script>
