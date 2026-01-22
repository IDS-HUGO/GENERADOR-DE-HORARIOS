/**
 * Módulo de Gestión Avanzada de Usuarios
 * Control de roles, permisos, y administración de usuarios
 */

class GestorUsuariosAvanzados {
    constructor() {
        this.usuarioActual = null;
        this.usuariosCacheados = [];
        this.rolesDisponibles = ['admin', 'docente', 'coordinador', 'asistente'];
    }

    /**
     * Cargar usuario actual
     */
    async cargarUsuarioActual() {
        try {
            const response = await api('/src/api/usuarios.php?action=current');
            
            if (response.success) {
                this.usuarioActual = response.data;
                console.log('[USUARIOS] Usuario actual:', this.usuarioActual.nombre);
                return this.usuarioActual;
            }
            return null;
        } catch (error) {
            console.error('[USUARIOS] Error cargando usuario actual:', error);
            return null;
        }
    }

    /**
     * Verificar permisos del usuario
     */
    tienePermiso(permiso) {
        if (!this.usuarioActual) return false;
        
        const permisosPorRol = {
            'admin': ['crear', 'editar', 'eliminar', 'reportes', 'configurar'],
            'coordinador': ['crear', 'editar', 'reportes'],
            'docente': ['ver', 'editar_propio'],
            'asistente': ['ver']
        };

        const permisos = permisosPorRol[this.usuarioActual.tipo] || [];
        return permisos.includes(permiso);
    }

    /**
     * Crear nuevo usuario
     */
    async crearUsuario(datos) {
        if (!this.tienePermiso('crear')) {
            notificaciones.error('No tienes permiso para crear usuarios');
            return null;
        }

        try {
            const response = await api('/src/api/usuarios.php?action=create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });

            if (response.success) {
                notificaciones.exito(`Usuario creado: ${datos.email}`);
                return response.data;
            } else {
                notificaciones.error(response.message);
                return null;
            }
        } catch (error) {
            console.error('[USUARIOS] Error creando usuario:', error);
            notificaciones.error('Error creando usuario');
            return null;
        }
    }

    /**
     * Editar usuario
     */
    async editarUsuario(usuarioId, datos) {
        if (!this.tienePermiso('editar')) {
            notificaciones.error('No tienes permiso para editar usuarios');
            return false;
        }

        try {
            const response = await api('/src/api/usuarios.php?action=update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: usuarioId, ...datos })
            });

            if (response.success) {
                notificaciones.exito('Usuario actualizado');
                return true;
            } else {
                notificaciones.error(response.message);
                return false;
            }
        } catch (error) {
            console.error('[USUARIOS] Error editando usuario:', error);
            notificaciones.error('Error actualizando usuario');
            return false;
        }
    }

    /**
     * Eliminar usuario (soft delete)
     */
    async desactivarUsuario(usuarioId) {
        if (!this.tienePermiso('eliminar')) {
            notificaciones.error('No tienes permiso para eliminar usuarios');
            return false;
        }

        try {
            const response = await api(
                `/src/api/usuarios.php?action=delete&id=${usuarioId}`,
                { method: 'DELETE' }
            );

            if (response.success) {
                notificaciones.exito('Usuario desactivado');
                return true;
            }
            return false;
        } catch (error) {
            console.error('[USUARIOS] Error desactivando usuario:', error);
            return false;
        }
    }

    /**
     * Listar todos los usuarios (con caché)
     */
    async listarUsuarios(filtro = {}) {
        try {
            let url = '/src/api/usuarios.php?action=list';
            
            if (filtro.tipo) url += `&tipo=${filtro.tipo}`;
            if (filtro.estado) url += `&estado=${filtro.estado}`;

            const response = await api(url);

            if (response.success) {
                this.usuariosCacheados = response.data;
                return response.data;
            }
            return [];
        } catch (error) {
            console.error('[USUARIOS] Error listando usuarios:', error);
            return this.usuariosCacheados; // Retornar caché en caso de error
        }
    }

    /**
     * Buscar usuario por email
     */
    async buscarPorEmail(email) {
        try {
            const response = await api(
                `/src/api/usuarios.php?action=search&email=${encodeURIComponent(email)}`
            );

            if (response.success) {
                return response.data;
            }
            return null;
        } catch (error) {
            console.error('[USUARIOS] Error buscando:', error);
            return null;
        }
    }

    /**
     * Cambiar rol de usuario
     */
    async cambiarRol(usuarioId, nuevoRol) {
        if (!this.tienePermiso('editar')) {
            notificaciones.error('No tienes permiso para cambiar roles');
            return false;
        }

        if (!this.rolesDisponibles.includes(nuevoRol)) {
            notificaciones.error(`Rol inválido: ${nuevoRol}`);
            return false;
        }

        return await this.editarUsuario(usuarioId, { tipo: nuevoRol });
    }

    /**
     * Resetear contraseña de usuario
     */
    async resetearContraseña(usuarioId) {
        if (!this.tienePermiso('editar')) {
            notificaciones.error('No tienes permiso para resetear contraseñas');
            return false;
        }

        try {
            const response = await api('/src/api/usuarios.php?action=reset_password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: usuarioId })
            });

            if (response.success) {
                notificaciones.exito(`Contraseña reseteada. Nueva: ${response.data.temporal_password}`);
                return response.data;
            }
            return null;
        } catch (error) {
            console.error('[USUARIOS] Error reseteando:', error);
            return null;
        }
    }

    /**
     * Obtener estadísticas de usuarios
     */
    async obtenerEstadísticas() {
        try {
            const response = await api('/src/api/estadisticas.php?action=usuarios');

            if (response.success) {
                return response.data;
            }
            return null;
        } catch (error) {
            console.error('[USUARIOS] Error obteniendo stats:', error);
            return null;
        }
    }

    /**
     * Exportar lista de usuarios a CSV
     */
    async exportarCSV(filtro = {}) {
        try {
            const usuarios = await this.listarUsuarios(filtro);
            
            if (usuarios.length === 0) {
                notificaciones.advertencia('No hay usuarios para exportar');
                return false;
            }

            // Crear CSV
            let csv = 'ID,Email,Nombre,Tipo,Estado,Fecha Creación\n';
            usuarios.forEach(u => {
                csv += `${u.id},"${u.email}","${u.nombre || ''}",${u.tipo},${u.estado},${u.created_at}\n`;
            });

            // Descargar
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `usuarios_${new Date().getTime()}.csv`;
            a.click();

            notificaciones.exito('CSV descargado');
            return true;
        } catch (error) {
            console.error('[USUARIOS] Error exportando:', error);
            notificaciones.error('Error descargando CSV');
            return false;
        }
    }

    /**
     * Validar datos de usuario
     */
    validarDatos(datos) {
        const errores = [];

        if (!datos.email || !datos.email.includes('@')) {
            errores.push('Email inválido');
        }

        if (!datos.nombre || datos.nombre.length < 2) {
            errores.push('Nombre debe tener al menos 2 caracteres');
        }

        if (datos.tipo && !this.rolesDisponibles.includes(datos.tipo)) {
            errores.push('Rol inválido');
        }

        return {
            válido: errores.length === 0,
            errores: errores
        };
    }
}

// Instancia global
const gestorUsuarios = new GestorUsuariosAvanzados();

console.log('[USUARIOS-AVANZADOS] Módulo de gestión avanzada de usuarios cargado');
