/**
 * API Module - Gestiona comunicación con el servidor
 */

function detectApiBase() {
    const override = window.__API_BASE_URL__ || localStorage.getItem('API_BASE_URL');
    if (override) return override.replace(/\/$/, '');

    const { origin, pathname } = window.location;
    
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
console.log('[API] Base URL:', API_BASE_URL);

/**
 * Fetch wrapper
 */
async function api(endpoint, options = {}) {
    const { method = 'GET', body = null, headers = {} } = options;
    const config = { 
        method, 
        headers: { 'Content-Type': 'application/json', ...headers }, 
        credentials: 'include' 
    };
    if (body) config.body = typeof body === 'string' ? body : JSON.stringify(body);
    const url = endpoint.startsWith('http') ? endpoint : `${API_BASE_URL}${endpoint}`;

    console.log('[API REQUEST]', method, url, body);
    showLoading(true);
    
    try {
        const resp = await fetch(url, config);
        console.log('[API RESPONSE STATUS]', resp.status);
        const text = await resp.text();
        console.log('[API RAW RESPONSE]', text.substring(0, 200));
        
        let json;
        try {
            json = JSON.parse(text);
        } catch(e) {
            console.error('[API PARSE ERROR]', e, text);
            throw new Error('Invalid JSON response: ' + text);
        }
        
        console.log('[API JSON RESPONSE]', json);
        
        if (!resp.ok && json && json.success === false) {
            console.warn('[API] Error response:', json.message);
            return json;
        }
        
        return json;
    } catch (err) {
        console.error('[API FETCH ERROR]', err);
        throw err;
    } finally {
        showLoading(false);
    }
}

// Exportar para navegadores
window.API = { api, API_BASE_URL };
