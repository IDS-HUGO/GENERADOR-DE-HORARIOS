# 📅 ClassControl - Sistema de Gestión de Horarios

**Versión 1.0** | Sistema profesional de gestión de horarios académicos

---

## 📋 Descripción

ClassControl es un **sistema completo de gestión de horarios académicos** diseñado para instituciones educativas. Permite la administración eficiente de docentes, programas académicos, materias, grupos de estudiantes y la generación automática de horarios.

### ✨ Características Principales

- 🎯 **Gestión Completa**: Administración de docentes, programas, materias y grupos
- ⏰ **Horarios Inteligentes**: Generación automática de horarios sin conflictos
- 👥 **Roles de Usuario**: Sistema de permisos para administradores y docentes
- 📊 **Dashboard Interactivo**: Paneles personalizados según el rol del usuario
- 🔒 **Seguridad Avanzada**: Autenticación segura con bcrypt y sesiones
- 📱 **Diseño Responsive**: Interfaz adaptable a todos los dispositivos
- 🎨 **Diseño Moderno**: Paleta de colores Alegreya Ubuntu

---

## 🚀 Instalación Rápida

### Requisitos Previos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache con mod_rewrite habilitado
- Navegador web moderno

### Pasos de Instalación

1. **Clonar o descargar el proyecto**
   ```bash
   # Copiar la carpeta ClassControl_LocalHost a:
   C:\xampp\htdocs\ClassControl_LocalHost
   ```

2. **Crear la base de datos**
   - Abrir phpMyAdmin: `http://localhost/phpmyadmin`
   - Importar el archivo: `database/ClassControl.sql`
   - Esto creará la base de datos `classcontrol` con todas las tablas necesarias

3. **Configurar la conexión** (Opcional)
   - Editar `src/config.php` si es necesario
   - Por defecto usa: localhost, root, sin contraseña

4. **Acceder al sistema**
   - URL: `http://localhost/ClassControl_LocalHost/public/`
   - Credenciales de administrador:
     - **Email**: admin@classcontrol.com
     - **Contraseña**: GenerateSecure123!

5. **¡Listo!** El sistema está funcionando

> ⚠️ **Importante**: Cambia la contraseña del administrador inmediatamente después del primer acceso.

---

## 📁 Estructura del Proyecto

```
ClassControl_LocalHost/
├── database/
│   └── ClassControl.sql          # Base de datos completa
├── logs/                          # Logs del sistema
├── public/                        # Archivos públicos
│   ├── index.html                 # Login
│   ├── admin/
│   │   └── dashboard.php          # Panel administrador
│   ├── docente/
│   │   └── dashboard.php          # Panel docente
│   └── assets/
│       ├── css/
│       │   └── style.css          # Estilos profesionales
│       └── js/
│           └── utils.js           # Utilidades JavaScript
└── src/                           # Backend PHP
    ├── config.php                 # Configuración central
    ├── api/
    │   ├── auth/                  # APIs de autenticación
    │   ├── docentes.php           # API de docentes
    │   ├── programas.php          # API de programas
    │   ├── materias.php           # API de materias
    │   └── grupos.php             # API de grupos
    ├── models/                    # Modelos OOP
    │   ├── Model.php              # Clase base
    │   ├── Usuario.php
    │   ├── Docente.php
    │   ├── Materia.php
    │   └── ...
    └── includes/
        └── Models.php             # Carga automática
```

---

## 👥 Roles de Usuario

### 👨‍💼 Administrador

**Funcionalidades:**
- ✅ Registrar, editar y eliminar docentes
- ✅ Crear y gestionar programas académicos
- ✅ Administrar materias y cursos
- ✅ Crear grupos de estudiantes
- ✅ Generar horarios automáticamente
- ✅ Ver estadísticas y reportes completos
- ✅ Gestión total del sistema

**Acceso:**
- Email: admin@classcontrol.com
- Panel: `/public/admin/dashboard.php`

### 👨‍🏫 Docente

**Funcionalidades:**
- ✅ Ver su información personal
- ✅ Actualizar disponibilidad horaria
- ✅ Consultar materias asignadas
- ✅ Ver horario semanal personal
- ✅ Editar datos de perfil
- ✅ Ver estadísticas personales

**Acceso:**
- Registro desde el login
- Panel: `/public/docente/dashboard.php`

---

## 🎨 Diseño y Paleta de Colores

