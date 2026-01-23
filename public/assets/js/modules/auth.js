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
            const payload = response.data || response;

            // Guardar credenciales si marca "recuérdame"
            if (rememberMe) {
                Forms.saveCredentials(formData.email, formData.password);
                console.log('[LOGIN] Credenciales guardadas');
            } else {
                Forms.clearCredentials();
            }
            
            const msg = '✅ ¡Bienvenido ' + (payload.nombre || 'Usuario') + '!';
            showAlert(msg, 'success', 1000);
            
            // Usar redirect del servidor (dinámico)
            let redirectUrl = payload.redirect;
            
            if (!redirectUrl) {
                // Fallback: construir URL dinámicamente y respetar rol
                const { origin, pathname } = window.location;
                const pathParts = pathname.split('/').filter(p => p);
                const publicIndex = pathParts.indexOf('public');
                const projectFolder = publicIndex > 0 ? pathParts[0] : '';
                const base = projectFolder ? origin + '/' + projectFolder : origin;
                const dashPath = payload.tipo_usuario === 'docente' ? '/public/docente/dashboard.php' : '/public/admin/dashboard.php';
                redirectUrl = base + dashPath;
            }
            
            console.log('[LOGIN] Redirect a:', redirectUrl);
            
            setTimeout(() => {
                window.location.href = redirectUrl;
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

/**
 * Logout - Cierra sesión y limpia datos locales
 */
async function handleLogout(apiEndpoint = '/src/api/auth/logout.php') {
    if (!confirm('¿Desea cerrar sesión?')) return;
    
    try {
        console.log('[LOGOUT] Iniciando...');
        
        // Mostrar estado
        const logoutBtn = document.getElementById('logout-btn') || document.querySelector('[onclick*="logout"]');
        if (logoutBtn) {
            logoutBtn.disabled = true;
            logoutBtn.textContent = '⏳ Cerrando...';
        }
        
        // Llamar API de logout
        const response = await fetch(apiEndpoint, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' }
        });
        
        const data = await response.json();
        console.log('[LOGOUT] Respuesta:', data);
        
        if (data && data.success) {
            // Limpiar datos locales
            localStorage.clear();
            sessionStorage.clear();
            console.log('[LOGOUT] Datos locales limpiados');
            
            // Mostrar mensaje (si existe showAlert)
            if (typeof showAlert === 'function') {
                showAlert('✅ Sesión cerrada correctamente', 'success', 1500);
            }
            
            // Redirigir al login
            setTimeout(() => {
                let redirectUrl = data.data?.redirect;
                
                if (!redirectUrl) {
                    // Construir URL de redirección dinámicamente
                    const { origin, pathname } = window.location;
                    const pathParts = pathname.split('/').filter(p => p);
                    const projectFolder = pathParts[0] || '';
                    redirectUrl = projectFolder ? origin + '/' + projectFolder + '/public/index.html' : origin + '/public/index.html';
                }
                
                console.log('[LOGOUT] Redirigiendo a:', redirectUrl);
                window.location.href = redirectUrl;
            }, 1500);
        } else {
            const errorMsg = data.message || 'desconocido';
            console.error('[LOGOUT] Error:', errorMsg);
            if (typeof showAlert === 'function') {
                showAlert('❌ Error al cerrar sesión: ' + errorMsg, 'danger', 3000);
            }
            
            // Restaurar botón
            if (logoutBtn) {
                logoutBtn.disabled = false;
                logoutBtn.textContent = logoutBtn.textContent.includes('Salir') ? 'Salir' : 'Logout';
            }
        }
    } catch (error) {
        console.error('[LOGOUT] Error:', error);
        if (typeof showAlert === 'function') {
            showAlert('❌ Error de conexión: ' + error.message, 'danger', 3000);
        }
    }
}

// Exportar
window.Auth = { handleLogin, loadSavedCredentials, handleLogout };
