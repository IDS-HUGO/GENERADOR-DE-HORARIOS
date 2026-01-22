/**
 * Módulo de Gestión Avanzada de Horarios
 * Funciones para crear, editar, validar y optimizar horarios
 */

class GestorHorariosAvanzados {
    constructor() {
        this.horarios = [];
        this.conflictos = [];
        this.sugerencias = [];
    }

    /**
     * Crear horario nuevo
     */
    async crearHorario(data) {
        try {
            const response = await api('/src/api/horarios.php?action=create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (response.success) {
                notificaciones.exito(`Horario creado: ${data.nombre}`);
                return response.data;
            } else {
                notificaciones.error(`Error: ${response.message}`);
                return null;
            }
        } catch (error) {
            console.error('[HORARIOS] Error creando:', error);
            notificaciones.error('Error creando horario');
            return null;
        }
    }

    /**
     * Detectar conflictos en horarios
     */
    async detectarConflictos(grupoId, materiaId) {
        try {
            const response = await api(
                `/src/api/conflictos.php?action=detectar&grupo_id=${grupoId}&materia_id=${materiaId}`
            );

            if (response.success) {
                this.conflictos = response.data || [];
                if (this.conflictos.length > 0) {
                    notificaciones.advertencia(`Se encontraron ${this.conflictos.length} conflictos`);
                }
                return this.conflictos;
            }
            return [];
        } catch (error) {
            console.error('[CONFLICTOS] Error:', error);
            return [];
        }
    }

    /**
     * Obtener sugerencias automáticas
     */
    async obtenerSugerencias(grupoId) {
        try {
            const response = await api(
                `/src/api/sugerencias.php?action=generar&grupo_id=${grupoId}`
            );

            if (response.success) {
                this.sugerencias = response.data || [];
                notificaciones.info(`${this.sugerencias.length} sugerencias disponibles`);
                return this.sugerencias;
            }
            return [];
        } catch (error) {
            console.error('[SUGERENCIAS] Error:', error);
            return [];
        }
    }

    /**
     * Aplicar sugerencia seleccionada
     */
    async aplicarSugerencia(sugerenciaId) {
        const sugerencia = this.sugerencias.find(s => s.id === sugerenciaId);
        if (!sugerencia) {
            notificaciones.error('Sugerencia no encontrada');
            return false;
        }

        return await this.crearHorario(sugerencia);
    }

    /**
     * Validar horario contra conflictos
     */
    async validarHorario(horarioId) {
        try {
            const response = await api(
                `/src/api/sugerencias.php?action=validar&horario_id=${horarioId}`
            );

            if (response.success) {
                notificaciones.exito('Horario válido, sin conflictos');
                return true;
            } else {
                notificaciones.advertencia(response.message);
                return false;
            }
        } catch (error) {
            console.error('[VALIDAR] Error:', error);
            return false;
        }
    }

    /**
     * Generar horario óptimo automático
     */
    async generarOptimo(grupoId) {
        try {
            const response = await api(
                `/src/api/sugerencias.php?action=optimo&grupo_id=${grupoId}`
            );

            if (response.success && response.data) {
                notificaciones.exito('Horario óptimo generado');
                return response.data;
            } else {
                notificaciones.advertencia('No se pudo generar horario óptimo');
                return null;
            }
        } catch (error) {
            console.error('[OPTIMO] Error:', error);
            return null;
        }
    }

    /**
     * Obtener disponibilidad de docentes
     */
    async obtenerDisponibilidad(docenteId) {
        try {
            const response = await api(
                `/src/api/disponibilidad.php?action=list&docente_id=${docenteId}`
            );

            if (response.success) {
                return response.data || [];
            }
            return [];
        } catch (error) {
            console.error('[DISPONIBILIDAD] Error:', error);
            return [];
        }
    }

    /**
     * Actualizar disponibilidad de docente
     */
    async actualizarDisponibilidad(docenteId, horario, disponible) {
        try {
            const response = await api('/src/api/disponibilidad.php?action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    docente_id: docenteId,
                    horario: horario,
                    disponible: disponible
                })
            });

            if (response.success) {
                notificaciones.exito('Disponibilidad actualizada');
                return true;
            }
            return false;
        } catch (error) {
            console.error('[DISPONIBILIDAD UPDATE] Error:', error);
            return false;
        }
    }

    /**
     * Exportar horario a PDF
     */
    async exportarPDF(grupoId, nombreArchivo = 'horario.pdf') {
        try {
            notificaciones.info('Generando PDF...');
            const response = await api(
                `/src/api/reportes.php?action=exportar_pdf&grupo_id=${grupoId}`
            );

            if (response.success) {
                // Crear descarga
                const link = document.createElement('a');
                link.href = 'data:application/pdf;base64,' + response.data;
                link.download = nombreArchivo;
                link.click();
                notificaciones.exito('PDF descargado');
                return true;
            }
            return false;
        } catch (error) {
            console.error('[PDF] Error:', error);
            notificaciones.error('Error descargando PDF');
            return false;
        }
    }

    /**
     * Duplicar horario existente
     */
    async duplicarHorario(horarioOriginal, nuevoNombre) {
        const horarioDuplicado = { ...horarioOriginal };
        horarioDuplicado.nombre = nuevoNombre;
        delete horarioDuplicado.id;

        return await this.crearHorario(horarioDuplicado);
    }

    /**
     * Obtener estadísticas de uso de horarios
     */
    async obtenerEstadísticas() {
        try {
            const response = await api('/src/api/estadisticas.php?action=horarios');

            if (response.success) {
                return response.data;
            }
            return null;
        } catch (error) {
            console.error('[STATS] Error:', error);
            return null;
        }
    }
}

// Instancia global
const gestorHorarios = new GestorHorariosAvanzados();

console.log('[HORARIOS-AVANZADOS] Módulo de gestión avanzada de horarios cargado');
