/**
 * ClassControl - Estadísticas y Análisis
 */

class EstadisticasManager {
    constructor() {
        this.datos = {};
    }
    
    // Cargar todas las estadísticas
    async cargarTodas() {
        try {
            this.datos = {
                docentes: (await api('/src/api/docentes.php?action=list')).data || [],
                materias: (await api('/src/api/materias.php?action=list')).data || [],
                programas: (await api('/src/api/programas.php?action=list')).data || [],
                grupos: (await api('/src/api/grupos.php?action=list')).data || []
            };
            return true;
        } catch (e) {
            console.error('[STATS]', e);
            return false;
        }
    }
    
    // Estadísticas de docentes
    obtenerEstadisticasDocentes() {
        const docentes = this.datos.docentes || [];
        return {
            total: docentes.length,
            activos: docentes.filter(d => d.estado === 'activo').length,
            inactivos: docentes.filter(d => d.estado === 'inactivo').length,
            porDepartamento: this.agruparPor(docentes, 'departamento'),
            promedio: {
                horasAsignadas: this.calcularPromedioHoras(docentes)
            }
        };
    }
    
    // Estadísticas de materias
    obtenerEstadisticasMaterias() {
        const materias = this.datos.materias || [];
        return {
            total: materias.length,
            porPrograma: this.agruparPor(materias, 'programa_id'),
            creditoTotal: materias.reduce((sum, m) => sum + (m.creditos || 0), 0),
            promedioCreditosPorMateria: materias.length > 0 ? 
                materias.reduce((sum, m) => sum + (m.creditos || 0), 0) / materias.length : 0
        };
    }
    
    // Estadísticas de grupos
    obtenerEstadisticasGrupos() {
        const grupos = this.datos.grupos || [];
        return {
            total: grupos.length,
            porPrograma: this.agruparPor(grupos, 'programa_id'),
            promediaEstudiantes: grupos.length > 0 ?
                grupos.reduce((sum, g) => sum + (g.estudiantes || 0), 0) / grupos.length : 0,
            estudiantesTotal: grupos.reduce((sum, g) => sum + (g.estudiantes || 0), 0)
        };
    }
    
    // Conflictos de horarios (si existen)
    detectarConflictosHorarios(horarios) {
        const conflictos = [];
        
        for (let i = 0; i < horarios.length; i++) {
            for (let j = i + 1; j < horarios.length; j++) {
                const h1 = horarios[i];
                const h2 = horarios[j];
                
                // Mismo docente, mismo día, mismo horario
                if (h1.docente_id === h2.docente_id && 
                    h1.dia === h2.dia &&
                    this.haya_solapamiento(h1.hora_inicio, h1.hora_fin, h2.hora_inicio, h2.hora_fin)) {
                    conflictos.push({
                        tipo: 'docente',
                        docente_id: h1.docente_id,
                        horarios: [h1, h2]
                    });
                }
                
                // Mismo grupo, mismo día, mismo horario
                if (h1.grupo_id === h2.grupo_id && 
                    h1.dia === h2.dia &&
                    this.haya_solapamiento(h1.hora_inicio, h1.hora_fin, h2.hora_inicio, h2.hora_fin)) {
                    conflictos.push({
                        tipo: 'grupo',
                        grupo_id: h1.grupo_id,
                        horarios: [h1, h2]
                    });
                }
            }
        }
        
        return conflictos;
    }
    
    // Verificar si hay solapamiento de horarios
    haya_solapamiento(inicio1, fin1, inicio2, fin2) {
        const h1i = parseInt(inicio1.split(':')[0]);
        const h1f = parseInt(fin1.split(':')[0]);
        const h2i = parseInt(inicio2.split(':')[0]);
        const h2f = parseInt(fin2.split(':')[0]);
        
        return (h1i < h2f) && (h2i < h1f);
    }
    
    // Agrupar datos
    agruparPor(datos, propiedad) {
        return datos.reduce((acc, item) => {
            const valor = item[propiedad];
            acc[valor] = (acc[valor] || 0) + 1;
            return acc;
        }, {});
    }
    
    // Calcular promedio de horas
    calcularPromedioHoras(docentes) {
        if (docentes.length === 0) return 0;
        const total = docentes.reduce((sum, d) => sum + (d.horas_totales || 0), 0);
        return (total / docentes.length).toFixed(1);
    }
    
    // Generar reporte de utilización
    generarReporteUtilizacion() {
        const stats = {
            docentes: this.obtenerEstadisticasDocentes(),
            materias: this.obtenerEstadisticasMaterias(),
            grupos: this.obtenerEstadisticasGrupos(),
            generadoEn: new Date().toLocaleString()
        };
        return stats;
    }
    
    // Gráficos (datos listos para Chart.js)
    obtenerDatosGrafico(tipo) {
        const stats = this.obtenerEstadisticasDocentes();
        
        if (tipo === 'docentes_por_departamento') {
            return {
                labels: Object.keys(stats.porDepartamento),
                datasets: [{
                    label: 'Docentes',
                    data: Object.values(stats.porDepartamento),
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ]
                }]
            };
        }
        
        if (tipo === 'estado_docentes') {
            return {
                labels: ['Activos', 'Inactivos'],
                datasets: [{
                    data: [stats.activos, stats.inactivos],
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            };
        }
        
        return null;
    }
}

// Instancia global
const estadisticas = new EstadisticasManager();

console.log('[ESTADISTICAS] Módulo de estadísticas cargado');
