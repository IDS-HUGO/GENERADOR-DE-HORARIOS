/**
 * Módulo de Análisis Avanzado y Reportería
 * Generación de reportes personalizados, análisis de datos y exportación
 */

class AnalizadorReportes {
    constructor() {
        this.reportesGuardados = [];
        this.datosAnalisis = {};
    }

    /**
     * Generar reporte personalizado
     */
    async generarReporte(tipo, filtros = {}) {
        try {
            let url = `/src/api/reportes.php?action=generar&tipo=${tipo}`;
            
            // Agregar filtros a la URL
            Object.entries(filtros).forEach(([key, value]) => {
                if (value !== null && value !== undefined) {
                    url += `&${key}=${encodeURIComponent(value)}`;
                }
            });

            const response = await api(url);

            if (response.success) {
                console.log('[REPORTES] Reporte generado:', tipo);
                return response.data;
            } else {
                notificaciones.error(response.message);
                return null;
            }
        } catch (error) {
            console.error('[REPORTES] Error:', error);
            notificaciones.error('Error generando reporte');
            return null;
        }
    }

    /**
     * Obtener resumen ejecutivo
     */
    async obtenerResumen() {
        try {
            const response = await api('/src/api/reportes.php?action=resumen');

            if (response.success) {
                return response.data;
            }
            return null;
        } catch (error) {
            console.error('[RESUMEN] Error:', error);
            return null;
        }
    }

    /**
     * Análisis de carga de docentes
     */
    async analizarCargaDocentes() {
        try {
            const response = await api('/src/api/estadisticas.php?action=carga_docentes');

            if (response.success) {
                this.datosAnalisis.cargaDocentes = response.data;
                return response.data;
            }
            return null;
        } catch (error) {
            console.error('[CARGA] Error:', error);
            return null;
        }
    }

    /**
     * Análisis de utilización de aulas
     */
    async analizarUtilizacionAulas() {
        try {
            const response = await api('/src/api/estadisticas.php?action=utilizacion_aulas');

            if (response.success) {
                this.datosAnalisis.utilizacionAulas = response.data;
                return response.data;
            }
            return null;
        } catch (error) {
            console.error('[AULAS] Error:', error);
            return null;
        }
    }

    /**
     * Generar gráfico de estadísticas
     */
    async generarGrafico(tipo, datos) {
        try {
            // Usar Chart.js si está disponible
            if (typeof Chart === 'undefined') {
                console.warn('[GRAFICOS] Chart.js no cargado');
                return null;
            }

            const canvas = document.createElement('canvas');
            canvas.id = `grafico_${Date.now()}`;
            
            let configuracion = {};

            switch(tipo) {
                case 'barras':
                    configuracion = {
                        type: 'bar',
                        data: datos,
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: true }
                            }
                        }
                    };
                    break;

                case 'pastel':
                    configuracion = {
                        type: 'doughnut',
                        data: datos,
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'bottom' }
                            }
                        }
                    };
                    break;

                case 'linea':
                    configuracion = {
                        type: 'line',
                        data: datos,
                        options: {
                            responsive: true,
                            tension: 0.4
                        }
                    };
                    break;
            }

            return new Chart(canvas.getContext('2d'), configuracion);
        } catch (error) {
            console.error('[GRAFICOS] Error:', error);
            return null;
        }
    }

    /**
     * Exportar reporte a Excel
     */
    async exportarExcel(datos, nombreArchivo = 'reporte.xlsx') {
        try {
            // Convertir JSON a CSV temporal (sin librería externa)
            let csv = '';
            
            if (Array.isArray(datos) && datos.length > 0) {
                // Encabezados
                const headers = Object.keys(datos[0]);
                csv = headers.join(',') + '\n';
                
                // Datos
                datos.forEach(fila => {
                    const valores = headers.map(h => {
                        const valor = fila[h];
                        // Escapar comillas y envolver en comillas si contiene comas
                        return typeof valor === 'string' && valor.includes(',') 
                            ? `"${valor.replace(/"/g, '""')}"` 
                            : valor;
                    });
                    csv += valores.join(',') + '\n';
                });
            }

            // Descargar como CSV (compatible con Excel)
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = nombreArchivo;
            link.click();

            notificaciones.exito('Archivo descargado');
            return true;
        } catch (error) {
            console.error('[EXCEL] Error:', error);
            notificaciones.error('Error exportando');
            return false;
        }
    }

    /**
     * Guardar reporte personalizado
     */
    async guardarReporte(nombre, configuracion) {
        try {
            const reporte = {
                id: Date.now(),
                nombre: nombre,
                configuracion: configuracion,
                createdAt: new Date().toISOString()
            };

            this.reportesGuardados.push(reporte);

            // Guardar en localStorage
            localStorage.setItem('reportesPersonalizados', JSON.stringify(this.reportesGuardados));

            notificaciones.exito(`Reporte guardado: ${nombre}`);
            return reporte;
        } catch (error) {
            console.error('[GUARDAR] Error:', error);
            notificaciones.error('Error guardando reporte');
            return null;
        }
    }

    /**
     * Obtener reportes guardados
     */
    obtenerReportesGuardados() {
        try {
            const guardados = localStorage.getItem('reportesPersonalizados');
            if (guardados) {
                this.reportesGuardados = JSON.parse(guardados);
            }
            return this.reportesGuardados;
        } catch (error) {
            console.error('[CARGAR] Error:', error);
            return [];
        }
    }

    /**
     * Eliminar reporte guardado
     */
    async eliminarReporte(reporteId) {
        this.reportesGuardados = this.reportesGuardados.filter(r => r.id !== reporteId);
        localStorage.setItem('reportesPersonalizados', JSON.stringify(this.reportesGuardados));
        notificaciones.exito('Reporte eliminado');
    }

    /**
     * Compartir reporte por email
     */
    async compartirReporte(reporteId, emails) {
        try {
            const response = await api('/src/api/reportes.php?action=compartir', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    reporte_id: reporteId,
                    emails: emails
                })
            });

            if (response.success) {
                notificaciones.exito(`Reporte compartido con ${emails.length} destinatarios`);
                return true;
            }
            return false;
        } catch (error) {
            console.error('[COMPARTIR] Error:', error);
            notificaciones.error('Error compartiendo reporte');
            return false;
        }
    }

    /**
     * Programar reporte automático
     */
    async programarReporte(configuracion) {
        try {
            const response = await api('/src/api/reportes.php?action=programar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(configuracion)
            });

            if (response.success) {
                notificaciones.exito('Reporte programado');
                return response.data;
            }
            return null;
        } catch (error) {
            console.error('[PROGRAMAR] Error:', error);
            return null;
        }
    }

    /**
     * Obtener auditoría de cambios
     */
    async obtenerAuditoria(filtros = {}) {
        try {
            let url = '/src/api/auditoria.php?action=list';
            
            Object.entries(filtros).forEach(([key, value]) => {
                if (value) url += `&${key}=${encodeURIComponent(value)}`;
            });

            const response = await api(url);
            return response.success ? response.data : [];
        } catch (error) {
            console.error('[AUDITORIA] Error:', error);
            return [];
        }
    }
}

// Instancia global
const analizador = new AnalizadorReportes();

console.log('[REPORTES-AVANZADOS] Módulo de análisis y reportería cargado');
