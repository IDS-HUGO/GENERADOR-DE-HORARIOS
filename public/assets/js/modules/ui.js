/**
 * UI Module - Componentes visuales
 */

/** Alertas */
function showAlert(message, type = 'info', timeout = 5000) {
    console.log('[ALERT]', type, message);
    const alertHTML = `<div class="alert alert-${type}"><span class="alert-icon">${getAlertIcon(type)}</span><div style="flex:1">${message}</div><button class="alert-close" onclick="this.parentElement.remove()">&times;</button></div>`;
    let container = document.getElementById('alert-container');
    if (!container) { 
        container = document.createElement('div'); 
        container.id = 'alert-container'; 
        container.style.cssText = 'position:fixed; top:80px; right:20px; z-index:9999; max-width:420px;'; 
        document.body.appendChild(container); 
    }
    container.insertAdjacentHTML('beforeend', alertHTML);
    const nodes = container.querySelectorAll('.alert');
    const el = nodes[nodes.length - 1];
    if (timeout > 0) setTimeout(() => el && el.remove(), timeout);
}

function getAlertIcon(type) { 
    const icons = { success: '✓', danger: '✕', warning: '⚠', info: 'ℹ' }; 
    return icons[type] || 'ℹ'; 
}

/** Modales */
function showModal(modalId) { 
    const modal = document.getElementById(modalId); 
    if (modal) { 
        modal.classList.add('active'); 
        document.body.style.overflow = 'hidden'; 
    } 
}

function closeModal(modalId) { 
    const modal = document.getElementById(modalId); 
    if (modal) { 
        modal.classList.remove('active'); 
        document.body.style.overflow = ''; 
        const form = modal.querySelector('form'); 
        if (form) form.reset(); 
    } 
}

document.addEventListener('click', (e) => { 
    if (e.target.classList && e.target.classList.contains('modal-overlay')) 
        closeModal(e.target.id); 
});

/** Loading overlay */
function showLoading(show = true) {
    let overlay = document.getElementById('loading-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.style.cssText = 'position:fixed; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.35); z-index:9998;';
        overlay.innerHTML = '<div class="spinner" style="width:60px;height:60px;border-width:6px"></div>';
        document.body.appendChild(overlay);
    }
    overlay.style.display = show ? 'flex' : 'none';
}

// Exportar
window.UI = { showAlert, showModal, closeModal, showLoading };
