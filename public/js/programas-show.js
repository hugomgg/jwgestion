function initProgramasShow(programaId) {
    // Cargar partes de la primera sección (TB)
    loadPartes(programaId);

    // Cargar partes de la segunda sección (Escuela SP)
    loadPartesSegundaSeccion(programaId, false);

    // Cargar partes de la tercera sección (Escuela S1)
    loadPartesSegundaSeccion(programaId, true);

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
                response.data.forEach(function(parte) {
                    // Manejar diferentes estructuras de respuesta
                    const encargadoNombre = parte.encargado?.name || parte.encargado_nombre || '-';

                    html += `
                        <tr>
                            <td>${parte.tiempo || '-'}</td>
                            <td>${parte.parte_nombre || parte.parte_abreviacion || '-'}</td>
                            <td>${encargadoNombre}</td>
                            <td>${parte.tema || '-'}</td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="4" class="text-center">No hay asignaciones</td></tr>';
            }
            $('#partesTableBody').html(html);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar partes TB:', error);
            $('#partesTableBody').html('<tr><td colspan="4" class="text-center text-danger">Error al cargar los datos</td></tr>');
        }
    });
}

function loadPartesSegundaSeccion(programaId, isTerceraSeccion) {
    const tableBody = isTerceraSeccion ? '#partesTerceraSeccionTableBody' : '#partesSegundaSeccionTableBody';
    const url = isTerceraSeccion
        ? '/programas/' + programaId + '/partes-tercera-seccion'
        : '/programas/' + programaId + '/partes-segunda-seccion';

    $.ajax({
        url: url,
        method: 'GET',
        data: isTerceraSeccion ? {} : { is_tercera_seccion: isTerceraSeccion },
        success: function(response) {
            let html = '';
            if (response.success && response.data && response.data.length > 0) {
                response.data.forEach(function(parte) {
                    // Manejar diferentes estructuras de respuesta
                    const encargadoNombre = parte.encargado?.name || parte.encargado_nombre || '-';
                    const ayudanteNombre = parte.ayudante?.name || parte.ayudante_nombre || '-';

                    html += `
                        <tr>
                            <td>${parte.tiempo || '-'}</td>
                            <td>${parte.parte_nombre || parte.parte_abreviacion || '-'}</td>
                            <td>${encargadoNombre}</td>
                            <td>${ayudanteNombre}</td>
                            <td>${parte.leccion || '-'}</td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="5" class="text-center">No hay asignaciones</td></tr>';
            }
            $(tableBody).html(html);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar partes segunda sección:', error);
            $(tableBody).html('<tr><td colspan="5" class="text-center text-danger">Error al cargar los datos</td></tr>');
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
                response.data.forEach(function(parte) {
                    // Manejar diferentes estructuras de respuesta
                    const encargadoNombre = parte.encargado?.name || parte.encargado_nombre || '-';

                    html += `
                        <tr>
                            <td>${parte.tiempo || '-'}</td>
                            <td>${parte.parte_nombre || parte.parte_abreviacion || '-'}</td>
                            <td>${encargadoNombre}</td>
                            <td>${parte.tema || '-'}</td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="4" class="text-center">No hay asignaciones</td></tr>';
            }
            $('#partesNVTableBody').html(html);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar partes NVC:', error);
            $('#partesNVTableBody').html('<tr><td colspan="4" class="text-center text-danger">Error al cargar los datos</td></tr>');
        }
    });
}