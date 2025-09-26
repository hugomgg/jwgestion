function initProgramasShow(programaId) {
    // Cargar partes de la primera sección (TB)
    loadPartes(programaId);

    // Cargar partes de la segunda sección (Escuela)
    loadPartesSegundaSeccion(programaId);

    // Cargar partes de NVC
    loadPartesNV(programaId);
}

function loadPartes(programaId) {
    $.ajax({
        url: '/programas/' + programaId + '/partes',
        method: 'GET',
        success: function(response) {
            let html = '';
            if (response.success && response.data && response.data.length > 0) {
                response.data.forEach(function(parte, index) {
                    const numero = parte.numero; // Número incremental empezando desde 1
                    // Manejar diferentes estructuras de respuesta
                    const encargadoNombre = parte.encargado?.name || parte.encargado_nombre || '-';

                    html += `
                        <tr>
                            <td>${numero}</td>
                            <td>${parte.tiempo || '-'}</td>
                            <td>${parte.parte_nombre || parte.parte_abreviacion || '-'}</td>
                            <td>${encargadoNombre}</td>
                            <td>${parte.tema || '-'}</td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="5" class="text-center">No hay asignaciones</td></tr>';
            }
            $('#partesTableBody').html(html);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar partes TB:', error);
            $('#partesTableBody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar los datos</td></tr>');
        }
    });
}

function loadPartesSegundaSeccion(programaId) {
    const tableBody = '#partesSegundaSeccionTableBody';
    const url = '/programas/' + programaId + '/partes-segunda-seccion';

    $.ajax({
        url: url,
        method: 'GET',
        success: function(response) {
            let html = '';
            if (response.success && response.data && response.data.length > 0) {
                response.data.forEach(function(parte, index) {
                    const numero = parte.numero; // Número incremental empezando desde 1
                    // Manejar diferentes estructuras de respuesta
                    const encargadoNombre = parte.encargado?.name || parte.encargado_nombre || '-';
                    const ayudanteNombre = parte.ayudante?.name || parte.ayudante_nombre || '-';

                    // Crear badge para la sala
                    let salaBadge = '';
                    if (parte.sala_abreviacion === 'SP') {
                        salaBadge = `<span class="badge bg-primary">${parte.sala_abreviacion}</span>`;
                    } else if (parte.sala_abreviacion === 'S1') {
                        salaBadge = `<span class="badge bg-warning">${parte.sala_abreviacion}</span>`;
                    } else if (parte.sala_abreviacion === 'S2') {
                        salaBadge = `<span class="badge bg-success">${parte.sala_abreviacion}</span>`;
                    } else {
                        salaBadge = '<span class="badge bg-secondary">-</span>';
                    }

                    html += `
                        <tr>
                            <td>${numero}</td>
                            <td>${salaBadge}</td>
                            <td>${parte.tiempo || '-'}</td>
                            <td>${parte.parte_nombre || parte.parte_abreviacion || '-'}</td>
                            <td>${encargadoNombre}</td>
                            <td>${ayudanteNombre}</td>
                            <td>${parte.leccion || '-'}</td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="7" class="text-center">No hay asignaciones</td></tr>';
            }
            $(tableBody).html(html);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar partes segunda sección:', error);
            $(tableBody).html('<tr><td colspan="7" class="text-center text-danger">Error al cargar los datos</td></tr>');
        }
    });
}

function loadPartesNV(programaId) {
    $.ajax({
        url: '/programas/' + programaId + '/partes-nv',
        method: 'GET',
        success: function(response) {
            let html = '';
            if (response.success && response.data && response.data.length > 0) {
                response.data.forEach(function(parte, index) {
                    const numero = parte.numero; // Número incremental empezando desde 1
                    // Manejar diferentes estructuras de respuesta
                    const encargadoNombre = parte.encargado?.name || parte.encargado_nombre || '-';

                    html += `
                        <tr>
                            <td>${numero}</td>
                            <td>${parte.tiempo || '-'}</td>
                            <td>${parte.parte_nombre || parte.parte_abreviacion || '-'}</td>
                            <td>${encargadoNombre}</td>
                            <td>${parte.tema || '-'}</td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="5" class="text-center">No hay asignaciones</td></tr>';
            }
            $('#partesNVTableBody').html(html);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar partes NVC:', error);
            $('#partesNVTableBody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar los datos</td></tr>');
        }
    });
}