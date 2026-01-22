/**
 * ClassControl - Funciones de Reportes y Exportación
 */

// Generar reporte de docentes
async function generarReporteDocentes(filtros = {}) {
    try {
        const docentes = await api('/src/api/docentes.php?action=list', { method: 'GET' });
        if (!docentes.success) {
            showAlert('Error al cargar docentes', 'danger');
            return;
        }
        
        const data = docentes.data || [];
        
        // Aplicar filtros
        let filtered = data;
        if (filtros.departamento) {
            filtered = filtered.filter(d => d.departamento === filtros.departamento);
        }
        if (filtros.buscar) {
            const search = filtros.buscar.toLowerCase();
            filtered = filtered.filter(d => 
                d.nombre.toLowerCase().includes(search) || 
                d.email.toLowerCase().includes(search)
            );
        }
        
        return exportarACSV(filtered, 'docentes.csv', ['nombre', 'email', 'departamento', 'telefono']);
    } catch (e) {
        showAlert(e.message, 'danger');
    }
}

// Generar reporte de horarios
async function generarReporteHorarios(docenteId = null) {
    try {
        const horarios = await api('/src/api/horarios.php?action=list', { method: 'GET' });
        if (!horarios.success) {
            showAlert('Error al cargar horarios', 'danger');
            return;
        }
        
        let data = horarios.data || [];
        if (docenteId) {
            data = data.filter(h => h.docente_id === docenteId);
        }
        
        return exportarACSV(data, 'horarios.csv', ['dia', 'hora_inicio', 'hora_fin', 'materia', 'grupo']);
    } catch (e) {
        showAlert(e.message, 'danger');
    }
}

// Exportar a CSV avanzado
function exportarACSV(datos, nombre = 'export.csv', columnas = null) {
    if (!Array.isArray(datos) || datos.length === 0) {
        showAlert('No hay datos para exportar', 'warning');
        return false;
    }
    
    const cols = columnas || Object.keys(datos[0]);
    const csv = [];
    
    // Headers
    csv.push(cols.map(c => `"${c}"`).join(','));
    
    // Rows
    datos.forEach(row => {
        const rowData = cols.map(col => {
            let val = row[col] || '';
            val = String(val).replace(/"/g, '""');
            return `"${val}"`;
        });
        csv.push(rowData.join(','));
    });
    
    const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', nombre);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showAlert(`✓ ${nombre} descargado`, 'success', 2000);
    return true;
}

// Generar reporte PDF (simulado con tabla formateada)
function generarReportePDF(titulo, datos, columnas) {
    let html = `
    <html>
    <head>
        <title>${titulo}</title>
        <style>
            body { font-family: Arial; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #4a90e2; color: white; }
            h1 { color: #333; }
        </style>
    </head>
    <body>
        <h1>${titulo}</h1>
        <p>Generado: ${new Date().toLocaleDateString()}</p>
        <table>
            <thead>
                <tr>${columnas.map(c => `<th>${c}</th>`).join('')}</tr>
            </thead>
            <tbody>
    `;
    
    datos.forEach(row => {
        html += '<tr>';
        columnas.forEach(col => {
            html += `<td>${row[col.toLowerCase()] || ''}</td>`;
        });
        html += '</tr>';
    });
    
    html += `
            </tbody>
        </table>
    </body>
    </html>
    `;
    
    const ventana = window.open('', '_blank');
    ventana.document.write(html);
    ventana.document.close();
    ventana.print();
}

console.log('[REPORTES] Módulo de reportes y exportación cargado');
