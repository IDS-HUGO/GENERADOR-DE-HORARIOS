# ClassControl - Universidad Maya

**Sistema de Gestión de Horarios Académicos**  
Copyright © 2026 Universidad Maya - Todos los derechos reservados

---

## 🎓 Descripción

Sistema profesional para gestión de horarios académicos con enfoque en autonomía docente y priorización por antigüedad. Diseñado específicamente para la Universidad Maya.

## ✨ Características v2.0

### Roles del Sistema
- 👑 **DIRECTORES**: Administran todo el sistema, gestionan administradores
- ⚙️ **ADMINISTRADORES**: Gestionan horarios, docentes, materias, grupos y programas
- 👨‍🏫 **DOCENTES**: Eligen materias y grupos según disponibilidad y antigüedad

### Novedades
- ✅ Sistema de selección autónoma para docentes
- ✅ Prioridad basada en años de antigüedad
- ✅ Gestión de administradores (solo directores)
- ✅ Gestión y aprobación de horarios (admin/director)
- ✅ Dashboard mejorado con estadísticas
- ✅ APIs REST optimizadas

---

## 📁 Estructura de Carpetas

```
ClassControl/
├── .env                    → Variables de entorno (DB, SMTP, URL)
├── .gitignore              → Archivos ignorados por git
├── .htaccess               → Reglas Apache (seguridad, rutas)
├── composer.json           → Dependencias PHP
├── README.md               → Esta guía rápida
├── DOCUMENTACION.md        → Documentación técnica completa
│
├── database/               → Backups y scripts SQL
│   ├── .htaccess           → Bloquear acceso web
│   └── ClassControl.sql    → Script completo de BD
│
├── logs/                   → Logs de aplicación
│   └── .htaccess           → Bloquear acceso web
│
├── public/                 → Frontend (servido por Apache)
│   ├── index.html          → Página de login
│   ├── assets/             → CSS, JS, imágenes
│   ├── admin/              → Dashboard admin/director
│   │   ├── dashboard.php
│   │   └── sections/       → Secciones del panel
│   │       ├── administradores.php  (Solo directores)
│   │       ├── docentes.php
│   │       ├── grupos.php
│   │       ├── materias.php
│   │       └── programas.php
│   └── docente/            → Portal docente
│       ├── dashboard.php
│       └── seleccion-materias.php  (Nueva funcionalidad)
│
├── scripts/                → Utilidades de soporte o cron
│
└── src/                    → Código backend PHP
    ├── config.php          → Configuración principal
    ├── config.example.php  → Plantilla de config
    ├── api/                → Endpoints REST
    │   ├── administradores.php     → Gestión admins
    │   ├── seleccion-docente.php   → Selección materias
    │   ├── docentes.php
    │   ├── grupos.php
    │   ├── horarios.php
    │   ├── materias.php
    │   ├── programas.php
    │   └── auth/           → Login, logout, registro
    ├── models/             → Clases modelo (Usuario, Docente, etc.)
    ├── controllers/        → Lógica de negocio
    ├── includes/           → Servicios compartidos
    └── views/              → Plantillas
```

---

## 🚀 Puesta en Marcha Rápida

### 1. Configurar Base de Datos
```bash
# Importar SQL
mysql -u root -p < database/ClassControl.sql
```

### 2. Configurar Variables de Entorno
Edita el archivo `.env`:
```env
# Base de Datos
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=tu_password
DB_NAME=classcontrol

# Aplicación
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost/ClassControl

# Email (opcional)
GMAIL_EMAIL=tu_email@gmail.com
GMAIL_PASSWORD=tu_app_password
```

### 3. Configurar Apache
**Opción A:** DocumentRoot apunta a `/public`
```apache
DocumentRoot "C:/xampp/htdocs/ClassControl/public"
```

**Opción B:** Usar `.htaccess` en raíz (ya incluido)

### 4. Verificar Permisos
```bash
# Windows
icacls logs /grant Everyone:(OI)(CI)F

# Linux/Mac
chmod 755 logs/
chmod 644 .env src/config.php
```

### 5. Acceder al Sistema
- URL: `http://localhost/ClassControl/public/`
- Usar credenciales iniciales (ver abajo)

---

## 🔐 Credenciales Iniciales

