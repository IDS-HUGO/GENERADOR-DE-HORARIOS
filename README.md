# 🎓 ClassControl - Sistema de Gestión de Horarios Académicos

Sistema profesional para la gestión integrada de horarios, docentes, materias y disponibilidades en instituciones educativas.

---

## 📋 Tabla de Contenidos

- [Características](#características)
- [Requisitos del Sistema](#requisitos-del-sistema)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Uso del Sistema](#uso-del-sistema)
- [Módulos Principales](#módulos-principales)
- [Roles de Usuario](#roles-de-usuario)
- [Solución de Problemas](#solución-de-problemas)

---

## ✨ Características

### 🔐 Seguridad
- Autenticación robusta con encriptación de contraseñas
- Sistema de roles (Administrador, Docente)
- Gestión de sesiones seguras
- Validación de datos en cliente y servidor

### 📊 Gestión Integral
- **Docentes**: Registro, perfiles, disponibilidad horaria
- **Materias**: Creación, asignación a programas
- **Grupos**: Organización por programa y semestre
- **Horarios**: Planificación automática y manual
- **Programas Académicos**: Gestión de carreras

### 📅 Disponibilidad Horaria
- Especifica tu disponibilidad de **sábado a jueves**
- Define horarios por día de la semana
- Visualización clara de franjas horarias

### 📈 Reportes y Análisis
- Estadísticas de asignaciones
- Detección automática de conflictos
- Sugerencias de horarios óptimos
- Auditoría de cambios

---

## 🖥️ Requisitos del Sistema

### Servidor
- **PHP**: 7.4 o superior
- **MySQL**: 5.7 o superior
- **Servidor Web**: Apache o Nginx con mod_rewrite

### Cliente
- Navegador web moderno (Chrome, Firefox, Safari, Edge)
- JavaScript habilitado
- Conexión a Internet estable

### Librerías PHP
- MySQLi (incluida en PHP por defecto)
- OpenSSL (para seguridad)

---

## 🚀 Instalación

### Paso 1: Descargar el Proyecto

```bash
# Clonar o descargar el proyecto
git clone <url-del-repositorio>
cd ClassControl
```

### Paso 2: Crear la Base de Datos

```bash
# Abrir phpMyAdmin o tu gestor MySQL favorito
# Importar el archivo de base de datos
mysql -u tu_usuario -p tu_base_datos < database/ClassControl.sql
```

### Paso 3: Configurar Variables de Entorno

```bash
# Copiar el archivo de ejemplo
cp src/config.example.php src/config.php

# O crear el archivo .env en la raíz del proyecto:
cat > .env << EOF
GMAIL_EMAIL=tu-email@gmail.com
GMAIL_PASSWORD=contraseña-de-aplicación
EOF
```

**Importante**: Para usar Gmail:
1. Habilita [Verificación en dos pasos](https://myaccount.google.com/security)
2. Genera una [Contraseña de aplicación](https://myaccount.google.com/apppasswords)
3. Usa esa contraseña en `GMAIL_PASSWORD` (no tu contraseña de Gmail)

### Paso 4: Configurar Permisos

```bash
# Linux/Mac
chmod 755 public/
chmod 755 src/
chmod 755 logs/
chmod 755 database/

# Windows: No es necesario (heredar permisos)
```

### Paso 5: Verificar Instalación

Abre tu navegador y accede a:
```
http://localhost/ClassControl/public/index.html
```

---

## ⚙️ Configuración

### Archivo de Configuración (src/config.php)

```php
// Base de Datos
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'tu_usuario');
define('DB_PASSWORD', 'tu_contraseña');
define('DB_NAME', 'classcontrol');

// Email
define('MAIL_FROM_ADDRESS', 'tu-email@gmail.com');
define('MAIL_PASSWORD', 'contraseña-de-aplicación');

// Horario del Sistema
date_default_timezone_set('America/Bogota'); // Cambia tu zona horaria
```

### Configuración en .env

Si prefieres usar variables de entorno:

```env
GMAIL_EMAIL=tu-email@example.com
GMAIL_PASSWORD=contraseña-aplicacion
DB_HOST=localhost
DB_NAME=classcontrol
```

---

## 📖 Uso del Sistema

### 🔑 Inicio de Sesión

1. Accede a `http://tu-dominio/ClassControl/public/index.html`
2. Ingresa tu **email** y **contraseña**
3. Selecciona tu rol (Administrador o Docente)

**Credenciales de Administrador Por Defecto**:
```
Email: admin@universidadmaya.edu
Contraseña: admin123
```

### 👨‍💼 Como Administrador

#### Dashboard Principal
- Vista general del sistema
- Estadísticas de docentes, materias y grupos
- Acceso rápido a todas las secciones

#### Gestión de Docentes
1. Ir a **Administradores** → **Docentes**
2. **Crear Docente**: Completa nombre, email, área
3. **Editar Docente**: Modifica información del docente
4. **Eliminar Docente**: Elimina un docente del sistema
5. Los docentes recibirán un email con credenciales temporales

#### Gestión de Materias
1. Ir a **Administradores** → **Materias**
2. **Crear Materia**: 
   - Nombre, código, créditos
   - Tipo (teórica, práctica, mixta)
   - Horas por semana
   - Selecciona el programa académico

#### Gestión de Grupos
1. Ir a **Administradores** → **Grupos**
2. **Crear Grupo**:
   - Código, nombre
   - Programa y semestre
   - Jornada (diurna, nocturna)
   - Cantidad de estudiantes

#### Gestión de Horarios
1. Ir a **Administradores** → **Horarios**
2. **Crear Horario Manual**:
   - Selecciona docente, materia, grupo
   - Define día y hora
   - Asigna aula
3. **Ver Conflictos**: Sistema detenta automáticamente
4. **Obtener Sugerencias**: IA sugiere horarios óptimos

#### Reportes
- **Estado General**: Carga de docentes
- **Conflictos**: Cruces de horarios
- **Disponibilidad**: Horas disponibles vs asignadas

---

### 👨‍🏫 Como Docente

#### Dashboard Personal
- Mis materias asignadas
- Mi horario oficial (solo lectura)
- Estadísticas personales

#### Mi Disponibilidad Horaria
1. Ofrente de **Dashboard** → **Editar Perfil**
2. **Definir Disponibilidad**:
   - Especifica tu disponibilidad de **Sábado a Jueves**
   - Ingresa hora de inicio y fin para cada día
   - Ejemplo: Sábado 08:00-20:00, Domingo libre, etc.
3. **Guardar**: Tus cambios se aplican inmediatamente

#### Mis Materias Asignadas
1. Ir a **Mis Materias**
2. Visualiza todas tus materias asignadas
3. Ve detalles: créditos, horas, grupo, estudiantes
4. **Mi Horario Oficial**: Ver horario oficial completo (solo lectura)

#### Cambios de Horario
Si necesitas cambios en tu horario:
1. <strong>Contacta al administrador</strong>
2. El administrador hará los cambios necesarios
3. Recibirás confirmación por email

---

## 🔄 Disponibilidad Horaria - Detalle

### Rango de Días: Sábado a Jueves

El sistema utiliza un rango de trabajo de **6 días**:

| Día | Código | Ejemplo |
|-----|--------|---------|
| Sábado | `sabado` | Sábado 08:00-20:00 |
| Domingo | `domingo` | Domingo 08:00-20:00 |
| Lunes | `lunes` | Lunes 08:00-20:00 |
| Martes | `martes` | Martes 08:00-20:00 |
| Miércoles | `miercoles` | Miércoles 08:00-20:00 |
| Jueves | `jueves` | Jueves 08:00-20:00 |

### Horarios Recomendados

```
Jornada Diurna:  08:00 - 12:30 o 14:00 - 17:30
Jornada Nocturna: 18:00 - 21:30 o 19:00 - 22:30
```

---

## 👥 Roles de Usuario

### Administrador
- ✅ Crear y editar docentes
- ✅ Crear materias y grupos
- ✅ Asignar horarios
- ✅ Ver reportes completos
- ✅ Gestionar conflictos
- ❌ No puede editar su propia asignación

### Docente
- ✅ Ver sus materias asignadas
- ✅ Actualizar disponibilidad horaria
- ✅ Ver su horario oficial
- ✅ Ver estadísticas personales
- ❌ No puede crear ni editar horarios
- ❌ No puede crear materias

---

## 🔧 Solución de Problemas

### ❌ No puedo iniciar sesión

**Problema**: Email o contraseña incorrectos
- **Solución**: Verifica que escribas correctamente
- **Recuperación**: Contacta al administrador

**Problema**: Cuenta desactivada
- **Solución**: El administrador debe reactivarla

---

### 📧 No recibo correos con credenciales

**Causas comunes:**
1. Gmail no está configurado
   - Verifica `MAIL_FROM_ADDRESS` en `src/config.php`
   
2. Contraseña de aplicación incorrecta
   - Genera una nueva [aquí](https://myaccount.google.com/apppasswords)
   - Asegúrate de verificación en dos pasos esté activa

3. Problemas de SMTP del servidor
   - Contacta a tu proveedor de hosting
   - Algunos servidores bloquean SMTP

**Solución alternativa**:
- Las credenciales se guardan en la base de datos
- Puedes ver o compartirlas manualmente con el usuario

---

### ⏰ Los horarios no aparecen correctamente

**Solución**:
1. Limpia caché del navegador (Ctrl+Shift+Del)
2. Recarga la página (F5)
3. Verifica que el navegador tenga JavaScript habilitado

---

### 🚫 Error "Base de datos no conecta"

**Verificar:**
1. MySQL está corriendo
2. Credenciales en `src/config.php` son correctas
3. Nombre de base de datos existe
4. Usuario MySQL tiene permisos

---

### 🔐 Olvide contraseña de administrador

**Reset Manual** (requiere acceso a base de datos):

```sql
-- Generar nueva contraseña hash
-- password.php: password_hash('nueva_password', PASSWORD_BCRYPT, ['cost' => 10])

UPDATE usuarios 
SET contrasena = '$2y$10$...(tu_hash_aqui)...'
WHERE email = 'admin@universidadmaya.edu';
```

---

## 📁 Estructura del Proyecto

```
ClassControl/
├── public/                 # Archivos públicos (HTML, CSS, JS)
│   ├── index.html         # Página de login
│   ├── admin/             # Panel administrativo
│   ├── docente/           # Panel de docentes
│   └── assets/            # CSS, JS, imágenes
│
├── src/                   # Código fuente PHP
│   ├── api/               # Endpoints de la API
│   ├── models/            # Modelos de datos
│   ├── config.php         # Configuración
│   └── includes/          # Servicios reutilizables
│
├── database/              # Base de datos
│   └── ClassControl.sql   # SQL de inicialización
│
├── logs/                  # Archivos de log
├── .env                   # Variables de entorno (NO subir a Github)
└── README.md             # Este archivo
```

---

## 🔗 Endpoints de la API

### Autenticación
```
POST   /src/api/auth/login.php           Login
POST   /src/api/auth/logout.php          Logout
POST   /src/api/auth/register.php        Registrar usuario
```

### Docentes
```
GET    /src/api/docentes.php             Listar docentes
POST   /src/api/docentes.php             Crear docente
PUT    /src/api/docentes.php             Editar docente
DELETE /src/api/docentes.php             Eliminar docente
```

### Disponibilidad
```
GET    /src/api/disponibilidad.php?action=list      Ver disponibilidad
POST   /src/api/disponibilidad.php?action=update    Actualizar disponibilidad
```

### Materias, Grupos, Horarios
```
GET    /src/api/materias.php             Listar materias
POST   /src/api/materias.php             Crear materia
GET    /src/api/grupos.php               Listar grupos
GET    /src/api/horarios.php             Listar horarios
```

---

## 📞 Soporte y Contacto

- **Documentación Técnica**: Ver `docs/` (si existe)
- **Reportar Errores**: Crear issue en el repositorio
- **Solicitudes de Funciones**: Discutir en el equipo

---

## 📄 Licencia

© 2026 Universidad Maya - Todos los derechos reservados

Sistema desarrollado para uso exclusivo de la institución.

---

## 🎯 Próximas Mejoras Planeadas

- [ ] Integración con calendario Google/Outlook
- [ ] Notificaciones por SMS
- [ ] Exportación de horarios a PDF
- [ ] Aplicación móvil
- [ ] Dashboard con gráficos avanzados
- [ ] Importación masiva de docentes

---

**Última actualización**: Febrero 2026
**Versión**: 1.0
