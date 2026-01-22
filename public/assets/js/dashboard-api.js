/**
 * Dashboard API - Funciones para cargar datos del dashboard
 */

// Cargar docentes
async function loadDocentes() {
    try {
        console.log('[DOCENTES] Cargando...');
        const response = await api('/src/api/docentes.php?action=list', {
            method: 'GET'
        });

        if (response.success && response.data) {
            console.log('[DOCENTES] Cargados:', response.data.length);
            return response.data;
        } else {
            showAlert('❌ Error: ' + (response.message || 'No se pudo cargar docentes'), 'danger');
            return [];
        }
    } catch (error) {
        console.error('[DOCENTES ERROR]', error);
        showAlert('❌ Error cargando docentes: ' + error.message, 'danger');
        return [];
    }
}

// Cargar programas
async function loadProgramas() {
    try {
        console.log('[PROGRAMAS] Cargando...');
        const response = await api('/src/api/programas.php?action=list', {
            method: 'GET'
        });

        if (response.success && response.data) {
            console.log('[PROGRAMAS] Cargados:', response.data.length);
            return response.data;
        } else {
            showAlert('❌ Error: ' + (response.message || 'No se pudo cargar programas'), 'danger');
            return [];
        }
    } catch (error) {
        console.error('[PROGRAMAS ERROR]', error);
        showAlert('❌ Error cargando programas: ' + error.message, 'danger');
        return [];
    }
}

// Cargar materias
async function loadMaterias() {
    try {
        console.log('[MATERIAS] Cargando...');
        const response = await api('/src/api/materias.php?action=list', {
            method: 'GET'
        });

        if (response.success && response.data) {
            console.log('[MATERIAS] Cargadas:', response.data.length);
            return response.data;
        } else {
            showAlert('❌ Error: ' + (response.message || 'No se pudo cargar materias'), 'danger');
            return [];
        }
    } catch (error) {
        console.error('[MATERIAS ERROR]', error);
        showAlert('❌ Error cargando materias: ' + error.message, 'danger');
        return [];
    }
}

// Cargar grupos
async function loadGrupos() {
    try {
        console.log('[GRUPOS] Cargando...');
        const response = await api('/src/api/grupos.php?action=list', {
            method: 'GET'
        });

        if (response.success && response.data) {
            console.log('[GRUPOS] Cargados:', response.data.length);
            return response.data;
        } else {
            showAlert('❌ Error: ' + (response.message || 'No se pudo cargar grupos'), 'danger');
            return [];
        }
    } catch (error) {
        console.error('[GRUPOS ERROR]', error);
        showAlert('❌ Error cargando grupos: ' + error.message, 'danger');
        return [];
    }
}

// Crear docente
async function createDocente(formData) {
    try {
        const response = await api('/src/api/docentes.php?action=create', {
            method: 'POST',
            body: formData
        });

        if (response.success) {
            showAlert('✅ Docente creado exitosamente', 'success', 2000);
            closeModal('modal-crear-docente');
            clearForm('form-crear-docente');
            return response.data;
        } else {
            showAlert('❌ Error: ' + (response.message || 'No se pudo crear docente'), 'danger');
            return null;
        }
    } catch (error) {
        console.error('[CREATE DOCENTE ERROR]', error);
        showAlert('❌ Error: ' + error.message, 'danger');
        return null;
    }
}

// Crear programa
async function createPrograma(formData) {
    try {
        const response = await api('/src/api/programas.php?action=create', {
            method: 'POST',
            body: formData
        });

        if (response.success) {
            showAlert('✅ Programa creado exitosamente', 'success', 2000);
            closeModal('modal-crear-programa');
            clearForm('form-crear-programa');
            return response.data;
        } else {
            showAlert('❌ Error: ' + (response.message || 'No se pudo crear programa'), 'danger');
            return null;
        }
    } catch (error) {
        console.error('[CREATE PROGRAMA ERROR]', error);
        showAlert('❌ Error: ' + error.message, 'danger');
        return null;
    }
}

// Crear materia
async function createMateria(formData) {
    try {
        const response = await api('/src/api/materias.php?action=create', {
            method: 'POST',
            body: formData
        });

        if (response.success) {
            showAlert('✅ Materia creada exitosamente', 'success', 2000);
            closeModal('modal-crear-materia');
            clearForm('form-crear-materia');
            return response.data;
        } else {
            showAlert('❌ Error: ' + (response.message || 'No se pudo crear materia'), 'danger');
            return null;
        }
    } catch (error) {
        console.error('[CREATE MATERIA ERROR]', error);
        showAlert('❌ Error: ' + error.message, 'danger');
        return null;
    }
}

console.log('[DASHBOARD-API] Funciones cargadas');