### Director General
```
Email: director@universidadmaya.edu
Contraseña: director123
```

### Administrador
```
Email: admin@universidadmaya.edu
Contraseña: admin123
```

**⚠️ IMPORTANTE**: Cambiar estas contraseñas después del primer login.

---

## 📖 Flujos de Trabajo

### Para Docentes
1. Login → Portal Docente
2. Ir a **"Seleccionar Materias"**
3. Elegir grupo → Ver materias disponibles
4. Solicitar asignación
5. Esperar aprobación del administrador

**Nota**: Mayor antigüedad = mayor prioridad

### Para Administradores
1. Login → Panel Administrativo
2. Gestionar docentes, programas, materias, grupos
3. Aprobar solicitudes de asignación
4. Generar horarios y asignar aulas
5. Generar reportes

### Para Directores
1. Login → Panel Director
2. Acceso a **"Administradores"** (gestión exclusiva)
3. Crear/editar/desactivar administradores
4. Supervisar estadísticas generales del sistema
5. Auditar actividades

---

## 🛠️ Tecnologías

- **Backend**: PHP 7.4+, MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript vanilla
- **Servidor**: Apache 2.4+ con mod_rewrite
- **Seguridad**: Sessions, password_hash(), CSRF protection
- **Arquitectura**: MVC, PDO/MySQLi

---

## 📊 Base de Datos

### Tablas Principales
- `usuarios`: Directores, administradores, docentes
- `docentes`: Perfil docente con antigüedad
- `asignaciones`: Solicitudes y aprobaciones (con prioridad)
- `programas_academicos`, `materias`, `grupos`
- `horarios`, `disponibilidad_horaria`
- `historial_cambios`: Auditoría completa

### Optimizaciones v2.0
- Índices en campos críticos
- Nuevos campos: `antiguedad`, `prioridad_asignacion`, `solicitada_por_docente`
- Mejor performance en consultas complejas

---

## 🔌 APIs Principales

### Docentes: `/src/api/seleccion-docente.php`
- `GET ?action=grupos` → Lista grupos
- `GET ?action=materias&grupo_id=X` → Materias de grupo
- `POST {action: 'solicitar', ...}` → Solicitar materia
- `GET ?action=mis-solicitudes` → Ver mis solicitudes

### Directores: `/src/api/administradores.php`
- `GET ?action=list` → Listar administradores
- `POST {nombre, email, ...}` → Crear administrador
- `PUT {usuario_id, ...}` → Editar administrador
- `DELETE ?id=X` → Desactivar administrador

### Horarios: `/src/api/horarios.php`
- `GET ?action=gestion` → Listar horarios con contexto (admin/director)
- `POST action=approve` → Aprobar/Rechazar horario
- `DELETE ?action=delete&id=X` → Eliminar horario

---

## 🐛 Troubleshooting

**Error de conexión DB**
- Verificar credenciales en `.env`
- Verificar MySQL corriendo: `mysql -u root -p`

**Sesión no inicia**
- Verificar permisos en `/logs`
- Limpiar cookies del navegador

**No se ven estilos CSS**
- Verificar `APP_URL` en `.env`
- Verificar `.htaccess` activo
- Limpiar caché navegador (Ctrl+F5)

**Error 403 en APIs**
- Verificar sesión activa
- Verificar permisos de rol (director/admin/docente)

---

## 📚 Documentación Completa

Para detalles técnicos, arquitectura, ejemplos de código y más, consulta:
- 📄 **[DOCUMENTACION.md](DOCUMENTACION.md)** → Guía técnica completa

---

## 📞 Soporte

**Universidad Maya**  
Email: soporte@universidadmaya.edu  
Teléfono: +52 999 000 0000  
Horario: Lunes a Viernes, 8:00 AM - 6:00 PM

---

## 📜 Licencia

**© 2026 Universidad Maya - Todos los derechos reservados**

Este software es propiedad exclusiva de la Universidad Maya.  
Prohibida su reproducción, distribución o uso no autorizado.

---

## 🔄 Versión Actual: 2.0

- ✅ Sistema de roles completo (Director/Admin/Docente)
- ✅ Selección autónoma de materias
- ✅ Prioridad por antigüedad
- ✅ Gestión de administradores
- ✅ Base de datos optimizada
- ✅ Copyright Universidad Maya integrado

