<?php
/**
 * Sección: Gestión de Horarios
 * Visible para Administradores y Directores
 */

if (!in_array(getCurrentUser()['tipo_usuario'], ['administrador', 'director'])) {
    echo "<div class='alert alert-danger'>⛔ No tienes permisos para gestionar horarios</div>";
    exit;
}
?>

<div class="section-header">
    <div>
        <h1>📅 Gestión de Horarios</h1>
        <p style="color: #666; margin: 6px 0 0;">Administra, aprueba o rechaza horarios antes de su publicación final.</p>
    </div>
    <div>
        <button class="btn btn-secondary" onclick="cargarHorarios()">🔄 Actualizar</button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="filters" style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px;">
            <div>
                <label for="filtro-estado" style="font-weight: 600;">Estado</label>
                <select id="filtro-estado" class="form-control" style="min-width: 160px;">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="aprobado">Aprobado</option>
                    <option value="rechazado">Rechazado</option>
                </select>
            </div>
            <div>
                <label for="filtro-docente" style="font-weight: 600;">Docente (ID)</label>
                <input type="number" id="filtro-docente" class="form-control" placeholder="Opcional">
            </div>
            <div>
                <label for="filtro-grupo" style="font-weight: 600;">Grupo (ID)</label>
                <input type="number" id="filtro-grupo" class="form-control" placeholder="Opcional">
            </div>
            <div style="align-self: flex-end;">
                <button class="btn" onclick="aplicarFiltros()">Filtrar</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="tabla-horarios">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Docente</th>
                        <th>Materia</th>
                        <th>Grupo</th>
                        <th>Día</th>
                        <th>Hora</th>
                        <th>Aula</th>
                        <th>Estado</th>
                        <th>Confirmado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="10" style="text-align:center;">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let horariosData = [];

function estadoBadge(estado) {
    const map = {
        'aprobado': 'badge badge-confirmada',
        'pendiente': 'badge badge-pendiente',
        'rechazado': 'badge badge-cancelada',
        'borrador': 'badge badge-pendiente'
    };
    const cls = map[estado] || 'badge badge-pendiente';
    return `<span class="${cls}">${estado || 'pendiente'}</span>`;
}

async function cargarHorarios() {
    const estado = document.getElementById('filtro-estado').value;
    const docente = document.getElementById('filtro-docente').value;
    const grupo = document.getElementById('filtro-grupo').value;

    let url = `${API_BASE_URL}/src/api/horarios.php?action=gestion`;
    const params = [];
    if (docente) params.push('docente_id=' + encodeURIComponent(docente));
    if (grupo) params.push('grupo_id=' + encodeURIComponent(grupo));
    if (params.length) url += '&' + params.join('&');

    try {
        const resp = await fetch(url, { credentials: 'include' });
        const json = await resp.json();
        if (!json.success) {
            alert(json.message || 'Error al cargar horarios');
            return;
        }
        horariosData = json.data || [];
        renderHorarios(estado);
    } catch (e) {
        console.error(e);
        alert('Error al cargar horarios');
    }
}

function renderHorarios(estadoFiltro = '') {
    const tbody = document.querySelector('#tabla-horarios tbody');
    const rows = horariosData
        .filter(h => !estadoFiltro || h.estado_horario === estadoFiltro)
        .map(h => {
            const hora = `${h.hora_inicio} - ${h.hora_fin}`;
            const docente = `${h.docente_nombre || ''} ${h.docente_apellido || ''}`.trim();
            const acciones = `
                <button class="btn btn-sm btn-success" onclick="aprobar(${h.horario_id}, 'aprobado')">✅ Aprobar</button>
                <button class="btn btn-sm btn-warning" onclick="aprobar(${h.horario_id}, 'rechazado')">⚠️ Rechazar</button>
                <button class="btn btn-sm btn-danger" onclick="eliminarHorario(${h.horario_id})">🗑️ Eliminar</button>
            `;
            return `
                <tr>
                    <td>${h.horario_id}</td>
                    <td>${docente || '-'}<br><small>${h.docente_email || ''}</small></td>
                    <td>${h.materia_nombre || ''}</td>
                    <td>${h.grupo_codigo || ''}</td>
                    <td>${h.dia_semana}</td>
                    <td>${hora}</td>
                    <td>${h.aula_codigo || '-'}</td>
                    <td>${estadoBadge(h.estado_horario)}</td>
                    <td>${h.confirmado ? 'Sí' : 'No'}</td>
                    <td>${acciones}</td>
                </tr>
            `;
        }).join('');

    tbody.innerHTML = rows || '<tr><td colspan="10" style="text-align:center;">Sin resultados</td></tr>';
}

async function aprobar(id, estado) {
    const obs = estado === 'rechazado' ? prompt('Motivo de rechazo (opcional):') : null;
    try {
        const resp = await fetch(`${API_BASE_URL}/src/api/horarios.php?action=approve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ id, estado, observaciones: obs })
        });
        const json = await resp.json();
        if (!json.success) {
            alert(json.message || 'Error al actualizar');
            return;
        }
        await cargarHorarios();
    } catch (e) {
        console.error(e);
        alert('Error al actualizar horario');
    }
}

async function eliminarHorario(id) {
    if (!confirm('¿Eliminar este horario?')) return;
    try {
        const resp = await fetch(`${API_BASE_URL}/src/api/horarios.php?action=delete&id=${id}`, {
            method: 'DELETE',
            credentials: 'include'
        });
        const json = await resp.json();
        if (!json.success) {
            alert(json.message || 'Error al eliminar');
            return;
        }
        await cargarHorarios();
    } catch (e) {
        console.error(e);
        alert('Error al eliminar horario');
    }
}

function aplicarFiltros() {
    const estado = document.getElementById('filtro-estado').value;
    renderHorarios(estado);
}

window.addEventListener('DOMContentLoaded', cargarHorarios);
</script>
