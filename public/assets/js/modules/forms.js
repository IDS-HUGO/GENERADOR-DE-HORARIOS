/**
 * Forms Module - Manejo de formularios
 */

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

function clearForm(formId) { 
    const f = document.getElementById(formId); 
    if (f) f.reset(); 
}

function disableForm(formId, disabled = true) { 
    const f = document.getElementById(formId); 
    if (!f) return; 
    f.querySelectorAll('input,select,textarea,button').forEach(i => i.disabled = disabled); 
}

function validateEmail(email) { 
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; 
    return re.test(String(email).toLowerCase()); 
}

function validatePassword(password) { 
    const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/; 
    return re.test(password); 
}

// Remember me - guardas credenciales
function saveCredentials(email, password) {
    localStorage.setItem('cc_remembered_email', email);
    localStorage.setItem('cc_remembered_password', btoa(password)); // Base64 simple
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

// Exportar
window.Forms = { getFormData, clearForm, disableForm, validateEmail, validatePassword, saveCredentials, loadCredentials, clearCredentials };