**Paleta Alegreya Ubuntu:**
- 🔵 **Azul Claro**: `#ADD8E6` - Elementos principales
- ⚪ **Gris Claro**: `#D3D3D3` - Elementos secundarios
- 🌸 **Rosa Claro**: `#FFB6C1` - Acentos y destacados
- 🍊 **Salmón Claro**: `#FFCBA4` - Acentos especiales

**Características del Diseño:**
- ✨ Interfaz moderna y profesional
- 📱 Completamente responsive
- 🎯 Navegación intuitiva
- ⚡ Animaciones suaves
- 🔔 Sistema de notificaciones
- 🖼️ Modales interactivos

---

## 🛠️ Tecnologías Utilizadas

### Backend
- **PHP 7.4+**: Lenguaje del servidor
- **MySQL 5.7+**: Base de datos relacional
- **PDO**: Acceso seguro a la base de datos
- **OOP**: Arquitectura orientada a objetos

### Frontend
- **HTML5**: Estructura semántica
- **CSS3**: Estilos modernos con variables CSS
- **JavaScript ES6+**: Funcionalidad interactiva
- **AJAX/Fetch**: Comunicación asíncrona

### Seguridad
- **bcrypt**: Hash de contraseñas
- **Prepared Statements**: Prevención de SQL injection
- **Session Management**: Control de sesiones seguras
- **Input Validation**: Validación de entrada de datos

---

## 📊 Base de Datos

### Tablas Principales

1. **usuarios** - Información de usuarios (admin/docentes)
2. **docentes** - Datos específicos de docentes
3. **programas_academicos** - Carreras y programas
4. **materias** - Cursos y asignaturas
5. **grupos** - Grupos de estudiantes
6. **asignaciones** - Asignación docente-materia-grupo
7. **horarios** - Horarios generados
8. **disponibilidad_horaria** - Disponibilidad de docentes
9. **aulas** - Salones y espacios
10. **preferencias_docentes** - Preferencias de materias
11. **restricciones** - Restricciones de horarios
12. **historial_cambios** - Auditoría
13. **configuracion_sistema** - Parámetros del sistema

---

## 🔧 Configuración Avanzada

### Modificar Configuración

Editar `src/config.php`:

```php
// Base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'classcontrol');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');

// Aplicación
define('PRODUCTION', true);
define('APP_URL', 'http://tu-dominio.com');

// Seguridad
define('SESSION_LIFETIME', 8 * 3600); // 8 horas
```

### Habilitar Logs

Los logs se guardan automáticamente en la carpeta `/logs/`:
- `error.log` - Errores del sistema
- `info.log` - Información general

---

## 🐛 Solución de Problemas

### Error de Conexión a Base de Datos
```
✅ Verificar que MySQL esté corriendo
✅ Revisar credenciales en config.php
✅ Comprobar que la BD existe
```

### No Carga el CSS/JS
```
✅ Verificar la ruta base en config.php
✅ Limpiar caché del navegador
✅ Revisar permisos de archivos
```

### Sesión Expira Muy Rápido
```
✅ Aumentar SESSION_LIFETIME en config.php
✅ Verificar configuración de PHP session.gc_maxlifetime
```

---

## 📈 Próximas Características

- [ ] Generación automática de horarios con algoritmo inteligente
- [ ] Exportación de horarios a PDF
- [ ] Notificaciones por correo electrónico
- [ ] Sistema de reportes avanzados
- [ ] Integración con calendario externo
- [ ] Aplicación móvil nativa
- [ ] Sistema de backup automático

---

## 🤝 Contribuir

Si deseas contribuir al proyecto:

1. Realiza un fork del repositorio
2. Crea una rama para tu feature
3. Realiza tus cambios
4. Envía un pull request

---

## 📄 Licencia

Este proyecto es de código propietario. Todos los derechos reservados.

---

## 👨‍💻 Autor

**ClassControl Development Team**
- Email: admin@classcontrol.com
- Versión: 1.0
- Fecha: 2026

---

## 📞 Soporte

Para soporte técnico o consultas:
- **Email**: admin@classcontrol.com
- **Documentación**: Ver archivos en `/docs`

---

## ⚡ Comandos Útiles

```bash
# Iniciar Apache y MySQL
xampp-control.exe

# Ver logs en tiempo real (Windows PowerShell)
Get-Content logs/error.log -Wait

# Backup de base de datos
mysqldump -u root classcontrol > backup.sql

# Restaurar base de datos
mysql -u root classcontrol < backup.sql
```

---

## 🎓 Créditos

Diseñado y desarrollado con ❤️ para instituciones educativas que buscan optimizar su gestión de horarios académicos.

**ClassControl** - Gestión Inteligente de Horarios Académicos
