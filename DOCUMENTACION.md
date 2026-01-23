# Sistema de Gestión de Horarios - Universidad Maya

**Copyright © 2026 Universidad Maya - Todos los derechos reservados**

## 🎓 Sistema ClassControl v2.0

Sistema profesional de gestión de horarios académicos desarrollado para facilitar la asignación de materias y grupos en la Universidad Maya, con énfasis en la autonomía docente y priorización por antigüedad.

---

## 🌟 Características Principales

### Jerarquía de Roles

#### 👑 **DIRECTORES**
- Máximo nivel de autoridad en el sistema
- Gestionan administradores (crear, editar, activar/desactivar)
- Supervisión completa del sistema académico
- Acceso total a reportes y estadísticas
- Aprueban y configuran parámetros del sistema

#### ⚙️ **ADMINISTRADORES**
- Gestionan docentes, programas, materias y grupos
- Aprueban solicitudes de asignación de docentes
- Generan horarios y verifican conflictos
- Administran aulas y disponibilidad
- Crean reportes y auditorías

#### 👨‍🏫 **DOCENTES**
- **Selección autónoma de materias y grupos**
- Prioridad basada en antigüedad (años de servicio)
- Gestión de disponibilidad horaria
- Visualización de horarios asignados
- Consulta de perfil y estadísticas personales

---

## 🚀 Novedades v2.0

### 1. Sistema de Selección Inteligente para Docentes
- Los docentes pueden elegir sus propias materias y grupos
- Sistema de prioridad automática basado en antigüedad
- Vista en tiempo real de disponibilidad de materias
- Solicitudes pendientes de aprobación administrativa

### 2. Gestión de Administradores (Solo Directores)
- Panel exclusivo para directores
- Creación y edición de cuentas de administradores
- Control de acceso y permisos
- Auditoría de actividades administrativas

### 3. Optimizaciones de Base de Datos
- Índices mejorados para consultas rápidas
- Nuevos campos: `prioridad_asignacion`, `antiguedad`, `solicitada_por_docente`
- Gestión de horarios con aprobación: `estado_horario`, `aprobado_por`, `aprobado_rol`, `observaciones`

---

## 📋 Estructura de la Base de Datos

### Tablas Principales

#### `usuarios`
- Tipo: `director`, `administrador`, `docente`
- Estados: `activo`, `inactivo`, `pendiente`
- Control de accesos y bloqueos

#### `docentes`
- Vinculación con usuarios
- **`antiguedad`**: años de servicio (prioridad)
- **`prioridad_seleccion`**: calculada automáticamente
- Horas asignadas vs máximas semanales

#### `asignaciones`
- Estados: `solicitada`, `pendiente`, `asignada`, `confirmada`, `cancelada`, `completada`
- **`solicitada_por_docente`**: indica si fue solicitud del docente
- **`prioridad_asignacion`**: basada en antigüedad
- **`aprobada_por_admin`**: control de aprobaciones

#### Otras tablas
- `programas_academicos`: carreras y modalidades
- `materias`: con créditos, horas, prerrequisitos
- `grupos`: semestres, jornadas, capacidad
- `horarios`: días, horas, aulas
- `disponibilidad_horaria`: preferencias docentes
- `restricciones`: licencias, capacitaciones
- `historial_cambios`: auditoría completa

---

## 🔐 Credenciales Iniciales

### Director General
```
Email: director@universidadmaya.edu
Contraseña: director123
```

### Administrador Principal
```
Email: admin@universidadmaya.edu
Contraseña: admin123
```

**⚠️ IMPORTANTE: Cambiar estas contraseñas en producción**

---

## 🛠️ Instalación y Configuración

### Requisitos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache con mod_rewrite
- Composer (opcional, para dependencias)

### Pasos de Instalación

1. **Importar Base de Datos**
   ```bash
   mysql -u root -p < database/ClassControl.sql
   ```

2. **Configurar Variables de Entorno**
   - Editar archivo `.env` con credenciales DB y SMTP
   ```
   DB_HOST=localhost
   DB_USERNAME=root
   DB_PASSWORD=tu_password
   DB_NAME=classcontrol
   APP_URL=http://localhost/ClassControl
   ```

3. **Configurar Apache**
   - DocumentRoot debe apuntar a `/public`
   - O usar `.htaccess` para reescritura de rutas

