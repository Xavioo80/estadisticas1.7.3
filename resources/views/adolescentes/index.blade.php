<x-app-layout>
    @section('title', 'Adolescentes')
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between py-2">
            <div>
                <h2 class="font-bold text-xl text-slate-900 leading-none mb-1">
                    {{ __('Registro  Adolescentes') }}
                </h2>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-[0.2em] m-0">Administración de
                    Pacientes y Expedientes</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                {{-- Filtro de Fechas --}}
                <div class="d-flex align-items-center bg-white border rounded shadow-sm px-2 gap-2"
                    style="height: 35px;">
                    <span class="text-[10px] font-bold text-muted uppercase">Desde:</span>
                    <input type="date" id="fecha-desde" class="border-0 p-0 text-sm"
                        style="width: 110px; outline: none; font-size: 0.8rem;">
                    <span class="text-[10px] font-bold text-muted uppercase">Hasta:</span>
                    <input type="date" id="fecha-hasta" class="border-0 p-0 text-sm"
                        style="width: 110px; outline: none; font-size: 0.8rem;">
                </div>

                {{-- Buscador --}}
                <div class="input-group shadow-sm" style="width: 250px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0" style="height: 35px;"><i
                                class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" id="search-input" value="{{ request('search') }}"
                        class="form-control border-left-0 border-right-0"
                        style="font-size: 0.9rem; height: 35px; border-radius: 0;" placeholder="Buscar...">
                    <div class="input-group-append">
                        <button id="btn-clear-search" class="btn btn-light border-left-0 px-2" type="button"
                            style="height: 35px;"><i class="fas fa-times text-muted"></i></button>
                    </div>
                </div>

                <a href="{{ route('adolescentes.export-excel') }}"
                    class="btn btn-success fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2"
                    style="height: 35px; font-size: 13px;">
                    <i class="fas fa-file-excel mr-2"></i> EXPORTAR
                </a>

                <button type="button"
                    class="btn btn-primary fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2"
                    data-toggle="modal" data-target="#modalNuevo" style="height: 35px; font-size: 13px;">
                    <i class="fas fa-plus mr-2"></i> NUEVO
                </button>
            </div>
        </div>
    </x-slot>

    @push('css')
        <style>
            /* ── Estilo ATA (Spreadsheet) ──────────────────────────────── */
            .ata-table-container {
                background-color: #f8fafc;
                padding: 10px;
                height: calc(100vh - 170px);
                max-height: calc(100vh - 165px);
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }

            html.dark .ata-table-container,
            html.theme-darkgray .ata-table-container,
            body.dark .ata-table-container {
                background-color: #0f172a !important;
            }

            .ata-card {
                background: white;
                border: 1px solid #cbd5e1;
                border-radius: 12px;
                flex: 1;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            html.dark .ata-card,
            html.theme-darkgray .ata-card,
            body.dark .ata-card {
                background-color: #1e293b !important;
                border-color: #334155 !important;
            }

            .ata-table-wrapper {
                flex: 1;
                overflow: auto;
            }

            .table-ata {
                font-family: 'Aptos Narrow', 'Arial Narrow', sans-serif !important;
                font-size: 14px !important;
                border-collapse: separate;
                border-spacing: 0;
                width: 100%;
            }

            .table-ata thead th {
                background: linear-gradient(to bottom, #f2f2f2, #e8e8e8) !important;
                color: #000 !important;
                font-weight: 400 !important;
                border-bottom: 2px solid #9e9e9e !important;
                border-right: 1px solid #b8b8b8 !important;
                text-align: center !important;
                height: 28px !important;
                line-height: 28px !important;
                padding: 0 8px !important;
                position: sticky;
                top: 0;
                z-index: 10;
                white-space: nowrap;
            }

            .table-ata tbody td {
                height: 24px !important;
                line-height: 24px !important;
                padding: 0 4px !important;
                border-right: 1px solid #d0d0d0 !important;
                border-bottom: 1px solid #d0d0d0 !important;
                color: #000 !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .table-ata tbody tr:nth-child(odd) td {
                background-color: #ffffff !important;
            }

            .table-ata tbody tr:nth-child(even) td {
                background-color: #f5f7fa !important;
            }

            .table-ata tbody tr:hover td {
                background-color: #fefce8 !important;
            }

            .editable:focus {
                outline: 2px solid #3b82f6 !important;
                background-color: white !important;
                z-index: 5;
            }

            .ata-select,
            .ata-input {
                width: 100%;
                height: 23px;
                border: none;
                background: transparent;
                font-family: inherit;
                font-size: inherit;
                padding: 0 2px;
                outline: none;
            }

            .ata-select:focus,
            .ata-input:focus {
                background: white;
            }

            /* Footer Estilo ATA */
            .ata-footer {
                background: white;
                border-top: 1px solid #cbd5e1;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 15px;
                font-size: 10px;
                font-weight: 800;
                color: #475569;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .badge-ata {
                background: #fee2e2;
                color: #b91c1c;
                padding: 2px 8px;
                border-radius: 4px;
                margin-left: 5px;
            }

            /* Loading Spinner */
            #infinite-scroll-loader {
                display: none;
                text-align: center;
                padding: 10px;
                background: white;
                font-weight: bold;
                font-size: 11px;
                color: #64748b;
            }
        </style>
    @endpush

    <div class="ata-table-container">
        {{-- Mensaje de éxito manejado por SweetAlert2 --}}
        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Logrado!',
                        text: "{{ session('success') }}",
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                });
            </script>
        @endif

        <div class="ata-card">
            <div class="ata-table-wrapper" id="table-wrapper">
                <table class="table-ata">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th style="width: 80px;">EXP</th>
                            <th style="min-width: 250px;">NOMBRE COMPLETO</th>
                            <th style="width: 45px;">SEXO</th>
                            <th style="width: 100px;">NACIMIENTO</th>
                            <th style="width: 100px;">INGRESO</th>
                            <th style="width: 45px;">EDAD</th>
                            <th style="width: 120px;">DNI</th>
                            <th class="text-center" style="width: 150px;">COLONIA</th>
                            <th class="text-center" style="width: 100px;">TELÉFONO</th>
                            <th class="text-center" style="width: 100px;">EST. CIVIL</th>
                            <th class="text-center" style="width: 100px;">ESCOLARIDAD</th>
                            <th class="text-center" style="width: 50px;">AÑOS</th>
                            <th class="text-center" style="width: 120px;">OCUPACIÓN</th>
                            <th class="text-center" style="width: 50px;">ESTADO</th>
                            <th class="text-center" style="width: 80px;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        @include('adolescentes.partials.table_rows')
                    </tbody>
                </table>
                <div id="infinite-scroll-loader">
                    <i class="fas fa-spinner fa-spin mr-2"></i> CARGANDO MÁS REGISTROS...
                </div>
            </div>

            <div class="ata-footer">
                <div class="d-flex align-items-center">
                    <span>REGISTROS: <span class="badge-ata" id="total-count">{{ $registros->total() }}</span></span>
                    <span class="mx-3">|</span>
                    <span>VISTA: <span class="badge-ata bg-light text-dark border"
                            id="loaded-count">{{ $registros->count() }}</span></span>
                </div>
                <div id="pagination-container" style="display: none;">
                    {{ $registros->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableWrapper = document.getElementById('table-wrapper');
            const tableBody = document.getElementById('table-body');
            const searchInput = document.getElementById('search-input');
            const fechaDesde = document.getElementById('fecha-desde');
            const fechaHasta = document.getElementById('fecha-hasta');
            const loader = document.getElementById('infinite-scroll-loader');
            const loadedCountBadge = document.getElementById('loaded-count');
            const btnClearSearch = document.getElementById('btn-clear-search');

            let currentPage = 1;
            let lastPage = {{ $registros->lastPage() }};
            let isLoading = false;
            let searchTimeout = null;

            // --- MÁSCARAS Y VALIDACIONES ---
            const maskInput = (el, maskType) => {
                el.addEventListener('input', e => {
                    let v = e.target.value.replace(/\D/g, '');
                    if (maskType === 'phone') {
                        if (v.length > 8) v = v.slice(0, 8);
                        if (v.length > 4) v = v.slice(0, 4) + '-' + v.slice(4);
                    } else if (maskType === 'dni') {
                        if (v.length > 13) v = v.slice(0, 13);
                    }
                    e.target.value = v;
                });
            };

            const dniInputs = document.querySelectorAll('input[name="numero_identidad"]');
            const phoneInputs = document.querySelectorAll('input[name="numero_telefono"]');
            dniInputs.forEach(i => maskInput(i, 'dni'));
            phoneInputs.forEach(i => maskInput(i, 'phone'));

            // Función para cargar datos
            function loadData(reset = false) {
                if (isLoading) return;
                if (reset) {
                    currentPage = 1;
                } else {
                    if (currentPage >= lastPage) return;
                    currentPage++;
                }

                isLoading = true;
                if (!reset) loader.style.display = 'block';
                if (reset) tableBody.style.opacity = '0.5';

                const params = new URLSearchParams({
                    page: currentPage,
                    search: searchInput.value,
                    fecha_desde: fechaDesde.value,
                    fecha_hasta: fechaHasta.value
                });

                fetch(`{{ route('adolescentes.index') }}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(response => response.text())
                    .then(html => {
                        if (reset) {
                            tableBody.innerHTML = html;
                            tableBody.style.opacity = '1';
                            tableWrapper.scrollTop = 0;
                        } else {
                            tableBody.insertAdjacentHTML('beforeend', html);
                        }

                        isLoading = false;
                        loader.style.display = 'none';

                        // Actualizar contador visual
                        loadedCountBadge.innerText = tableBody.querySelectorAll('tr').length;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        isLoading = false;
                        loader.style.display = 'none';
                        tableBody.style.opacity = '1';
                    });
            }

            // Infinite Scroll
            tableWrapper.addEventListener('scroll', function () {
                if (tableWrapper.scrollTop + tableWrapper.clientHeight >= tableWrapper.scrollHeight - 50) {
                    loadData();
                }
            });

            // Filtros interactivos
            const triggerSearch = () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => loadData(true), 300);
            };

            searchInput.addEventListener('input', triggerSearch);
            fechaDesde.addEventListener('change', triggerSearch);
            fechaHasta.addEventListener('change', triggerSearch);

            btnClearSearch.addEventListener('click', () => {
                searchInput.value = '';
                triggerSearch();
            });

            // Variable para almacenar el ID del adolescente a editar
            let currentEditId = null;

            // DELEGACIÓN DE EVENTOS PARA LA TABLA DINÁMICA
            document.addEventListener('click', function (e) {
                const btnEdit = e.target.closest('.btn-editar');
                if (btnEdit) {
                    e.preventDefault();
                    currentEditId = btnEdit.dataset.id;
                    console.log('Edit button clicked for ID:', currentEditId);

                    const form = document.getElementById('formEditar');
                    form.action = `/adolescentes/${currentEditId}`;

                    const loading = document.getElementById('loadingEditar');
                    const campos = document.getElementById('camposEditar');

                    loading.classList.remove('d-none');
                    campos.classList.add('opacity-50');

                    $('#modalEditar').modal('show');
                }
            });

            // Evento cuando el modal se ha mostrado completamente
            $('#modalEditar').on('shown.bs.modal', function () {
                if (currentEditId) {
                    console.log('Modal shown, loading data for ID:', currentEditId);

                    const loading = document.getElementById('loadingEditar');
                    const campos = document.getElementById('camposEditar');

                    // Asegurar estado inicial
                    loading.style.setProperty('display', 'flex', 'important');
                    campos.style.opacity = '0.3';

                    fetch(`/adolescentes/${currentEditId}/edit`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(r => {
                            console.log('Response status:', r.status);
                            if (!r.ok) return r.text().then(t => { throw new Error(t) });
                            return r.json();
                        })
                        .then(data => {
                            console.log('Data received successfully');

                            if (data.error) throw new Error(data.error);

                            const camposMap = [
                                { id: 'edit_no_expediente', key: 'no_expediente' },
                                { id: 'edit_nombre_completo', key: 'nombre_completo' },
                                { id: 'edit_sexo', key: 'sexo' },
                                { id: 'edit_fecha_nacimiento', key: 'fecha_nacimiento' },
                                { id: 'edit_fecha_ingreso', key: 'fecha_ingreso' },
                                { id: 'edit_edad', key: 'edad' },
                                { id: 'edit_numero_identidad', key: 'numero_identidad' },
                                { id: 'edit_colonia', key: 'colonia' },
                                { id: 'edit_nombre_tutor', key: 'nombre_tutor' },
                                { id: 'edit_numero_telefono', key: 'numero_telefono' },
                                { id: 'edit_estado_civil', key: 'estado_civil' },
                                { id: 'edit_escolaridad', key: 'escolaridad' },
                                { id: 'edit_anios_cursados', key: 'anios_cursados' },
                                { id: 'edit_ocupacion', key: 'ocupacion' }
                            ];

                            camposMap.forEach(item => {
                                const el = document.getElementById(item.id);
                                if (el) {
                                    let valor = data[item.key];
                                    if (item.key.includes('fecha') && valor) {
                                        el.value = valor.split(' ')[0];
                                    } else {
                                        el.value = valor ?? '';
                                    }
                                }
                            });
                        })
                        .catch(err => {
                            console.error('Error loading data:', err);
                            alert('Error al cargar datos: ' + err.message);
                        })
                        .finally(() => {
                            console.log('Hiding loader...');
                            loading.style.setProperty('display', 'none', 'important');
                            campos.style.opacity = '1';
                        });
                }
            });

            // Evento cuando el modal se cierra
            $('#modalEditar').on('hidden.bs.modal', function () {
                currentEditId = null;
                const loading = document.getElementById('loadingEditar');
                const campos = document.getElementById('camposEditar');
                loading.classList.add('d-none');
                campos.classList.remove('opacity-50');
            });

            // Eventos de edición (Delegados)
            document.addEventListener('blur', function (e) {
                if (e.target.classList.contains('editable')) {
                    saveChange(e.target.dataset.id, e.target.dataset.field, e.target.innerText.trim(), e.target);
                }
            }, true);

            document.addEventListener('keydown', function (e) {
                if (e.target.classList.contains('editable') && e.key === 'Enter') {
                    e.preventDefault();
                    e.target.blur();
                }
            });

            document.addEventListener('change', function (e) {
                if (e.target.classList.contains('select-editable') || e.target.classList.contains('date-editable')) {
                    const row = e.target.closest('tr');
                    const field = e.target.dataset.field;
                    const id = e.target.dataset.id;
                    const value = e.target.value;

                    saveChange(id, field, value, e.target.closest('td'));

                    // Cálculo automático de edad en la tabla
                    if (field === 'fecha_nacimiento' || field === 'fecha_ingreso') {
                        const nacInput = row.querySelector('.date-editable[data-field="fecha_nacimiento"]');
                        const ingInput = row.querySelector('.date-editable[data-field="fecha_ingreso"]');
                        const edadCell = row.querySelector('.editable[data-field="edad"]');

                        if (nacInput && ingInput && edadCell && nacInput.value && ingInput.value) {
                            const nac = new Date(nacInput.value);
                            const ing = new Date(ingInput.value);
                            let edad = ing.getFullYear() - nac.getFullYear();
                            const m = ing.getMonth() - nac.getMonth();
                            if (m < 0 || (m === 0 && ing.getDate() < nac.getDate())) edad--;

                            if (edad >= 0) {
                                edadCell.innerText = edad;
                                saveChange(id, 'edad', edad, edadCell);
                            }
                        }
                    }
                }
            });

            // Validación DNI duplicado en tabla
            document.addEventListener('blur', function (e) {
                if (e.target.classList.contains('editable') && e.target.dataset.field === 'numero_identidad') {
                    const dni = e.target.innerText.trim();
                    const id = e.target.dataset.id;
                    if (dni.length > 5) {
                        fetch(`{{ route('adolescentes.check-dni') }}?dni=${dni}`)
                            .then(r => r.json())
                            .then(data => {
                                if (data.exists) {
                                    // El backend debería idealmente decir si es de OTRO id, pero aquí haremos un aviso simple
                                    // Si existe, avisamos
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Identidad Duplicada',
                                        text: 'Este número de identidad ya existe en otro registro.',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                    e.target.classList.add('bg-danger', 'bg-opacity-10');
                                }
                            });
                    }
                }
            }, true);

            // Manejo de edición en celdas contenteditable (blur)
            document.addEventListener('blur', function (e) {
                if (e.target.classList.contains('editable')) {
                    const originalValue = e.target.getAttribute('data-original') || e.target.innerText.trim();
                    const newValue = e.target.innerText.trim();

                    if (originalValue !== newValue) {
                        saveChange(e.target.dataset.id, e.target.dataset.field, newValue, e.target);
                        e.target.setAttribute('data-original', newValue);
                    }
                }
            }, true);

            // Manejo de eliminación con SweetAlert2 (Delegado)
            document.addEventListener('submit', function (e) {
                if (e.target.classList.contains('form-eliminar')) {
                    e.preventDefault();
                    const form = e.target;

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "Esta acción no se puede deshacer.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }
            });

        });

        function saveChange(id, field, value, element) {
            element.classList.add('bg-warning', 'bg-opacity-10');
            fetch(`/adolescentes/${id}/ajax`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ field: field, value: value })
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        element.classList.remove('bg-warning', 'bg-opacity-10');
                        element.classList.add('bg-success', 'bg-opacity-10');
                        setTimeout(() => element.classList.remove('bg-success', 'bg-opacity-10'), 1000);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        element.classList.add('bg-danger', 'bg-opacity-10');
                    }
                })
                .catch(error => {
                    console.error('Error saveChange:', error);
                    element.classList.add('bg-danger', 'bg-opacity-10');
                });
        }

        // Función para calcular edad automáticamente
        function calcularEdad(fechaNac, fechaIng, targetInput) {
            if (!fechaNac || !fechaIng) return;

            const nac = new Date(fechaNac);
            const ing = new Date(fechaIng);

            if (isNaN(nac.getTime()) || isNaN(ing.getTime())) return;

            let edad = ing.getFullYear() - nac.getFullYear();
            const m = ing.getMonth() - nac.getMonth();

            if (m < 0 || (m === 0 && ing.getDate() < nac.getDate())) {
                edad--;
            }

            if (edad >= 0) {
                targetInput.value = edad;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // --- PERSISTENCIA LOCALSTORAGE ---
            const formNuevo = document.querySelector('#modalNuevo form');
            const btnLimpiar = document.getElementById('btnLimpiarModal');

            if (formNuevo) {
                const inputsNuevo = formNuevo.querySelectorAll('input, select, textarea');

                // Cargar datos guardados
                const savedData = JSON.parse(localStorage.getItem('last_adolescente_draft') || '{}');
                Object.keys(savedData).forEach(key => {
                    const input = formNuevo.querySelector(`[name="${key}"]`);
                    if (input) input.value = savedData[key];
                });

                // Guardar al cambiar
                inputsNuevo.forEach(input => {
                    input.addEventListener('input', () => {
                        const data = {};
                        new FormData(formNuevo).forEach((value, key) => {
                            if (key !== '_token') data[key] = value;
                        });
                        localStorage.setItem('last_adolescente_draft', JSON.stringify(data));
                    });
                });

                // Botón Limpiar
                if (btnLimpiar) {
                    btnLimpiar.addEventListener('click', () => {
                        formNuevo.reset();
                        localStorage.removeItem('last_adolescente_draft');
                        Swal.fire({ icon: 'info', title: 'Formulario limpiado', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                    });
                }

                // Validación de DNI duplicado
                const inputDni = document.getElementById('nuevo_numero_identidad');
                const dniWarning = document.getElementById('dniWarning');
                if (inputDni) {
                    inputDni.addEventListener('blur', function () {
                        const dni = this.value.trim();
                        if (dni.length > 5) {
                            fetch(`{{ route('adolescentes.check-dni') }}?dni=${dni}`)
                                .then(r => r.json())
                                .then(data => {
                                    if (data.exists) {
                                        dniWarning.classList.remove('d-none');
                                        this.classList.add('is-invalid');
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Paciente Duplicado',
                                            text: 'Este número de identidad ya se encuentra registrado.',
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 4000
                                        });
                                    } else {
                                        dniWarning.classList.add('d-none');
                                        this.classList.remove('is-invalid');
                                    }
                                });
                        }
                    });
                }

                // Validación de Expediente duplicado
                const inputExp = document.getElementById('nuevo_no_expediente');
                const expWarning = document.getElementById('expWarning');
                if (inputExp) {
                    inputExp.addEventListener('blur', function () {
                        const exp = this.value.trim();
                        if (exp.length > 0) {
                            fetch(`{{ route('adolescentes.check-expediente') }}?exp=${exp}`)
                                .then(r => r.json())
                                .then(data => {
                                    if (data.exists) {
                                        expWarning.classList.remove('d-none');
                                        this.classList.add('is-invalid');
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Expediente Duplicado',
                                            text: 'Este número de expediente ya se encuentra registrado.',
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 4000
                                        });
                                    } else {
                                        expWarning.classList.add('d-none');
                                        this.classList.remove('is-invalid');
                                    }
                                });
                        }
                    });
                }

                // Validación Rango Edad (10-19)
                formNuevo.addEventListener('submit', function (e) {
                    const edad = parseInt(document.getElementById('nuevo_edad').value);
                    if (edad < 10 || edad > 19) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Fuera de Rango',
                            text: 'El paciente debe tener entre 10 y 19 años para ser registrado como adolescente.',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        localStorage.removeItem('last_adolescente_draft');
                    }
                });
            }

            // --- EVENTOS DE CÁLCULO DE EDAD ---
            const nuevoNac = document.getElementById('nuevo_fecha_nacimiento');
            const nuevoIng = document.getElementById('nuevo_fecha_ingreso');
            const nuevoEdad = document.getElementById('nuevo_edad');

            if (nuevoNac && nuevoIng && nuevoEdad) {
                [nuevoNac, nuevoIng].forEach(el => {
                    el.addEventListener('change', () => calcularEdad(nuevoNac.value, nuevoIng.value, nuevoEdad));
                });
            }

            const editNac = document.getElementById('edit_fecha_nacimiento');
            const editIng = document.getElementById('edit_fecha_ingreso');
            const editEdad = document.getElementById('edit_edad');

            if (editNac && editIng && editEdad) {
                [editNac, editIng].forEach(el => {
                    el.addEventListener('change', () => calcularEdad(editNac.value, editIng.value, editEdad));
                });
            }
        });
    </script>
    {{-- MODAL NUEVO --}}
    <div class="modal fade" id="modalNuevo" tabindex="-1" aria-labelledby="modalNuevoLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('adolescentes.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalNuevoLabel"><i class="fas fa-user-plus me-2"></i>Registrar
                            Nuevo Adolescente</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label class="fw-bold small">Expediente</label>
                                <input type="text" name="no_expediente" id="nuevo_no_expediente" class="form-control" required>
                                <div id="expWarning" class="text-danger small fw-bold mt-1 d-none">
                                    <i class="fas fa-exclamation-triangle me-1"></i>¡Ya existe!
                                </div>
                            </div>
                            <div class="form-group col-md-10">
                                <label class="fw-bold small">Nombre Completo</label>
                                <input type="text" name="nombre_completo" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="fw-bold small">Sexo</label>
                                <select name="sexo" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    <option value="M">Masculino (M)</option>
                                    <option value="F">Femenino (F)</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="fw-bold small">Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="nuevo_fecha_nacimiento"
                                    class="form-control" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="fw-bold small">Fecha de Ingreso</label>
                                <input type="date" name="fecha_ingreso" id="nuevo_fecha_ingreso" class="form-control"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-2">
                                <label class="fw-bold small">Edad</label>
                                <input type="number" name="edad" id="nuevo_edad" class="form-control" required readonly
                                    style="background-color: #f8fafc;">
                            </div>
                            <div class="form-group col-md-5">
                                <label class="fw-bold small">Número de Identidad</label>
                                <input type="text" name="numero_identidad" id="nuevo_numero_identidad"
                                    class="form-control">
                                <div id="dniWarning" class="text-danger small fw-bold mt-1 d-none">
                                    <i class="fas fa-exclamation-triangle me-1"></i>¡Ya existe!
                                </div>
                            </div>
                            <div class="form-group col-md-5">
                                <label class="fw-bold small">Nombre del Tutor</label>
                                <input type="text" name="nombre_tutor" class="form-control">
                            </div>
                        </div>
                        <div class="mt-3">
                            <h6 class="border-bottom pb-2 text-primary fw-bold">Ubicación y Perfil</h6>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-9">
                                <label class="fw-bold small">Colonia de Residencia</label>
                                <select name="colonia" id="nuevo_colonia" class="form-control">
                                    <option value="">Seleccione Colonia...</option>
                                    @foreach($colonias as $col)
                                        <option value="{{ $col->COLONIA }}">{{ $col->COLONIA }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="fw-bold small">Teléfono</label>
                                <input type="text" name="numero_telefono" class="form-control">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="fw-bold small">Estado Civil</label>
                                <select name="estado_civil" class="form-control">
                                    <option value="">-</option>
                                    <option value="Soltero">Soltero</option>
                                    <option value="Unión Libre">Unión Libre</option>
                                    <option value="Casado">Casado</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="fw-bold small">Escolaridad</label>
                                <select name="escolaridad" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    <option value="PRIMARIA">PRIMARIA</option>
                                    <option value="SECUNDARIA">SECUNDARIA</option>
                                    <option value="UNIVERSITARIO">UNIVERSITARIO</option>
                                    <option value="NINGUNA">NINGUNA</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="fw-bold small text-nowrap">Años Cursados</label>
                                <input type="number" name="anios_cursados" class="form-control" min="0" max="25">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="fw-bold small">Ocupación</label>
                                <select name="ocupacion" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                    <option value="TRABAJA">TRABAJA</option>
                                    <option value="ESTUDIA" selected>ESTUDIA</option>
                                    <option value="TRABAJA Y ESTUDIA">TRABAJA Y ESTUDIA</option>
                                    <option value="NINGUNA">NINGUNA</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-danger" id="btnLimpiarModal"><i
                                class="fas fa-eraser me-2"></i>Limpiar Formulario</button>
                        <div>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Registrar
                                Paciente</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDITAR --}}
    <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <form id="formEditar" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="modalEditarLabel"><i class="fas fa-edit me-2"></i>Editar Información
                            de Adolescente</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0 position-relative">
                        <div id="loadingEditar"
                            class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75 d-none"
                            style="z-index: 10;">
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin fa-2x text-info mb-2"></i>
                                <p class="small fw-bold text-muted">Cargando datos...</p>
                            </div>
                        </div>
                        <div id="camposEditar" class="p-4">
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <label class="fw-bold small">Expediente</label>
                                    <input type="text" name="no_expediente" id="edit_no_expediente" class="form-control"
                                        required>
                                </div>
                                <div class="form-group col-md-10">
                                    <label class="fw-bold small">Nombre Completo</label>
                                    <input type="text" name="nombre_completo" id="edit_nombre_completo"
                                        class="form-control" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label class="fw-bold small">Sexo</label>
                                    <select name="sexo" id="edit_sexo" class="form-control" required>
                                        <option value="M">Masculino (M)</option>
                                        <option value="F">Femenino (F)</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="fw-bold small">Fecha de Nacimiento</label>
                                    <input type="date" name="fecha_nacimiento" id="edit_fecha_nacimiento"
                                        class="form-control" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="fw-bold small">Fecha de Ingreso</label>
                                    <input type="date" name="fecha_ingreso" id="edit_fecha_ingreso" class="form-control"
                                        required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <label class="fw-bold small">Edad</label>
                                    <input type="number" name="edad" id="edit_edad" class="form-control" required>
                                </div>
                                <div class="form-group col-md-5">
                                    <label class="fw-bold small">Número de Identidad</label>
                                    <input type="text" name="numero_identidad" id="edit_numero_identidad"
                                        class="form-control">
                                </div>
                                <div class="form-group col-md-5">
                                    <label class="fw-bold small">Nombre del Tutor</label>
                                    <input type="text" name="nombre_tutor" id="edit_nombre_tutor" class="form-control">
                                </div>
                            </div>
                            <div class="mt-3">
                                <h6 class="border-bottom pb-2 text-primary fw-bold">Ubicación y Perfil</h6>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-9">
                                    <label class="fw-bold small">Colonia de Residencia</label>
                                    <select name="colonia" id="edit_colonia" class="form-control">
                                        <option value="">Seleccione Colonia...</option>
                                        @foreach($colonias as $col)
                                            <option value="{{ $col->COLONIA }}">{{ $col->COLONIA }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="fw-bold small">Teléfono</label>
                                    <input type="text" name="numero_telefono" id="edit_numero_telefono"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label class="fw-bold small">Estado Civil</label>
                                    <select name="estado_civil" id="edit_estado_civil" class="form-control">
                                        <option value="">-</option>
                                        <option value="Soltero">Soltero</option>
                                        <option value="Unión Libre">Unión Libre</option>
                                        <option value="Casado">Casado</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="fw-bold small">Escolaridad</label>
                                    <select name="escolaridad" id="edit_escolaridad" class="form-control" required>
                                        <option value="PRIMARIA">PRIMARIA</option>
                                        <option value="SECUNDARIA">SECUNDARIA</option>
                                        <option value="UNIVERSITARIO">UNIVERSITARIO</option>
                                        <option value="NINGUNA">NINGUNA</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="fw-bold small text-nowrap">Años Cursados</label>
                                    <input type="number" name="anios_cursados" id="edit_anios_cursados"
                                        class="form-control" min="0" max="25">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label class="fw-bold small">Ocupación</label>
                                    <select name="ocupacion" id="edit_ocupacion" class="form-control" required>
                                        <option value="TRABAJA">TRABAJA</option>
                                        <option value="ESTUDIA">ESTUDIA</option>
                                        <option value="TRABAJA Y ESTUDIA">TRABAJA Y ESTUDIA</option>
                                        <option value="NINGUNA">NINGUNA</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .editable:focus {
            outline: 2px solid #3b82f6 !important;
            background-color: white !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .bg-opacity-10 {
            --tw-bg-opacity: 0.1;
        }
    </style>
</x-app-layout>