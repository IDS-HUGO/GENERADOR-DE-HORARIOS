/**
 * ClassControl - Utilidades JavaScript (limpio y consolidado)
 * Sistema de gestión de horarios
 */

const DEBUG_MODE = false;

function detectApiBase() {
    const override = window.__API_BASE_URL__ || localStorage.getItem('API_BASE_URL');
    if (override) return override.replace(/\/$/, '');

    const { origin, pathname, port } = window.location;
    // If running from VS Code Live Server (static), hit PHP dev server
    if (port === '5500') return 'http://localhost:8000';
    // If hosted under specific subfolders on Apache
    if (pathname.startsWith('/ClassControl_LocalHost')) return origin + '/ClassControl_LocalHost';
    if (pathname.startsWith('/ClassControl')) return origin + '/ClassControl';
    // Default: same origin
    return origin;
}

const API_BASE_URL = detectApiBase();

/**
 * Petición a la API (JSON)
 */
async function api(endpoint, options = {}) {
    const { method = 'GET', body = null, headers = {} } = options;
    const config = { method, headers: { 'Content-Type': 'application/json', ...headers }, credentials: 'include' };
    if (body) config.body = typeof body === 'string' ? body : JSON.stringify(body);
    const url = endpoint.startsWith('http') ? endpoint : `${API_BASE_URL}${endpoint}`;

    try {
        showLoading(true);
        const resp = await fetch(url, config);
        if (!resp.ok) {
            if (resp.status === 401) { window.location.href = `${API_BASE_URL}/public/index.html`; return { success: false, message: 'No autorizado' }; }
            const text = await resp.text();
            throw new Error(`HTTP ${resp.status} - ${text}`);
        }
        return await resp.json();
    } catch (err) {
        console.error('API error:', err);
        throw err;
    } finally {
        showLoading(false);
    }
}

/** Alertas */
function showAlert(message, type = 'info', timeout = 5000) {
    const alertHTML = `<div class="alert alert-${type}"><span class="alert-icon">${getAlertIcon(type)}</span><div style="flex:1">${message}</div><button class="alert-close" onclick="this.parentElement.remove()">&times;</button></div>`;
    let container = document.getElementById('alert-container');
    if (!container) { container = document.createElement('div'); container.id = 'alert-container'; container.style.cssText = 'position:fixed; top:80px; right:20px; z-index:9999; max-width:420px;'; document.body.appendChild(container); }
    container.insertAdjacentHTML('beforeend', alertHTML);
    const nodes = container.querySelectorAll('.alert');
    const el = nodes[nodes.length - 1];
    if (timeout > 0) setTimeout(() => el && el.remove(), timeout);
}

function getAlertIcon(type) { const icons = { success: '✓', danger: '✕', warning: '⚠', info: 'ℹ' }; return icons[type] || 'ℹ'; }

/** Modales */
function showModal(modalId) { const modal = document.getElementById(modalId); if (modal) { modal.classList.add('active'); document.body.style.overflow = 'hidden'; } }
function closeModal(modalId) { const modal = document.getElementById(modalId); if (modal) { modal.classList.remove('active'); document.body.style.overflow = ''; const form = modal.querySelector('form'); if (form) form.reset(); } }
document.addEventListener('click', (e) => { if (e.target.classList && e.target.classList.contains('modal-overlay')) closeModal(e.target.id); });

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

/** Formularios */
function getFormData(form) {
    const el = typeof form === 'string' ? document.getElementById(form) : form;
    if (!el) return {};
    const fd = new FormData(el);
    const data = {};
    for (const [key, value] of fd.entries()) {
        if (key.endsWith('[]')) { const k = key.slice(0, -2); if (!Array.isArray(data[k])) data[k] = []; data[k].push(value); }
        else data[key] = value;
    }
    return data;
}

function clearForm(formId) { const f = document.getElementById(formId); if (f) f.reset(); }
function disableForm(formId, disabled = true) { const f = document.getElementById(formId); if (!f) return; f.querySelectorAll('input,select,textarea,button').forEach(i => i.disabled = disabled); }

function validateEmail(email) { const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; return re.test(String(email).toLowerCase()); }
function validatePassword(password) { const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/; return re.test(password); }

function formatDate(d) { if (!d) return '-'; const date = (typeof d === 'string') ? new Date(d) : d; return date.toLocaleDateString('es-CO', { year: 'numeric', month: 'long', day: 'numeric' }); }
function formatTime(t) { if (!t) return '-'; return String(t).substring(0,5); }
function formatCurrency(v) { return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP' }).format(v); }
function capitalize(s) { if (!s) return ''; s = String(s); return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase(); }

function getUrlParams() { const params = new URLSearchParams(window.location.search); const out = {}; for (const [k,v] of params.entries()) out[k]=v; return out; }
function redirect(url) { window.location.href = url; }
async function loadTemplate(url, containerId) { try { const r = await fetch(url); const html = await r.text(); const c = document.getElementById(containerId); if (c) c.innerHTML = html; } catch (e) { console.error('Error al cargar template:', e); } }

function generateTable(data, columns) { let html = '<table class="table"><thead><tr>'; columns.forEach(c => html += `<th>${c.label}</th>`); html += '</tr></thead><tbody>'; data.forEach(row => { html += '<tr>'; columns.forEach(col => { const value = col.key ? row[col.key] : ''; const formatted = col.format ? col.format(value) : value; html += `<td>${formatted}</td>`; }); html += '</tr>'; }); html += '</tbody></table>'; return html; }

function exportToCSV(tableId, filename) { const table = document.getElementById(tableId); if (!table) return; const csv = []; const headers = []; table.querySelectorAll('th').forEach(th => headers.push(th.textContent.trim())); csv.push(headers.join(',')); table.querySelectorAll('tbody tr').forEach(tr => { const row = []; tr.querySelectorAll('td').forEach(td => row.push(`"${td.textContent.replace(/"/g,'""')}"`)); csv.push(row.join(',')); }); const blob = new Blob([csv.join('\n')], { type: 'text/csv' }); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = filename || 'export.csv'; a.click(); }

function parseApiResponse(resp) { if (!resp) return null; if (resp.success) { if (resp.message) showAlert(resp.message, 'success'); return resp.data || null; } if (resp.message) showAlert(resp.message, 'danger'); return null; }

function debugLog(msg, data=null) { if (DEBUG_MODE) console.log('[DEBUG]', msg, data); }

/* Inicialización global */
document.addEventListener('DOMContentLoaded', () => {
    const email = document.getElementById('email'); if (email) email.focus();
    const y = document.getElementById('current-year'); if (y) y.textContent = new Date().getFullYear();
});

