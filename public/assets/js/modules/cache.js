/**
 * ClassControl - Cache y Sincronización Offline
 */

class CacheManager {
    constructor() {
        this.dbName = 'ClassControl_Cache';
        this.storeName = 'datos';
        this.db = null;
        this.init();
    }
    
    init() {
        // Usar IndexedDB si está disponible
        if ('indexedDB' in window) {
            const request = indexedDB.open(this.dbName, 1);
            
            request.onerror = () => {
                console.warn('[CACHE] IndexedDB no disponible, usando localStorage');
            };
            
            request.onsuccess = (e) => {
                this.db = e.target.result;
                console.log('[CACHE] IndexedDB inicializado');
            };
            
            request.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains(this.storeName)) {
                    db.createObjectStore(this.storeName, { keyPath: 'key' });
                }
            };
        }
    }
    
    // Guardar datos en caché
    async guardar(clave, datos, ttl = 3600000) { // 1 hora por defecto
        const item = {
            key: clave,
            datos: datos,
            timestamp: Date.now(),
            expira: Date.now() + ttl
        };
        
        if (this.db) {
            const transaction = this.db.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            store.put(item);
        } else {
            localStorage.setItem(`cache_${clave}`, JSON.stringify(item));
        }
    }
    
    // Obtener datos del caché
    async obtener(clave) {
        let item = null;
        
        if (this.db) {
            return new Promise((resolve) => {
                const transaction = this.db.transaction([this.storeName], 'readonly');
                const store = transaction.objectStore(this.storeName);
                const request = store.get(clave);
                
                request.onsuccess = () => {
                    item = request.result;
                    if (item && item.expira > Date.now()) {
                        resolve(item.datos);
                    } else {
                        resolve(null);
                    }
                };
            });
        } else {
            const cached = localStorage.getItem(`cache_${clave}`);
            if (cached) {
                item = JSON.parse(cached);
                if (item.expira > Date.now()) {
                    return item.datos;
                } else {
                    localStorage.removeItem(`cache_${clave}`);
                }
            }
            return null;
        }
    }
    
    // Limpiar caché expirado
    async limpiar() {
        const ahora = Date.now();
        
        if (this.db) {
            const transaction = this.db.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            const request = store.getAll();
            
            request.onsuccess = () => {
                request.result.forEach(item => {
                    if (item.expira <= ahora) {
                        store.delete(item.key);
                    }
                });
            };
        } else {
            for (let key in localStorage) {
                if (key.startsWith('cache_')) {
                    const item = JSON.parse(localStorage.getItem(key));
                    if (item.expira <= ahora) {
                        localStorage.removeItem(key);
                    }
                }
            }
        }
    }
    
    // Limpiar todo
    limpiarTodo() {
        if (this.db) {
            const transaction = this.db.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            store.clear();
        } else {
            for (let key in localStorage) {
                if (key.startsWith('cache_')) {
                    localStorage.removeItem(key);
                }
            }
        }
    }
    
    // Invalidar caché por patrón
    invalidarPorPatron(patron) {
        if (this.db) {
            const transaction = this.db.transaction([this.storeName], 'readwrite');
            const store = transaction.objectStore(this.storeName);
            const request = store.getAll();
            
            request.onsuccess = () => {
                request.result.forEach(item => {
                    if (item.key.includes(patron)) {
                        store.delete(item.key);
                        console.log('[CACHE] Invalidado:', item.key);
                    }
                });
            };
        } else {
            for (let key in localStorage) {
                if (key.startsWith('cache_') && key.includes(patron)) {
                    localStorage.removeItem(key);
                    console.log('[CACHE] Invalidado:', key);
                }
            }
        }
    }
}

// API wrapper con caché automático
const apiConCache = (async function() {
    const cache = new CacheManager();
    
    return async function(endpoint, options = {}) {
        const cacheKey = `api_${endpoint}`;
        const usarCache = options.cache !== false && options.method !== 'POST';
        
        // Intentar obtener del caché
        if (usarCache) {
            const cached = await cache.obtener(cacheKey);
            if (cached) {
                console.log('[CACHE] Datos desde caché:', cacheKey);
                return cached;
            }
        }
        
        // Llamar API
        const respuesta = await api(endpoint, options);
        
        // Guardar en caché
        if (usarCache && respuesta.success) {
            await cache.guardar(cacheKey, respuesta, options.cacheTTL);
        }
        
        return respuesta;
    };
})();

// Sincronización offline
class SincronizadorOffline {
    constructor() {
        this.cola = [];
        this.online = navigator.onLine;
        this.iniciar();
    }
    
    iniciar() {
        window.addEventListener('online', () => this.alConectar());
        window.addEventListener('offline', () => this.alDesconectar());
    }
    
    alConectar() {
        this.online = true;
        console.log('[SYNC] Sistema online - sincronizando...');
        this.sincronizar();
    }
    
    alDesconectar() {
        this.online = false;
        console.log('[SYNC] Sistema offline - modo caché activado');
    }
    
    agregarAlaCola(operacion) {
        this.cola.push({
            ...operacion,
            timestamp: Date.now()
        });
        localStorage.setItem('sync_cola', JSON.stringify(this.cola));
    }
    
    async sincronizar() {
        const cola = JSON.parse(localStorage.getItem('sync_cola') || '[]');
        
        for (const op of cola) {
            try {
                await api(op.endpoint, {
                    method: op.metodo,
                    body: JSON.stringify(op.datos)
                });
                
                // Remover de cola si fue exitosa
                this.cola = this.cola.filter(x => x.timestamp !== op.timestamp);
                localStorage.setItem('sync_cola', JSON.stringify(this.cola));
            } catch (e) {
                console.error('[SYNC] Error sincronizando:', e);
            }
        }
        
        if (this.cola.length === 0) {
            notificaciones.exito('✓ Sincronizado', 'Todos los cambios fueron sincronizados');
        }
    }
}

const cache = new CacheManager();
const sincronizador = new SincronizadorOffline();

console.log('[CACHE] Módulo de caché y sincronización cargado');