4. **Verificar Permisos**
   ```bash
   chmod 755 /logs
   chmod 644 /src/config.php
   ```

5. **Acceder al Sistema**
   - URL: `http://localhost/ClassControl/public/`
   - Login con credenciales de director o admin

---

## 📖 Flujo de Trabajo

### Para Docentes

1. **Login** → Portal Docente
2. **Ir a "Seleccionar Materias"**
3. Ver grupos disponibles y su información
4. Seleccionar grupo → Ver materias disponibles
5. Solicitar asignación de materia
6. Esperar aprobación del administrador
7. Consultar horarios confirmados

**Nota**: Los docentes con mayor antigüedad tienen prioridad en caso de conflicto.

### Para Administradores

1. **Login** → Panel Administrativo
2. Gestionar docentes, programas, materias, grupos
3. Revisar solicitudes de asignación
4. Aprobar/rechazar solicitudes (considerando antigüedad)
5. Generar horarios sin conflictos
6. Asignar aulas
7. Generar reportes

### Para Directores

1. **Login** → Panel Director
2. Acceso a sección **"Administradores"**
3. Crear/editar/desactivar administradores
4. Supervisar estadísticas generales
5. Aprobar configuraciones del sistema
6. Auditar actividades

---

## 🔌 APIs Principales

### `/src/api/seleccion-docente.php` (Docentes)
```
GET ?action=grupos              → Lista grupos disponibles
GET ?action=materias&grupo_id=X → Materias de un grupo
GET ?action=mis-solicitudes     → Solicitudes del docente
GET ?action=estadisticas        → Stats personales
POST {action: 'solicitar', materia_id, grupo_id} → Solicitar materia
POST {action: 'cancelar', asignacion_id} → Cancelar solicitud
```

### `/src/api/administradores.php` (Solo Directores)
### `/src/api/horarios.php` (Admin/Director)
```
GET ?action=gestion                → Listar horarios con contexto completo
POST action=approve {id, estado}   → Aprobar/Rechazar horario (estado: aprobado|rechazado)
DELETE ?action=delete&id=X         → Eliminar horario
```

```
GET ?action=list  → Listar todos los administradores
GET ?action=get&id=X → Obtener detalles de admin
GET ?action=stats → Estadísticas del sistema
POST {nombre, apellido, email, password, ...} → Crear admin
PUT {usuario_id, campos...} → Editar admin
DELETE ?id=X → Desactivar admin
```

### `/src/api/auth/login.php`
- Maneja login para los 3 roles
- Redirige según tipo de usuario:
  - Director → `/public/admin/dashboard.php?role=director`
  - Administrador → `/public/admin/dashboard.php`
  - Docente → `/public/docente/dashboard.php`

---

## 📁 Estructura del Proyecto

```
ClassControl/
├── .env                    # Variables de entorno (DB, SMTP)
├── .gitignore              # Archivos ignorados por Git
├── .htaccess               # Configuración Apache
├── composer.json           # Dependencias PHP
├── README.md               # Esta documentación
│
├── database/
│   ├── .htaccess           # Bloquear acceso web
│   └── ClassControl.sql    # Script SQL completo
│
├── logs/                   # Logs del sistema
│   └── .htaccess
│
├── public/                 # Frontend (servido por Apache)
│   ├── index.html          # Login
│   ├── assets/
│   │   ├── css/style.css
│   │   ├── img/
│   │   └── js/
│   │       ├── app.js
│   │       ├── helpers.js
│   │       └── modules/
│   ├── admin/
│   │   ├── dashboard.php   # Dashboard admin/director
│   │   └── sections/
│   │       ├── administradores.php  # Solo directores
│   │       ├── docentes.php
│   │       ├── grupos.php
│   │       ├── materias.php
│   │       └── programas.php
│   └── docente/
│       ├── dashboard.php
│       └── seleccion-materias.php  # Nueva funcionalidad
│
├── scripts/                # Utilidades (cron, backups)
│
└── src/                    # Backend PHP
    ├── .htaccess           # Bloquear acceso directo
    ├── config.php
    ├── config.example.php
    ├── api/
    │   ├── administradores.php     # Gestión admins (directores)
    │   ├── seleccion-docente.php   # Selección materias (docentes)
    │   ├── docentes.php
    │   ├── grupos.php
    │   ├── horarios.php
    │   ├── materias.php
    │   ├── programas.php
    │   ├── reportes.php
    │   └── auth/
    │       ├── login.php
    │       ├── logout.php
    │       └── register.php
    ├── controllers/
    ├── includes/
    │   ├── EmailService.php
    │   └── Models.php
    ├── models/
    │   ├── Usuario.php      # Mejorado con roles
    │   ├── Docente.php      # Mejorado con selección
    │   ├── Asignacion.php
    │   ├── Grupo.php
    │   ├── Horario.php
    │   ├── Materia.php
    │   └── ...
    └── views/
```

