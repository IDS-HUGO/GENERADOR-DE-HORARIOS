# Configuración Dinámica del Proyecto

## ✅ El proyecto ahora funciona con CUALQUIER nombre de carpeta

No necesita estar en `/ClassControl` ni `/GENERADOR-DE-HORARIOS`. El sistema detecta dinámicamente el nombre de la carpeta y configura las rutas automáticamente.

### Cómo funciona:

1. **config.php** - Detecta dinámicamente la URL base
   - Función `getAppUrl()` extrae el nombre de carpeta de la URL
   - Define constante `APP_URL` con la URL correcta

2. **api.js** - Detecta dinámicamente la base URL de APIs
   - Función `detectApiBase()` extrae el nombre de carpeta
   - Todas las llamadas a `/src/api/...` usan esta base dinámicamente

3. **login.php** - Redirige dinámicamente
   - Usa `APP_URL` de config.php para construir rutas de redirección
   - No está hardcodeado `/ClassControl/public/admin/dashboard.php`

### Prueba con tu amigo:

1. Tu amigo clona el proyecto con cualquier nombre:
   ```
   git clone <repo> GENERADOR-DE-HORARIOS
   cd GENERADOR-DE-HORARIOS
   ```

2. Accede desde el navegador:
   ```
   http://localhost/GENERADOR-DE-HORARIOS/public/index.html
   ```

3. Login y verifica:
   - ✅ No debe dar 404 en las APIs
   - ✅ Debe redirigir correctamente a `/GENERADOR-DE-HORARIOS/public/admin/dashboard.php`
   - ✅ El admin panel debe cargar sin errores de rutas

### Archivos modificados:

- `src/config.php` - Función `getAppUrl()` dinámica
- `src/api/auth/login.php` - Redirección dinámica
- `public/assets/js/modules/api.js` - detectApiBase() mejorado
- `public/assets/js/modules/auth.js` - Usa redirect del servidor

### Notas técnicas:

- Las rutas relativas en HTML/CSS funcionan automáticamente
- Los módulos JS usan `API_BASE_URL` detectado al cargar
- El servidor PHP detecta la carpeta mediante `$_SERVER['REQUEST_URI']`
- Las sesiones usan `APP_URL` para rutas de redirección

### Si aún hay problemas:

1. Verifica en DevTools > Network que las URLs de API sean correctas
2. Revisa la consola JS para logs de `[API] Base URL:`
3. Comprueba que la carpeta del proyecto está en `/xampp/htdocs/`
