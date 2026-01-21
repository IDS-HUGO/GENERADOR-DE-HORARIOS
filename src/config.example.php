<?php
// config.example.php
// Este es un archivo EJEMPLO. Copia este archivo a config.php y actualiza con tus valores reales.
// NUNCA subes config.php a GitHub o repositorios públicos.

// ====== CONFIGURACIÓN DE BASE DE DATOS ======
// Hostinger: Obtén estos valores de tu panel cPanel

define('DB_HOST', 'localhost');           // Servidor MySQL (normalmente localhost)
define('DB_USERNAME', 'tu_usuario');      // Usuario MySQL que creaste
define('DB_PASSWORD', 'tu_contraseña');   // Contraseña del usuario
define('DB_NAME', 'tu_base_de_datos');    // Nombre de la base de datos
define('DB_CHARSET', 'utf8mb4');

// ====== CONFIGURACIÓN DE SESIONES ======
define('SESSION_LIFETIME', 8 * 60 * 60);  // Durabilidad de sesión en segundos (8 horas)

// ====== CONFIGURACIÓN DE SEGURIDAD ======
// IMPORTANTE: Cambiar a true cuando depliegues a producción (Hostinger)
define('PRODUCTION', false);

// ====== CONFIGURACIÓN DE RUTAS ======
define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', __DIR__);
define('PUBLIC_PATH', dirname(__DIR__) . '/public');
define('LOG_PATH', dirname(__DIR__) . '/logs');

// El resto de las funciones se encuentran en config.php
