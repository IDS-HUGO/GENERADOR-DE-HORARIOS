<?php
// Test para verificar que todas las clases se cargan correctamente
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<h1>✓ Test de Carga de Clases</h1>";

// Verificar cada clase
$classes = ['Usuario', 'Docente', 'Materia', 'Grupo', 'ProgramaAcademico', 'Asignacion', 'Horario', 'DisponibilidadHoraria'];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✅ $class: OK<br>";
    } else {
        echo "❌ $class: FALTA<br>";
    }
}

echo "<hr>";
echo "Base de datos: ";

try {
    $db = getDatabase();
    $result = $db->query("SELECT COUNT(*) as usuarios FROM usuarios");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ Conectada. Usuarios: " . $row['usuarios'] . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
