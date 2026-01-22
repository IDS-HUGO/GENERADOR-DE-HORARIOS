# Script de Verificación Rápida para Carpeta Dinámica - ClassControl
# Uso: PowerShell .\test-setup.ps1

Write-Host "`n╔════════════════════════════════════════════════════════════╗`n" -ForegroundColor Cyan
Write-Host "  Verificador de Configuración Dinámica - ClassControl" -ForegroundColor Cyan
Write-Host "`n╚════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

# Detectar carpeta del proyecto
$ProjectPath = (Get-Location).Path
$ProjectName = Split-Path -Leaf $ProjectPath
$BaseURL = "http://localhost/$ProjectName"

Write-Host "📍 Ubicación: $ProjectPath" -ForegroundColor Yellow
Write-Host "📍 Carpeta: $ProjectName" -ForegroundColor Yellow
Write-Host "📍 Base URL: $BaseURL`n" -ForegroundColor Yellow

# 1. Verificar estructura de carpetas
Write-Host "🔍 Verificando estructura de carpetas..." -ForegroundColor Cyan

$requiredDirs = @(
    "src",
    "src/api",
    "src/api/auth",
    "src/models",
    "src/includes",
    "public",
    "public/assets",
    "public/assets/js",
    "public/assets/js/modules",
    "public/assets/css",
    "database",
    "logs"
)

$dirsOK = 0
foreach ($dir in $requiredDirs) {
    $path = Join-Path $ProjectPath $dir
    if (Test-Path $path) {
        Write-Host "  ✅ $dir" -ForegroundColor Green
        $dirsOK++
    } else {
        Write-Host "  ❌ $dir (FALTA)" -ForegroundColor Red
    }
}

# 2. Verificar archivos críticos
Write-Host "`n🔍 Verificando archivos críticos..." -ForegroundColor Cyan

$requiredFiles = @(
    "src/config.php",
    "src/api/auth/login.php",
    "src/models/Usuario.php",
    "public/index.html",
    "public/assets/js/app.js",
    "public/assets/js/utils.js",
    "public/assets/js/modules/api.js",
    "public/assets/js/modules/auth.js",
    "public/assets/css/style.css",
    "public/verificador.html",
    "database/ClassControl.sql"
)

$filesOK = 0
foreach ($file in $requiredFiles) {
    $path = Join-Path $ProjectPath $file
    if (Test-Path $path) {
        Write-Host "  ✅ $file" -ForegroundColor Green
        $filesOK++
    } else {
        Write-Host "  ⚠️  $file (No encontrado)" -ForegroundColor Yellow
    }
}

# 3. Verificar configuración dinámicas
Write-Host "`n🔍 Verificando configuración dinámica..." -ForegroundColor Cyan

$configFile = Join-Path $ProjectPath "src/config.php"
if (Test-Path $configFile) {
    $configContent = Get-Content $configFile -Raw
    
    if ($configContent -match "function getAppUrl") {
        Write-Host "  ✅ getAppUrl() en config.php" -ForegroundColor Green
    } else {
        Write-Host "  ❌ getAppUrl() NO encontrado" -ForegroundColor Red
    }
    
    if ($configContent -match "APP_URL.*getAppUrl") {
        Write-Host "  ✅ APP_URL dinámico configurado" -ForegroundColor Green
    } else {
        Write-Host "  ❌ APP_URL NO es dinámico" -ForegroundColor Red
    }
}

$loginFile = Join-Path $ProjectPath "src/api/auth/login.php"
if (Test-Path $loginFile) {
    $loginContent = Get-Content $loginFile -Raw
    
    if ($loginContent -match 'APP_URL') {
        Write-Host "  ✅ login.php usa APP_URL" -ForegroundColor Green
    } else {
        Write-Host "  ⚠️  login.php no usa APP_URL" -ForegroundColor Yellow
    }
}

# 4. Verificar JavaScript dinámico
Write-Host "`n🔍 Verificando JavaScript dinámico..." -ForegroundColor Cyan

$utilsFile = Join-Path $ProjectPath "public/assets/js/utils.js"
if (Test-Path $utilsFile) {
    $utilsContent = Get-Content $utilsFile -Raw
    
    if ($utilsContent -match "detectApiBase") {
        Write-Host "  ✅ detectApiBase() en utils.js" -ForegroundColor Green
    } else {
        Write-Host "  ❌ detectApiBase() NO encontrado" -ForegroundColor Red
    }
    
    if ($utilsContent -match "pathParts.*pathname.split") {
        Write-Host "  ✅ Detección de rutas dinámica" -ForegroundColor Green
    } else {
        Write-Host "  ❌ Detección de rutas NO dinámica" -ForegroundColor Red
    }
}

$appFile = Join-Path $ProjectPath "public/assets/js/app.js"
if (Test-Path $appFile) {
    $appContent = Get-Content $appFile -Raw
    
    if ($appContent -match "projectFolder") {
        Write-Host "  ✅ Carga dinámica de utils.js" -ForegroundColor Green
    } else {
        Write-Host "  ⚠️  Carga de utils.js no dinámmica" -ForegroundColor Yellow
    }
}

# 5. Resumen
Write-Host "`n╔════════════════════════════════════════════════════════════╗`n" -ForegroundColor Cyan

if ($dirsOK -eq $requiredDirs.Count -and $filesOK -ge ($requiredFiles.Count - 1)) {
    Write-Host "✅ CONFIGURACIÓN CORRECTA" -ForegroundColor Green
    Write-Host "`nEl proyecto está listo. Accede a:`n" -ForegroundColor Green
    Write-Host "🌐 $BaseURL/public/index.html`n" -ForegroundColor Cyan
    Write-Host "O verifica la configuración en:`n" -ForegroundColor Green
    Write-Host "🔍 $BaseURL/public/verificador.html`n" -ForegroundColor Cyan
} else {
    Write-Host "⚠️  VERIFICACIÓN INCOMPLETA" -ForegroundColor Yellow
    Write-Host "`nAlguno archivo o carpeta no se encontró`n" -ForegroundColor Yellow
    Write-Host "Directorios: $dirsOK/$($requiredDirs.Count) ✅" -ForegroundColor Cyan
    Write-Host "Archivos: $filesOK/$($requiredFiles.Count) ✅`n" -ForegroundColor Cyan
}

Write-Host "`n💡 Próximos pasos:`n" -ForegroundColor Yellow
Write-Host "  1. Abre: $BaseURL/public/verificador.html`n" -ForegroundColor Cyan
Write-Host "  2. Ejecuta las verificaciones automáticas`n" -ForegroundColor Cyan
Write-Host "  3. Prueba login con credenciales válidas`n" -ForegroundColor Cyan

Write-Host "╚════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan
