/**
 * ClassControl - Búsqueda Avanzada y Filtros
 */

class BuscadorAvanzado {
    constructor() {
        this.resultados = [];
        this.filtrosActivos = {};
    }
    
    // Búsqueda global en todo el sistema
    async buscarGlobal(termino) {
        if (!termino || termino.length < 2) {
            return [];
        }
        
        const search = termino.toLowerCase();
        let resultados = [];
        
        try {
            // Buscar en docentes
            const docentes = await api('/src/api/docentes.php?action=list');
            if (docentes.success) {
                docentes.data.forEach(d => {
                    if (d.nombre.toLowerCase().includes(search) || 
                        d.email.toLowerCase().includes(search)) {
                        resultados.push({
                            tipo: 'Docente',
                            titulo: d.nombre,
                            descripcion: d.email,
                            id: d.id,
                            icon: '👨‍🏫'
                        });
                    }
                });
            }
            
            // Buscar en materias
            const materias = await api('/src/api/materias.php?action=list');
            if (materias.success) {
                materias.data.forEach(m => {
                    if (m.nombre.toLowerCase().includes(search) || 
                        m.codigo.toLowerCase().includes(search)) {
                        resultados.push({
                            tipo: 'Materia',
                            titulo: m.nombre,
                            descripcion: `Código: ${m.codigo}`,
                            id: m.id,
                            icon: '📚'
                        });
                    }
                });
            }
            
            // Buscar en programas
            const programas = await api('/src/api/programas.php?action=list');
            if (programas.success) {
                programas.data.forEach(p => {
                    if (p.nombre.toLowerCase().includes(search)) {
                        resultados.push({
                            tipo: 'Programa',
                            titulo: p.nombre,
                            descripcion: `Código: ${p.codigo}`,
                            id: p.id,
                            icon: '🎓'
                        });
                    }
                });
            }
            
            // Buscar en grupos
            const grupos = await api('/src/api/grupos.php?action=list');
            if (grupos.success) {
                grupos.data.forEach(g => {
                    if (g.nombre.toLowerCase().includes(search) || 
                        g.codigo.toLowerCase().includes(search)) {
                        resultados.push({
                            tipo: 'Grupo',
                            titulo: g.nombre,
                            descripcion: `Código: ${g.codigo}`,
                            id: g.id,
                            icon: '👥'
                        });
                    }
                });
            }
        } catch (e) {
            console.error('[BUSQUEDA]', e);
        }
        
        this.resultados = resultados;
        return resultados;
    }
    
    // Filtrar docentes
    filtrarDocentes(docentes, filtros) {
        return docentes.filter(d => {
            if (filtros.nombre && !d.nombre.toLowerCase().includes(filtros.nombre.toLowerCase())) {
                return false;
            }
            if (filtros.departamento && d.departamento !== filtros.departamento) {
                return false;
            }
            if (filtros.estado && d.estado !== filtros.estado) {
                return false;
            }
            return true;
        });
    }
    
    // Filtrar materias
    filtrarMaterias(materias, filtros) {
        return materias.filter(m => {
            if (filtros.nombre && !m.nombre.toLowerCase().includes(filtros.nombre.toLowerCase())) {
                return false;
            }
            if (filtros.programa && m.programa_id !== filtros.programa) {
                return false;
            }
            if (filtros.creditos) {
                if (filtros.creditos.min && m.creditos < filtros.creditos.min) return false;
                if (filtros.creditos.max && m.creditos > filtros.creditos.max) return false;
            }
            return true;
        });
    }
    
    // Filtrar horarios
    filtrarHorarios(horarios, filtros) {
        return horarios.filter(h => {
            if (filtros.dia && h.dia !== filtros.dia) return false;
            if (filtros.docente && h.docente_id !== filtros.docente) return false;
            if (filtros.grupo && h.grupo_id !== filtros.grupo) return false;
            if (filtros.materia && h.materia_id !== filtros.materia) return false;
            
            if (filtros.horaInicio) {
                const hora = parseInt(h.hora_inicio.split(':')[0]);
                if (hora < filtros.horaInicio) return false;
            }
            
            return true;
        });
    }
    
    // Ordenar resultados
    ordenar(datos, campo, direccion = 'asc') {
        const sorted = [...datos];
        sorted.sort((a, b) => {
            let valA = a[campo];
            let valB = b[campo];
            
            if (typeof valA === 'string') {
                valA = valA.toLowerCase();
                valB = valB.toLowerCase();
            }
            
            if (direccion === 'asc') {
                return valA > valB ? 1 : -1;
            } else {
                return valA < valB ? 1 : -1;
            }
        });
        return sorted;
    }
}

// Instancia global
const buscador = new BuscadorAvanzado();

console.log('[BUSQUEDA] Módulo de búsqueda avanzada cargado');
