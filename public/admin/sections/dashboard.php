<section id="dashboard" class="page-section">
    <div class="page-header">
        <div>
            <h1>Panel de Control</h1>
            <p class="text-muted" id="current-date"></p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card info">
            <div class="stat-label">Docentes</div>
            <div class="stat-value" id="stat-docentes">0</div>
        </div>
        <div class="stat-card success">
            <div class="stat-label">Programas</div>
            <div class="stat-value" id="stat-programas">0</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-label">Materias</div>
            <div class="stat-value" id="stat-materias">0</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-label">Grupos</div>
            <div class="stat-value" id="stat-grupos">0</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <div class="card-header">Acciones rápidas</div>
            <div class="card-body grid-2">
                <a href="?section=docentes" class="btn btn-primary">Ir a Docentes</a>
                <a href="?section=programas" class="btn btn-primary">Ir a Programas</a>
                <a href="?section=materias" class="btn btn-primary">Ir a Materias</a>
                <a href="?section=grupos" class="btn btn-primary">Ir a Grupos</a>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Información</div>
            <div class="card-body">
                <p class="text-muted">Usa el menú superior para navegar a las diferentes secciones de gestión. Las estadísticas se actualizan automáticamente.</p>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        updateDate();
        loadDashboardStats();
    });

    function updateDate() {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateEl = document.getElementById('current-date');
        if (dateEl) dateEl.textContent = now.toLocaleDateString('es-ES', options);
    }

    async function loadDashboardStats() {
        try {
            const [docentesResp, programasResp, materiasResp, gruposResp] = await Promise.all([
                api('/src/api/docentes.php?action=list'),
                api('/src/api/programas.php?action=list'),
                api('/src/api/materias.php?action=list'),
                api('/src/api/grupos.php?action=list')
            ]);
            document.getElementById('stat-docentes').textContent = docentesResp.data?.length || 0;
            document.getElementById('stat-programas').textContent = programasResp.data?.length || 0;
            document.getElementById('stat-materias').textContent = materiasResp.data?.length || 0;
            document.getElementById('stat-grupos').textContent = gruposResp.data?.length || 0;
        } catch (e) {
            console.error('Error cargando estadísticas', e);
        }
    }
</script>
