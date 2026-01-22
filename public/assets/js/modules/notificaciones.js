/**
 * ClassControl - Sistema de Notificaciones
 */

class NotificacionesManager {
    constructor() {
        this.notificaciones = [];
        this.container = null;
        this.init();
    }
    
    init() {
        // Crear contenedor de notificaciones
        this.container = document.createElement('div');
        this.container.id = 'notificaciones-container';
        this.container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(this.container);
    }
    
    // Crear notificación
    crear(titulo, mensaje, tipo = 'info', duracion = 5000) {
        const id = Date.now();
        const notif = {
            id,
            titulo,
            mensaje,
            tipo,
            timestamp: new Date()
        };
        
        this.notificaciones.push(notif);
        this.renderizar(notif);
        
        if (duracion > 0) {
            setTimeout(() => this.cerrar(id), duracion);
        }
        
        return id;
    }
    
    renderizar(notif) {
        const colores = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#4a90e2'
        };
        
        const el = document.createElement('div');
        el.id = `notif-${notif.id}`;
        el.style.cssText = `
            background: ${colores[notif.tipo] || colores.info};
            color: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease-out;
        `;
        
        const iconos = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        
        el.innerHTML = `
            <div style="font-weight: bold; margin-bottom: 5px;">
                ${iconos[notif.tipo]} ${notif.titulo}
            </div>
            <div style="font-size: 0.9em;">
                ${notif.mensaje}
            </div>
        `;
        
        this.container.appendChild(el);
    }
    
    cerrar(id) {
        const el = document.getElementById(`notif-${id}`);
        if (el) {
            el.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => {
                el.remove();
                this.notificaciones = this.notificaciones.filter(n => n.id !== id);
            }, 300);
        }
    }
    
    // Métodos abreviados
    exito(titulo, mensaje = '', duracion = 3000) {
        return this.crear(titulo, mensaje, 'success', duracion);
    }
    
    error(titulo, mensaje = '', duracion = 5000) {
        return this.crear(titulo, mensaje, 'error', duracion);
    }
    
    advertencia(titulo, mensaje = '', duracion = 4000) {
        return this.crear(titulo, mensaje, 'warning', duracion);
    }
    
    info(titulo, mensaje = '', duracion = 3000) {
        return this.crear(titulo, mensaje, 'info', duracion);
    }
    
    limpiar() {
        this.notificaciones.forEach(n => this.cerrar(n.id));
    }
}

// Instancia global
const notificaciones = new NotificacionesManager();

// Agregar estilos de animación
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

console.log('[NOTIFICACIONES] Sistema de notificaciones cargado');
