#!/usr/bin/env php
<?php
/**
 * VALIDADOR DE CONFIGURACIÓN DINÁMICA
 * Script CLI para verificar que todas las rutas dinámicas están implementadas correctamente
 */

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║        VALIDADOR DE CONFIGURACIÓN DINÁMICA - ClassControl      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$projectPath = __DIR__;
$checks = [];

// 1. Verificar que getAppUrl() existe en config.php
echo "🔍 Verificando configuración PHP...\n";
$configContent = file_get_contents($projectPath . '/src/config.php');
if (strpos($configContent, 'function getAppUrl()') !== false) {
    echo "  ✅ getAppUrl() encontrado en config.php\n";
    $checks[] = true;
} else {
    echo "  ❌ getAppUrl() NO encontrado en config.php\n";
    $checks[] = false;
}

// 2. Verificar login.php
$loginContent = file_get_contents($projectPath . '/src/api/auth/login.php');
if (strpos($loginContent, 'APP_URL') !== false && strpos($loginContent, '$appPath') !== false) {
    echo "  ✅ APP_URL usado en login.php para redirección dinámmica\n";
    $checks[] = true;
} else {
    echo "  ❌ login.php no usa APP_URL dinámicamente\n";
    $checks[] = false;
}

// 3. Verificar utils.js
echo "\n🔍 Verificando JavaScript...\n";
$utilsContent = file_get_contents($projectPath . '/public/assets/js/utils.js');
if (strpos($utilsContent, 'pathParts = pathname.split') !== false) {
    echo "  ✅ Detección de pathParts en utils.js\n";
    $checks[] = true;
} else {
    echo "  ❌ Detección de pathParts NO encontrada\n";
    $checks[] = false;
}

// 4. Verificar app.js
$appContent = file_get_contents($projectPath . '/public/assets/js/app.js');
if (strpos($appContent, 'projectFolder') !== false && strpos($appContent, 'script.src = projectFolder') !== false) {
    echo "  ✅ Carga dinámica de utils.js en app.js\n";
    $checks[] = true;
} else {
    echo "  ❌ Carga de utils.js NO es dinámica\n";
    $checks[] = false;
}

// 5. Verificar auth.js
$authContent = file_get_contents($projectPath . '/public/assets/js/modules/auth.js');
if (strpos($authContent, 'response.redirect ||') !== false) {
    echo "  ✅ Redirección dinámica en auth.js\n";
    $checks[] = true;
} else {
    echo "  ❌ Redirección NO es dinámica\n";
    $checks[] = false;
}

// 6. Verificar api.js
$apiContent = file_get_contents($projectPath . '/public/assets/js/modules/api.js');
if (strpos($apiContent, 'function detectApiBase()') !== false && strpos($apiContent, 'API_BASE_URL') !== false) {
    echo "  ✅ detectApiBase() implementado en api.js\n";
    $checks[] = true;
} else {
    echo "  ❌ detectApiBase() NO encontrado\n";
    $checks[] = false;
}

// 7. Verificar verificador.html
echo "\n🔍 Verificando herramientas de diagnóstico...\n";
if (file_exists($projectPath . '/public/verificador.html')) {
    echo "  ✅ verificador.html existe\n";
    $checks[] = true;
} else {
    echo "  ❌ verificador.html NO encontrado\n";
    $checks[] = false;
}

// 8. Verificar documentación
if (file_exists($projectPath . '/GUIA_PARA_AMIGO.md')) {
    echo "  ✅ GUIA_PARA_AMIGO.md existe\n";
    $checks[] = true;
} else {
    echo "  ❌ GUIA_PARA_AMIGO.md NO encontrado\n";
    $checks[] = false;
}

if (file_exists($projectPath . '/CONFIGURACION_DINAMICA.md')) {
    echo "  ✅ CONFIGURACION_DINAMICA.md existe\n";
    $checks[] = true;
} else {
    echo "  ❌ CONFIGURACION_DINAMICA.md NO encontrado\n";
    $checks[] = false;
}

// 9. Buscar rutas hardcodeadas peligrosas
echo "\n🔎 Buscando rutas hardcodeadas...\n";
$dangerous = [
    'window.location.href = \'/ClassControl' => 'Frontend hardcoding',
    'script.src = \'/ClassControl' => 'Script loading hardcoding',
];

$hasDangerous = false;
foreach ($dangerous as $pattern => $desc) {
    if (strpos($utilsContent, $pattern) !== false || 
        strpos($appContent, $pattern) !== false ||
        strpos($authContent, $pattern) !== false) {
        echo "  ⚠️  $desc encontrado\n";
        $hasDangerous = true;
    }
}

if (!$hasDangerous) {
    echo "  ✅ No se encontraron rutas hardcodeadas peligrosas\n";
    $checks[] = true;
} else {
    $checks[] = false;
}

// Resultado final
echo "\n╔════════════════════════════════════════════════════════════════╗\n";
$passed = array_sum($checks);
$total = count($checks);
$percentage = ($passed / $total) * 100;

if ($percentage === 100) {
    echo "║                  ✅ TODAS LAS PRUEBAS PASARON                 ║\n";
    echo "║                                                                ║\n";
    echo "║   El proyecto está completamente configurado para            ║\n";
    echo "║   funcionar desde cualquier nombre de carpeta.               ║\n";
} elseif ($percentage >= 70) {
    echo "║                  ⚠️  PRUEBAS PARCIALES ($percentage%)              ║\n";
    echo "║                                                                ║\n";
    echo "║   Revisa los puntos marcados con ❌ anteriormente            ║\n";
} else {
    echo "║                  ❌ PRUEBAS FALLIDAS ($percentage%)               ║\n";
    echo "║                                                                ║\n";
    echo "║   Se requiere revisar la configuración                       ║\n";
}

echo "║                                                                ║\n";
echo "║  Resultado: $passed/$total verificaciones exitosas                      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Instrucciones finales
echo "📋 Próximos pasos:\n";
echo "   1. Abre http://localhost/ClassControl/public/verificador.html\n";
echo "   2. Comparte con tu amigo para que lo clone\n";
echo "   3. Tu amigo abre http://localhost/GENERADOR-DE-HORARIOS/public/verificador.html\n";
echo "   4. ¡Sin errores de rutas!\n\n";

exit($percentage === 100 ? 0 : 1);
?>
