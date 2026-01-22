/**
 * ClassControl - App Initializer
 * Carga utils.js automáticamente en todos los archivos
 */

(function() {
    // Asegurar que utils.js esté cargado SOLO UNA VEZ
    if (window.Utils || window.__UTILS_LOADING__) {
        console.log('[APP] Utils ya cargado o cargando, skipping...');
        return;
    }
    
    window.__UTILS_LOADING__ = true;
    
    console.error('[APP] Utils.js no cargado - cargando...');
    const script = document.createElement('script');
    script.src = '/ClassControl/public/assets/js/utils.js';
    script.onload = () => {
        console.log('[APP] Utils.js cargado correctamente');
        window.__UTILS_LOADING__ = false;
        document.dispatchEvent(new Event('utils-loaded'));
    };
    script.onerror = () => {
        console.error('[APP] Error cargando utils.js');
        window.__UTILS_LOADING__ = false;
    };
    document.head.appendChild(script);
})();

// Exponer funciones globalmente para acceso desde HTML inline
window.api = (endpoint, options) => window.Utils?.api ? window.Utils.api(endpoint, options) : console.error('[APP] Utils not loaded');
window.showAlert = (msg, type, timeout) => window.Utils?.showAlert(msg, type, timeout);
window.showModal = (id) => window.Utils?.showModal(id);
window.closeModal = (id) => window.Utils?.closeModal(id);
window.showLoading = (show) => window.Utils?.showLoading(show);
window.getFormData = (form) => window.Utils?.getFormData(form);
window.validateEmail = (email) => window.Utils?.validateEmail(email);
window.validatePassword = (pass) => window.Utils?.validatePassword(pass);
window.saveCredentials = (email, pass) => window.Utils?.saveCredentials(email, pass);
window.loadCredentials = () => window.Utils?.loadCredentials();
window.clearCredentials = () => window.Utils?.clearCredentials();
window.handleLogin = (e) => window.Utils?.handleLogin(e);
window.loadSavedCredentials = () => window.Utils?.loadSavedCredentials();
window.formatDate = (d) => window.Utils?.formatDate(d);
window.formatTime = (t) => window.Utils?.formatTime(t);
window.formatCurrency = (v) => window.Utils?.formatCurrency(v);
window.capitalize = (s) => window.Utils?.capitalize(s);
window.clearForm = (id) => window.Utils?.clearForm(id);
window.disableForm = (id, disabled) => window.Utils?.disableForm(id, disabled);
window.getUrlParams = () => window.Utils?.getUrlParams();
window.redirect = (url) => window.Utils.redirect(url);

console.log('[APP] Global functions initialized');
