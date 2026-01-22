/**
 * Auth Module - Lógica de autenticación
 */

async function handleLogin(e) {
    e.preventDefault();
    console.log('[LOGIN] Iniciando...');
    
    const btn = document.getElementById('btn-login');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = '⏳ Procesando...';
    
    const formData = Forms.getFormData(e.target);
    const rememberMe = document.getElementById('remember')?.checked || false;
    
    console.log('[LOGIN] Email:', formData.email, 'Remember:', rememberMe);
    
    try {
        const response = await api('/src/api/auth/login.php', {
            method: 'POST',
            body: formData
        });

        console.log('[LOGIN] Respuesta:', response);

        if (response && response.success) {
            // Guardar credenciales si marca "recuérdame"
            if (rememberMe) {
                Forms.saveCredentials(formData.email, formData.password);
                console.log('[LOGIN] Credenciales guardadas');
            } else {
                Forms.clearCredentials();
            }
            
            const msg = '✅ ¡Bienvenido ' + (response.nombre || 'Usuario') + '!';
            showAlert(msg, 'success', 1000);
            console.log('[LOGIN] Redirect a:', response.redirect);
            
            setTimeout(() => {
                window.location.href = response.redirect || '/ClassControl/public/admin/dashboard.php';
            }, 1000);
        } else {
            const errorMsg = response?.message || 'Error desconocido';
            console.error('[LOGIN] Error:', errorMsg);
            showAlert('❌ ' + errorMsg, 'danger', 3000);
            btn.disabled = false;
            btn.textContent = originalText;
        }
    } catch (error) {
        console.error('[LOGIN] Exception:', error);
        showAlert('❌ Error: ' + error.message, 'danger', 3000);
        btn.disabled = false;
        btn.textContent = originalText;
    }
}

// Cargar credenciales guardadas al iniciar
function loadSavedCredentials() {
    const saved = Forms.loadCredentials();
    if (saved) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const rememberCheckbox = document.getElementById('remember');
        
        if (emailInput) emailInput.value = saved.email;
        if (passwordInput) passwordInput.value = saved.password;
        if (rememberCheckbox) rememberCheckbox.checked = true;
        
        console.log('[LOGIN] Credenciales restauradas desde localStorage');
    }
}

// Exportar
window.Auth = { handleLogin, loadSavedCredentials };