---

## 🎨 Características Técnicas

### Frontend
- HTML5, CSS3 puro (sin frameworks pesados)
- JavaScript vanilla para interactividad
- Diseño responsive (móvil, tablet, escritorio)
- Componentes modulares reutilizables

### Backend
- PHP 7.4+ orientado a objetos
- Patrón MVC (Model-View-Controller)
- PDO/MySQLi para base de datos
- Sistema de sesiones seguro
- Validación y sanitización de datos

### Seguridad
- Contraseñas hasheadas con `password_hash()`
- Protección contra SQL injection
- Protección CSRF en formularios
- Control de acceso basado en roles
- Auditoría de cambios

### Performance
- Índices optimizados en BD
- Consultas eficientes con JOINs
- Caché de sesiones
- Assets minificados

---

## 📊 Configuración del Sistema

Tabla `configuracion_sistema` permite ajustar:
- `hora_inicio_clases`: 08:00
- `hora_fin_clases`: 20:00
- `duracion_clase`: 60 minutos
- `max_horas_docente`: 40 horas/semana
- `prioridad_por_antiguedad`: true
- `max_asignaciones_por_docente`: 6 materias
- Datos institucionales (nombre, email, teléfono)

---

## 🐛 Troubleshooting

### Error: Base de datos no conecta
- Verificar credenciales en `.env`
- Verificar que MySQL esté corriendo
- Verificar permisos de usuario DB

### Error: Sesión no inicia
- Verificar permisos de carpeta `/logs`
- Verificar `session.save_path` en php.ini
- Limpiar cookies del navegador

### Error: No se ven estilos CSS
- Verificar ruta de `APP_URL` en `.env`
- Verificar que `.htaccess` esté activo
- Limpiar caché del navegador

### Error 403 en APIs
- Verificar autenticación de sesión
- Verificar tipo de usuario (permisos)
- Revisar logs en `/logs`

---

## 📞 Soporte y Contacto

**Universidad Maya**
- Email: soporte@universidadmaya.edu
- Teléfono: +52 999 000 0000
- Horario: Lunes a Viernes, 8:00 AM - 6:00 PM

**Desarrolladores**
- Para reportar bugs o solicitar funcionalidades
- Documentar paso a paso el problema
- Incluir capturas de pantalla si aplica

---

## 📜 Licencia y Derechos

**© 2026 Universidad Maya - Todos los derechos reservados**

Este sistema es propiedad exclusiva de la Universidad Maya. 
Prohibida su reproducción, distribución o uso no autorizado.

**Software desarrollado específicamente para:**
- Universidad Maya
- Gestión de horarios académicos
- Optimización de carga docente

---

## 🔄 Historial de Versiones

### v2.0 (Enero 2026)
- ✅ Rol de Director implementado
- ✅ Gestión de administradores
- ✅ Selección autónoma de materias por docentes
- ✅ Prioridad por antigüedad
- ✅ Optimización de base de datos
- ✅ Copyright y branding Universidad Maya
- ✅ Mejoras en APIs y frontend

### v1.0 (Baseline)
- Sistema básico de horarios
- Roles: Administrador, Docente
- CRUD de entidades principales
- Generación de horarios

---

## 🎯 Roadmap Futuro

- [ ] Notificaciones por email
- [ ] Dashboard con gráficas
- [ ] Exportación de horarios (PDF, Excel)
- [ ] App móvil para docentes
- [ ] Integración con sistema de nómina
- [ ] Reportes avanzados con BI
- [ ] API REST pública (para integraciones)

---

## 🙏 Agradecimientos

Al equipo de la Universidad Maya por confiar en este sistema.
A los docentes y administradores por su feedback.
