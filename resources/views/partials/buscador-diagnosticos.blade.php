<!-- Modal Buscador de Diagnósticos -->
<div class="modal fade" id="modalBuscadorDiagnosticos" tabindex="-1" role="dialog" aria-labelledby="modalBuscadorDiagnosticosLabel" aria-hidden="true" style="z-index: 9999 !important;">
    <div class="modal-dialog modal-lg" role="document" style="z-index: 10000 !important;">
        <div class="modal-content">
                            <div class="modal-header">
                <h5 class="modal-title" id="modalBuscadorDiagnosticosLabel">
                    <i class="fas fa-list"></i> Consulta de Diagnósticos
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Campo de búsqueda -->
                <div class="form-group">
                    <label for="busquedaDiagnostico">Buscar por código, patología o categoría:</label>
                    <div class="input-group">
                        <input type="text" 
                               id="busquedaDiagnostico" 
                               class="form-control" 
                               placeholder="Buscar diagnóstico... (filtrado en tiempo real)"
                               autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btnLimpiarBusqueda" title="Limpiar búsqueda">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted">
                        <span id="contadorResultados">Cargando diagnósticos...</span>
                    </small>
                </div>

                <!-- Tabla de resultados -->
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover table-bordered" id="tablaDiagnosticos">
                        <thead class="thead-light sticky-top">
                            <tr>
                                <th width="15%">Código</th>
                                <th>Patología</th>
                                <th width="20%">Categoría</th>
                                <th width="20%">Secundario</th>
                            </tr>
                        </thead>
                        <tbody id="resultadosDiagnosticos">
                            <!-- Los resultados se cargarán aquí dinámicamente -->
                        </tbody>
                    </table>
                </div>

                <!-- Indicador de carga -->
                <div id="loadingDiagnosticos" class="text-center" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Buscando diagnósticos...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para el buscador -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Función para abrir el modal (independiente del formulario)
    window.abrirBuscadorDiagnosticos = function(inputElement) {
        // El inputElement se ignora - este modal es independiente
        
        // Título fijo para consulta
        $('#modalBuscadorDiagnosticosLabel').html('<i class="fas fa-search"></i> Buscador de Diagnósticos');
        
        $('#modalBuscadorDiagnosticos').modal('show');
        
        // Limpiar búsqueda anterior
        document.getElementById('busquedaDiagnostico').value = '';
        
        // Cargar todos los diagnósticos al abrir el modal
        cargarTodosLosDiagnosticos();
    };
    
    // Función para cargar todos los diagnósticos
    function cargarTodosLosDiagnosticos() {
        const loadingDiv = document.getElementById('loadingDiagnosticos');
        const resultadosDiv = document.getElementById('resultadosDiagnosticos');
        
        // Mostrar indicador de carga
        loadingDiv.style.display = 'block';
        resultadosDiv.innerHTML = '';
        
        // Realizar petición AJAX para obtener todos los diagnósticos
        fetch(`{{ route('diagnosticos.buscar') }}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                loadingDiv.style.display = 'none';
                
                if (data.length === 0) {
                    resultadosDiv.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-info">
                                <i class="fas fa-info-circle"></i> No hay diagnósticos registrados
                            </td>
                        </tr>
                    `;
                    return;
                }
                
                // Almacenar todos los diagnósticos para filtrado
                window.todosLosDiagnosticos = data;
                
                // Mostrar todos los diagnósticos
                mostrarDiagnosticos(data);
            })
            .catch(error => {
                console.error('Error al cargar diagnósticos:', error);
                loadingDiv.style.display = 'none';
                resultadosDiv.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-danger">
                            <i class="fas fa-exclamation-circle"></i> Error al cargar diagnósticos: ${error.message}
                        </td>
                    </tr>
                `;
            });
    }
    
    // Función para mostrar diagnósticos
    function mostrarDiagnosticos(diagnosticos) {
        const resultadosDiv = document.getElementById('resultadosDiagnosticos');
        const contadorDiv = document.getElementById('contadorResultados');
        
        // Actualizar contador
        contadorDiv.textContent = `${diagnosticos.length} diagnóstico(s) encontrado(s)`;
        
        if (diagnosticos.length === 0) {
            resultadosDiv.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-info">
                        <i class="fas fa-search"></i> No se encontraron diagnósticos
                    </td>
                </tr>
            `;
            return;
        }
        
        // Mostrar resultados
        let html = '';
        const query = document.getElementById('busquedaDiagnostico').value.trim();
        
        diagnosticos.forEach(diagnostico => {
            // Resaltar texto de búsqueda si existe
            let codigoMostrar = diagnostico.codigo;
            let patologiaMostrar = diagnostico.patologia;
            let categoriaMostrar = diagnostico.categoria || 'N/A';
            
            if (query) {
                const regex = new RegExp(`(${query})`, 'gi');
                codigoMostrar = codigoMostrar.replace(regex, '<span class="highlight">$1</span>');
                patologiaMostrar = patologiaMostrar.replace(regex, '<span class="highlight">$1</span>');
                categoriaMostrar = categoriaMostrar.replace(regex, '<span class="highlight">$1</span>');
            }
            
            html += `
                <tr>
                    <td><strong>${codigoMostrar}</strong></td>
                    <td>${patologiaMostrar}</td>
                    <td><span class="badge badge-secondary">${categoriaMostrar}</span></td>
                    <td class="text-muted small">${diagnostico.secundario || 'N/A'}</td>
                </tr>
            `;
        });
        
        resultadosDiv.innerHTML = html;
    }
    
    // Función para realizar la búsqueda (ahora filtra localmente)
    function buscarDiagnosticos() {
        const query = document.getElementById('busquedaDiagnostico').value.trim().toLowerCase();
        
        // Si no hay query, mostrar todos los diagnósticos
        if (query === '') {
            if (window.todosLosDiagnosticos) {
                mostrarDiagnosticos(window.todosLosDiagnosticos);
            }
            return;
        }
        
        // Filtrar diagnósticos localmente
        if (window.todosLosDiagnosticos) {
            const diagnosticosFiltrados = window.todosLosDiagnosticos.filter(diagnostico => {
                return diagnostico.codigo.toLowerCase().includes(query) || 
                       diagnostico.patologia.toLowerCase().includes(query) ||
                       (diagnostico.categoria && diagnostico.categoria.toLowerCase().includes(query));
            });
            
            mostrarDiagnosticos(diagnosticosFiltrados);
        }
    }
    
    // Función simplificada para cerrar modal (solo consulta)
    function cerrarModal() {
        $('#modalBuscadorDiagnosticos').modal('hide');
    }
    
    // Event listeners
    document.getElementById('btnLimpiarBusqueda').addEventListener('click', function() {
        document.getElementById('busquedaDiagnostico').value = '';
        if (window.todosLosDiagnosticos) {
            mostrarDiagnosticos(window.todosLosDiagnosticos);
        }
    });
    
    // Búsqueda al presionar Enter
    document.getElementById('busquedaDiagnostico').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarDiagnosticos();
        }
    });
    
    // Búsqueda automática al escribir (con debounce)
    let timeoutBusqueda = null;
    document.getElementById('busquedaDiagnostico').addEventListener('input', function() {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(() => {
            buscarDiagnosticos();
        }, 300); // Reducido el tiempo de debounce para mejor experiencia
    });
});
</script>

<style>
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}

#modalBuscadorDiagnosticos .modal-dialog {
    max-width: 800px;
}

#contadorResultados {
    font-weight: 500;
    color: #495057;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

/* Resaltar texto de búsqueda en los resultados */
.highlight {
    background-color: #fff3cd;
    font-weight: bold;
}

/* Estilo para filas de solo lectura */
#tablaDiagnosticos tbody tr {
    background-color: #f8f9fa;
}

#tablaDiagnosticos tbody tr:nth-child(even) {
    background-color: #e9ecef;
}
</style>
