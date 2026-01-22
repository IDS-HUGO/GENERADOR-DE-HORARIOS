<?php
require_once '../../src/config.php';

if (!isAuthenticated() || getCurrentUser()['tipo_usuario'] !== 'administrador') {
    redirect(baseUrl('/public/index.html'));
}

$user = getCurrentUser();
$section = $_GET['section'] ?? 'dashboard';
$valid_sections = ['dashboard', 'docentes', 'programas', 'materias', 'grupos'];
if (!in_array($section, $valid_sections)) $section = 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - ClassControl</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">ClassControl Admin</div>
            <ul class="navbar-menu">
                <li><a href="?section=dashboard" class="nav-link <?php echo $section === 'dashboard' ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="?section=docentes" class="nav-link <?php echo $section === 'docentes' ? 'active' : ''; ?>">Docentes</a></li>
                <li><a href="?section=programas" class="nav-link <?php echo $section === 'programas' ? 'active' : ''; ?>">Programas</a></li>
                <li><a href="?section=materias" class="nav-link <?php echo $section === 'materias' ? 'active' : ''; ?>">Materias</a></li>
                <li><a href="?section=grupos" class="nav-link <?php echo $section === 'grupos' ? 'active' : ''; ?>">Grupos</a></li>
            </ul>
            <div class="navbar-user">
                <span class="user-name"><?php echo htmlspecialchars($user['nombre'] ?? 'Administrador'); ?></span>
                <button class="btn btn-sm btn-secondary" id="logout-btn">Salir</button>
            </div>
        </div>
    </nav>

    <main class="container">
        <div id="alert-container"></div>
        
        <?php
        $section_file = __DIR__ . "/sections/{$section}.php";
        if (file_exists($section_file)) {
            include $section_file;
        }
        ?>
    </main>

    <script src="../assets/js/utils.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('logout-btn');
            if (btn) {
                btn.addEventListener('click', () => {
                    if (confirm('¿Desea cerrar sesión?')) {
                        window.location.href = '<?php echo baseUrl('src/api/auth/logout.php'); ?>';
                    }
                });
            }
        });

        function showAlert(type, message) {
            const container = document.getElementById('alert-container');
            if (!container) return;
            const div = document.createElement('div');
            div.className = `alert alert-${type}`;
            div.textContent = message;
            container.appendChild(div);
            setTimeout(() => div.remove(), 3000);
        }

        function htmlEscape(text) {
            if (!text) return '';
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>
