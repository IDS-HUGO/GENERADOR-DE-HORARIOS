/**
 * ClassControl Helpers
 * Funciones comunes para todo el sistema
 */

// Crear tabla HTML desde datos
function createTable(data, columns) {
    let html = '<table class="table"><thead><tr>';
    columns.forEach(c => html += `<th>${c.label}</th>`);
    html += '</tr></thead><tbody>';
    
    data.forEach(row => {
        html += '<tr>';
        columns.forEach(col => {
            const value = col.key ? row[col.key] : '';
            const formatted = col.format ? col.format(value) : value;
            html += `<td>${formatted}</td>`;
        });
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    return html;
}

// Exportar a CSV
function exportToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const csv = [];
    const headers = [];
    table.querySelectorAll('th').forEach(th => headers.push(th.textContent.trim()));
    csv.push(headers.join(','));
    
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => row.push(`"${td.textContent.replace(/"/g, '""')}"`));
        csv.push(row.join(','));
    });
    
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename || 'export.csv';
    a.click();
}

// Parsear respuesta API
function parseApiResponse(resp) {
    if (!resp) return null;
    if (resp.success) {
        if (resp.message) showAlert(resp.message, 'success');
        return resp.data || null;
    }
    if (resp.message) showAlert(resp.message, 'danger');
    return null;
}

// Debug log
function debugLog(msg, data = null) {
    console.log('[DEBUG]', msg, data);
}

// Sleep/delay
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

// Validar formulario simple
function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    for (const [fieldId, rule] of Object.entries(rules)) {
        const field = document.getElementById(fieldId);
        if (!field) continue;
        
        const value = field.value.trim();
        
        if (rule.required && !value) {
            showAlert(`❌ ${rule.label} es requerido`, 'danger');
            field.focus();
            return false;
        }
        
        if (rule.pattern && !rule.pattern.test(value)) {
            showAlert(`❌ ${rule.label} formato inválido`, 'danger');
            field.focus();
            return false;
        }
    }
    
    return true;
}

// Obtener fecha actual formateada
function getTodayFormatted() {
    const now = new Date();
    return now.toISOString().split('T')[0];
}

// Obtener hora actual formateada
function getNowFormatted() {
    const now = new Date();
    return now.toTimeString().slice(0, 5);
}

// Confirmar acción
function confirmAction(message = '¿Está seguro?') {
    return confirm(message);
}

// Copy al portapapeles
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showAlert('✅ Copiado al portapapeles', 'success', 1500);
        return true;
    } catch (err) {
        console.error('[COPY ERROR]', err);
        return false;
    }
}

console.log('[HELPERS] Funciones auxiliares cargadas');
