/**
 * ClassControl - Utils Global
 * Expone todas las funciones globalmente para acceso desde HTML/PHP
 */

// Prevenir carga duplicada
(function() {
    if (window.__UTILS_LOADED__) {
        console.warn('[UTILS] Ya cargado, skipping duplicado');
        return;
    }
    window.__UTILS_LOADED__ = true;

// ============================================
// API MODULE
// ============================================

function detectApiBase() {
    const override = window.__API_BASE_URL__ || localStorage.getItem('API_BASE_URL');
    if (override) return override.replace(/\/$/, '');
    
    const { origin, pathname, port } = window.location;
    if (port === '5500') return 'http://localhost:8000';
    
    // Detectar carpeta del proyecto en la URL
    // Patrones posibles:
    // /ClassControl/public/index.html -> /ClassControl
    // /GENERADOR-DE-HORARIOS/public/index.html -> /GENERADOR-DE-HORARIOS
    // /public/index.html -> solo origin (desarrollo local)
    
    const pathParts = pathname.split('/').filter(p => p);
    
    // Buscar si hay 'public' en la ruta
    const publicIndex = pathParts.indexOf('public');
    
    if (publicIndex > 0) {
        // Hay carpeta del proyecto antes de 'public'
        // /ClassControl/public/... -> retorna /ClassControl
        return origin + '/' + pathParts[0];
    }
    
    // Si solo está /public/..., retorna origin
    return origin;
}

const API_BASE_URL = detectApiBase();
console.log('[INIT] API Base URL:', API_BASE_URL);

async function api(endpoint, options = {}) {
    const { method = 'GET', body = null, headers = {} } = options;
    const config = { 
        method, 
        headers: { 'Content-Type': 'application/json', ...headers }, 
        credentials: 'include' 
    };
    if (body) config.body = typeof body === 'string' ? body : JSON.stringify(body);
    const url = endpoint.startsWith('http') ? endpoint : `${API_BASE_URL}${endpoint}`;

    console.log('[API]', method, url);
    showLoading(true);
    
    try {
        const resp = await fetch(url, config);
        console.log('[API RESPONSE]', resp.status);
        const text = await resp.text();
        
        let json;
        try {
            json = JSON.parse(text);
        } catch(e) {
            console.error('[API ERROR] Invalid JSON:', text.substring(0, 100));
            throw new Error('Invalid JSON response');
        }
        
        return json;
    } catch (err) {
        console.error('[API ERROR]', err);
        throw err;
    } finally {
        showLoading(false);
    }
}

// ============================================
// UI MODULE
// ============================================

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

function showModal(modalId) { 
    const modal = document.getElementById(modalId); 
    if (modal) { 
        modal.classList.remove('hidden'); 
        document.body.style.overflow = 'hidden'; 
    } 
}

function closeModal(modalId) { 
    const modal = document.getElementById(modalId); 
    if (modal) { 
        modal.classList.add('hidden'); 
        document.body.style.overflow = ''; 
        const form = modal.querySelector('form'); 
        if (form) form.reset(); 
    } 
}

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

// ============================================
// FORMS MODULE
// ============================================

function getFormData(form) {
    const el = typeof form === 'string' ? document.getElementById(form) : form;
    if (!el) return {};
    const fd = new FormData(el);
    const data = {};
    for (const [key, value] of fd.entries()) {
        if (key.endsWith('[]')) { 
            const k = key.slice(0, -2); 
            if (!Array.isArray(data[k])) data[k] = []; 
            data[k].push(value); 
        }
        else data[key] = value;
    }
    return data;
}

function validateEmail(email) { 
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; 
    return re.test(String(email).toLowerCase()); 
}

function validatePassword(password) { 
    const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/; 
    return re.test(password); 
}

function saveCredentials(email, password) {
    localStorage.setItem('cc_remembered_email', email);
    localStorage.setItem('cc_remembered_password', btoa(password));
}

function loadCredentials() {
    const email = localStorage.getItem('cc_remembered_email');
    const password = localStorage.getItem('cc_remembered_password');
    if (email && password) {
        return { email, password: atob(password) };
    }
    return null;
}

function clearCredentials() {
    localStorage.removeItem('cc_remembered_email');
    localStorage.removeItem('cc_remembered_password');
}

// ============================================
// AUTH MODULE
// ============================================

async function handleLogin(e) {
    e.preventDefault();
    console.log('[LOGIN] Starting...');
    
    const btn = document.getElementById('btn-login');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = '⏳ Procesando...';
    
    const formData = getFormData(e.target);
    const rememberMe = document.getElementById('remember')?.checked || false;
    
    try {
        const response = await api('/src/api/auth/login.php', {
            method: 'POST',
            body: formData
        });

        if (response && response.success) {
            if (rememberMe) {
                saveCredentials(formData.email, formData.password);
            } else {
                clearCredentials();
            }
            
            showAlert('✅ ¡Bienvenido ' + (response.nombre || 'Usuario') + '!', 'success', 1000);
            
            // Usar redirect del servidor (dinámico)
            const redirectUrl = response.redirect || window.location.origin + '/public/admin/dashboard.php';
            setTimeout(() => {
                window.location.href = redirectUrl;
            }, 1000);
        } else {
            const errorMsg = response?.message || 'Error desconocido';
            showAlert('❌ ' + errorMsg, 'danger', 3000);
            btn.disabled = false;
            btn.textContent = originalText;
        }
    } catch (error) {
        console.error('[LOGIN] Error:', error);
        showAlert('❌ Error: ' + error.message, 'danger', 3000);
        btn.disabled = false;
        btn.textContent = originalText;
    }
}

function loadSavedCredentials() {
    const saved = loadCredentials();
    if (saved) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const rememberCheckbox = document.getElementById('remember');
        
        if (emailInput) emailInput.value = saved.email;
        if (passwordInput) passwordInput.value = saved.password;
        if (rememberCheckbox) rememberCheckbox.checked = true;
    }
}

// ============================================
// HELPERS MODULE
// ============================================

function formatDate(d) {
    if (!d) return '-';
    const date = (typeof d === 'string') ? new Date(d) : d;
    return date.toLocaleDateString('es-CO', { year: 'numeric', month: 'long', day: 'numeric' });
}

function formatTime(t) {
    if (!t) return '-';
    return String(t).substring(0, 5);
}

function formatCurrency(v) {
    return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP' }).format(v);
}

function capitalize(s) {
    if (!s) return '';
    s = String(s);
    return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();
}

function clearForm(formId) {
    const f = document.getElementById(formId);
    if (f) f.reset();
}

function disableForm(formId, disabled = true) {
    const f = document.getElementById(formId);
    if (!f) return;
    f.querySelectorAll('input,select,textarea,button').forEach(i => i.disabled = disabled);
}

function getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const out = {};
    for (const [k, v] of params.entries()) out[k] = v;
    return out;
}

function redirect(url) {
    console.log('[REDIRECT]', url);
    window.location.href = url;
}

// ============================================
// AUTO INIT
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('[INIT] Utils loaded');
    const emailField = document.getElementById('email');
    if (emailField) emailField.focus();
    const yearEl = document.getElementById('current-year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();
});

// ============================================
// EXPORTS (for modules if needed)
// ============================================

window.Utils = {
    api,
    API_BASE_URL,
    showAlert,
    showModal,
    closeModal,
    showLoading,
    getFormData,
    validateEmail,
    validatePassword,
    saveCredentials,
    loadCredentials,
    clearCredentials,
    handleLogin,
    loadSavedCredentials,
    formatDate,
    formatTime,
    formatCurrency,
    capitalize,
    clearForm,
    disableForm,
    getUrlParams,
    redirect
};

console.log('[UTILS] All functions available globally');

})(); // Fin del IIFE protector

