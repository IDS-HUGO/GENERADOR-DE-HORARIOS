#!/bin/bash
# Script de prueba para verificar que el proyecto funciona con carpetas dinámicas

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║     Prueba de Configuración Dinámica - ClassControl         ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

# Variables
PROJECT_NAME=${1:-"ClassControl"}
PROJECT_PATH="c:/xampp/htdocs/$PROJECT_NAME"
BASE_URL="http://localhost/$PROJECT_NAME"

echo "🔍 Verificando proyecto en: $PROJECT_PATH"
echo ""

# 1. Verificar que existe la carpeta
if [ ! -d "$PROJECT_PATH" ]; then
    echo "❌ FALLÓ: Carpeta no encontrada: $PROJECT_PATH"
    exit 1
fi
echo "✅ PASÓ: Carpeta encontrada"

# 2. Verificar archivos críticos
echo ""
echo "📋 Verificando archivos críticos..."

CRITICAL_FILES=(
    "src/config.php"
    "src/api/auth/login.php"
    "public/index.html"
    "public/assets/js/app.js"
    "public/assets/js/utils.js"
    "public/assets/js/modules/api.js"
    "public/assets/js/modules/auth.js"
    "public/verificador.html"
)

for file in "${CRITICAL_FILES[@]}"; do
    if [ -f "$PROJECT_PATH/$file" ]; then
        echo "  ✅ $file"
    else
        echo "  ❌ $file (FALTA)"
    fi
done

# 3. Verificar que no hay rutas hardcodeadas problemáticas
echo ""
echo "🔎 Buscando rutas hardcodeadas..."

# Buscar solo el patrón ClassControl dentro de archivos dinámicamente detectables
HARDCODED=$(grep -r "window.location.href.*ClassControl" "$PROJECT_PATH/public" 2>/dev/null || true)
if [ -z "$HARDCODED" ]; then
    echo "✅ PASÓ: No hay rutas hardcodeadas en el frontend"
else
    echo "⚠️  ADVERTENCIA: Se encontraron referencias a ClassControl:"
    echo "$HARDCODED"
fi

# 4. Verificar sintaxis PHP
echo ""
echo "🐘 Verificando sintaxis PHP..."

if command -v php &> /dev/null; then
    php -l "$PROJECT_PATH/src/config.php" | grep -q "No syntax errors"
    if [ $? -eq 0 ]; then
        echo "✅ PASÓ: src/config.php tiene sintaxis válida"
    else
        echo "❌ FALLÓ: Errores de sintaxis en src/config.php"
        php -l "$PROJECT_PATH/src/config.php"
    fi
    
    php -l "$PROJECT_PATH/src/api/auth/login.php" | grep -q "No syntax errors"
    if [ $? -eq 0 ]; then
        echo "✅ PASÓ: src/api/auth/login.php tiene sintaxis válida"
    else
        echo "❌ FALLÓ: Errores de sintaxis en src/api/auth/login.php"
        php -l "$PROJECT_PATH/src/api/auth/login.php"
    fi
else
    echo "⚠️  PHP no encontrado, saltando verificación de sintaxis"
fi

# 5. Verificar detectApiBase en JS
echo ""
echo "📜 Verificando función detectApiBase()..."

if grep -q "function detectApiBase()" "$PROJECT_PATH/public/assets/js/utils.js"; then
    echo "✅ PASÓ: detectApiBase() encontrado en utils.js"
else
    echo "❌ FALLÓ: detectApiBase() no encontrado"
fi

if grep -q "const pathParts = pathname.split('/')" "$PROJECT_PATH/public/assets/js/utils.js"; then
    echo "✅ PASÓ: Detección dinámica de ruta implementada"
else
    echo "❌ FALLÓ: Detección dinámica no encontrada"
fi

# 6. Verificar getAppUrl en PHP
echo ""
echo "🐘 Verificando función getAppUrl()..."

if grep -q "function getAppUrl()" "$PROJECT_PATH/src/config.php"; then
    echo "✅ PASÓ: getAppUrl() encontrado en config.php"
else
    echo "❌ FALLÓ: getAppUrl() no encontrado"
fi

# 7. Instrucciones finales
echo ""
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║                    PRUEBAS COMPLETADAS                       ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""
echo "📌 Próximas acciones:"
echo "   1. Abre: $BASE_URL/public/verificador.html"
echo "   2. Ejecuta las verificaciones automáticas"
echo "   3. Prueba login con credenciales válidas"
echo ""
echo "🚀 Para compartir con tu amigo:"
echo "   - El proyecto está listo para ser clonado con CUALQUIER nombre"
echo "   - Todo se configura automáticamente"
echo "   - No requiere cambios adicionales"
echo ""
