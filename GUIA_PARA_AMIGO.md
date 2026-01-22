# 📖 Guía para Tu Amigo - Instalación en Carpeta Diferente

## ¿Qué pasó?

El proyecto estaba configurado para funcionar SOLO en la carpeta `/ClassControl`. Cuando tu amigo lo clonó como `GENERADOR-DE-HORARIOS`, todas las APIs devolvían 404 porque las rutas estaban hardcodeadas.

## ✅ Ya está arreglado

Hemos actualizado el proyecto para detectar **dinámicamente** el nombre de la carpeta. Ahora funciona con CUALQUIER nombre.

---

## 🚀 Pasos para que tu amigo lo instale

### 1. Clonar el repositorio
```bash
cd c:\xampp\htdocs
git clone <URL_DEL_REPOSITORIO> GENERADOR-DE-HORARIOS
cd GENERADOR-DE-HORARIOS
```

### 2. Configurar la base de datos
```bash
# Importar el SQL en phpMyAdmin o MySQL CLI
mysql -u root < database/ClassControl.sql
```

O en phpMyAdmin:
- Abre http://localhost/phpmyadmin
- Crea base de datos `classcontrol`
- Importa `database/ClassControl.sql`

### 3. Verificar la instalación
Abre en el navegador:
```
http://localhost/GENERADOR-DE-HORARIOS/public/verificador.html
```

Este verificador comprobará automáticamente:
- ✅ Detección correcta de carpeta del proyecto
- ✅ API Base URL configurada correctamente
- ✅ Conexión a endpoints de la API
- ✅ Funcionalidad del login

### 4. Acceder al sistema
```
http://localhost/GENERADOR-DE-HORARIOS/public/index.html
```

**Credenciales de prueba:**
- Email: `admin@classcontrol.com`
- Contraseña: (revisa la BD, debe estar configurada)

---

## 🔧 Cambios realizados para soporte dinámico

### En `src/config.php`
```php
function getAppUrl() {
    // Detecta dinámicamente la carpeta desde la URL
    // Retorna http://localhost/GENERADOR-DE-HORARIOS (o la que sea)
}
define('APP_URL', getAppUrl());
```

### En `public/assets/js/app.js`
```javascript
// Detecta dinámicamente la carpeta y carga utils.js desde la ruta correcta
script.src = projectFolder + '/public/assets/js/utils.js';
```

### En `public/assets/js/utils.js` y `modules/api.js`
```javascript
function detectApiBase() {
    // Extrae el nombre de carpeta de window.location.pathname
    // Retorna http://localhost/GENERADOR-DE-HORARIOS
}
```

### En `src/api/auth/login.php`
```php
// Construye URLs de redirección dinámicamente
// En lugar de: /ClassControl/public/admin/dashboard.php
// Ahora usa: /GENERADOR-DE-HORARIOS/public/admin/dashboard.php (automático)
```

---

## 📋 Checklist para tu amigo

- [ ] Base de datos importada correctamente
- [ ] Pueden acceder a `/verificador.html`
- [ ] Todas las verificaciones pasan ✅
- [ ] Pueden hacer login
- [ ] El panel de admin carga sin errores 404
- [ ] Las tablas (docentes, programas, materias, grupos) cargan datos
- [ ] Los botones de crear/editar/eliminar funcionan

---

## 🐛 Si algo falla

### Error: "Cannot GET /GENERADOR-DE-HORARIOS/public/verificador.html"
→ Verifica que la carpeta esté en `c:\xampp\htdocs\GENERADOR-DE-HORARIOS`

### Error: "API returned 404 on login"
→ Abre DevTools (F12) → Console → busca logs `[API] Base URL:`
→ Debe mostrar algo como: `http://localhost/GENERADOR-DE-HORARIOS`

### Error: "Ruta de redirección incorrecta"
→ Verifica que `src/config.php` tenga la función `getAppUrl()`
→ Recompila con `php -l src/config.php` para verificar sintaxis

### Error: "Base de datos no conecta"
→ Verifica que `classcontrol` exista en MySQL
→ Verifica credenciales en `src/config.php`

---

## 📞 Soporte

Si algo no funciona:

1. Ejecuta el verificador: `http://localhost/GENERADOR-DE-HORARIOS/public/verificador.html`
2. Abre DevTools (F12) → Console
3. Busca mensajes de error con `[API]`, `[APP]`, `[LOGIN]`
4. Comparte esos logs

---

## ✨ Nota importante

El proyecto ahora es **100% portable**. Puede estar en:
- `/ClassControl`
- `/GENERADOR-DE-HORARIOS`
- `/MiProyecto`
- Cualquier nombre que quieras

¡Sin cambiar nada de código! 🎉
