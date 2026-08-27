<x-app-layout>
    @section('title', 'Adolescentes Depurados')
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between py-2">
            <div>
                <h2 class="font-bold text-xl text-slate-900 leading-none mb-1">
                    {{ __('Pacientes Depurados') }}
                </h2>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-[0.2em] m-0">Historial de
                    pacientes fuera de rango (10-19 años)</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                {{-- Buscador --}}
                <div class="input-group shadow-sm" style="width: 300px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0" style="height: 35px;"><i
                                class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" id="search-input" value="{{ request('search') }}"
                        class="form-control border-left-0" style="font-size: 0.9rem; height: 35px;"
                        placeholder="Buscar por expediente, nombre o DNI...">
                </div>
                <a href="{{ route('adolescentes.index') }}"
                    class="btn btn-outline-secondary btn-sm fw-bold shadow-sm d-flex align-items-center"
                    style="height: 35px;">
                    <i class="fas fa-arrow-left me-2"></i> VOLVER AL MAESTRO
                </a>
            </div>
        </div>
    </x-slot>

    @push('css')
        <style>
            .ata-table-container {
                background-color: #f8fafc;
                padding: 10px;
                height: calc(100vh - 120px);
                overflow: hidden;
                display: flex;
                flex-direction: column;
            }

            .ata-card {
                background: white;
                border: 1px solid #cbd5e1;
                border-radius: 4px;
                flex: 1;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
            }

            .table-ata tbody tr:nth-child(odd) td {
                background-color: #ffffff !important;
            }

            .table-ata tbody tr:nth-child(even) td {
                background-color: #f5f7fa !important;
            }

            .table-ata tbody tr:hover td {
                background-color: #fff7ed !important;
            }

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
            }

            .badge-ata {
                background: #fed7aa;
                color: #9a3412;
                padding: 2px 8px;
                border-radius: 4px;
                margin-left: 5px;
            }
        </style>
    @endpush

    <div class="ata-table-container">
        <div class="ata-card">
            <div class="ata-table-wrapper">
                <table class="table-ata">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th style="width: 80px;">EXP</th>
                            <th style="min-width: 250px;">NOMBRE COMPLETO</th>
                            <th style="width: 45px;">SEXO</th>
                            <th style="width: 100px;">NACIMIENTO</th>
                            <th style="width: 100px;">EDAD ACTUAL</th>
                            <th style="width: 120px;">DNI</th>
                            <th class="text-center" style="width: 150px;">COLONIA</th>
                            <th class="text-center" style="width: 100px;">TELÉFONO</th>
                            <th class="text-center" style="width: 100px;">ESCOLARIDAD</th>
                            <th class="text-center" style="width: 50px;">AÑOS</th>
                            <th class="text-center" style="width: 120px;">OCUPACIÓN</th>
                            <th class="text-center" style="width: 80px;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $index => $ado)
                            @php
                                $hoy = \Carbon\Carbon::now();
                                $cumple = $ado->fecha_nacimiento ? \Carbon\Carbon::parse($ado->fecha_nacimiento) : null;
                                $edadActual = $cumple ? $cumple->diffInYears($hoy) : 0;
                            @endphp
                            <tr>
                                <td class="text-center text-muted">{{ $registros->firstItem() + $index }}</td>
                                <td class="text-primary font-weight-bold text-center editable" data-id="{{ $ado->id }}"
                                    data-field="no_expediente" contenteditable="true">{{ $ado->no_expediente }}</td>
                                <td class="editable" data-id="{{ $ado->id }}" data-field="nombre_completo"
                                    contenteditable="true">{{ $ado->nombre_completo }}</td>
                                <td class="text-center p-0">
                                    <select class="ata-select select-editable text-center" data-id="{{ $ado->id }}"
                                        data-field="sexo">
                                        <option value="M" {{ $ado->sexo == 'M' ? 'selected' : '' }}>M</option>
                                        <option value="F" {{ $ado->sexo == 'F' ? 'selected' : '' }}>F</option>
                                    </select>
                                </td>
                                <td class="text-center p-0">
                                    <input type="date" class="ata-input date-editable text-center" data-id="{{ $ado->id }}"
                                        data-field="fecha_nacimiento"
                                        value="{{ $ado->fecha_nacimiento ? $ado->fecha_nacimiento->format('Y-m-d') : '' }}">
                                </td>
                                <td class="text-center">
                                    <span class="font-bold text-danger">{{ $edadActual }} AÑOS</span>
                                </td>
                                <td class="text-center editable" data-id="{{ $ado->id }}" data-field="numero_identidad"
                                    contenteditable="true">{{ $ado->numero_identidad }}</td>
                                <td class="p-0">
                                    <select class="ata-select select-editable text-center" data-id="{{ $ado->id }}"
                                        data-field="colonia">
                                        <option value="">-</option>
                                        @foreach($colonias as $col)
                                            <option value="{{ $col->COLONIA }}" {{ $ado->colonia == $col->COLONIA ? 'selected' : '' }}>{{ $col->COLONIA }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center editable" data-id="{{ $ado->id }}" data-field="numero_telefono"
                                    contenteditable="true">{{ $ado->numero_telefono }}</td>
                                <td class="p-0">
                                    <select class="ata-select select-editable text-center" data-id="{{ $ado->id }}"
                                        data-field="escolaridad">
                                        <option value="">-</option>
                                        <option value="PRIMARIA" {{ $ado->escolaridad == 'PRIMARIA' ? 'selected' : '' }}>
                                            PRIMARIA</option>
                                        <option value="SECUNDARIA" {{ $ado->escolaridad == 'SECUNDARIA' ? 'selected' : '' }}>
                                            SECUNDARIA</option>
                                        <option value="UNIVERSITARIO" {{ $ado->escolaridad == 'UNIVERSITARIO' ? 'selected' : '' }}>UNIVERSITARIO</option>
                                        <option value="NINGUNA" {{ $ado->escolaridad == 'NINGUNA' ? 'selected' : '' }}>NINGUNA
                                        </option>
                                    </select>
                                </td>
                                <td class="text-center editable" data-id="{{ $ado->id }}" data-field="anios_cursados"
                                    contenteditable="true">{{ $ado->anios_cursados }}</td>
                                <td class="p-0">
                                    <select class="ata-select select-editable text-center" data-id="{{ $ado->id }}"
                                        data-field="ocupacion">
                                        <option value="">-</option>
                                        <option value="TRABAJA" {{ $ado->ocupacion == 'TRABAJA' ? 'selected' : '' }}>TRABAJA
                                        </option>
                                        <option value="ESTUDIA" {{ $ado->ocupacion == 'ESTUDIA' ? 'selected' : '' }}>ESTUDIA
                                        </option>
                                        <option value="TRABAJA Y ESTUDIA" {{ $ado->ocupacion == 'TRABAJA Y ESTUDIA' ? 'selected' : '' }}>T/E</option>
                                        <option value="NINGUNA" {{ $ado->ocupacion == 'NINGUNA' ? 'selected' : '' }}>NINGUNA
                                        </option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <a href="{{ route('adolescentes.historial', $ado->no_expediente) }}"
                                            class="btn btn-primary btn-sm p-0 d-flex align-items-center justify-content-center"
                                            style="width: 55px; height: 18px; font-size: 8.5px; font-weight: bold; border-radius: 2px;"
                                            title="Ver Historial">
                                            HISTORIAL
                                        </a>
                                        <button type="button"
                                            class="btn btn-info btn-sm p-0 d-flex align-items-center justify-content-center btn-editar"
                                            data-id="{{ $ado->id }}" style="width: 20px; height: 18px; border-radius: 2px;">
                                            <i class="fas fa-edit text-white" style="font-size: 9px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">No hay pacientes depurados que
                                    coincidan con la búsqueda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="ata-footer">
                <div class="d-flex align-items-center">
                    <span>TOTAL DEPURADOS: <span class="badge-ata">{{ $registros->total() }}</span></span>
                </div>
                <div>
                    {{ $registros->appends(request()->input())->links('pagination::bootstrap-4') }}
                </div>
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
                                <div class="form-group col-md-6">
                                    <label class="fw-bold small">Expediente</label>
                                    <input type="text" name="no_expediente" id="edit_no_expediente" class="form-control"
                                        required>
                                </div>
                                <div class="form-group col-md-6">
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
                                <div class="form-group col-md-4">
                                    <label class="fw-bold small">Edad</label>
                                    <input type="number" name="edad" id="edit_edad" class="form-control" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="fw-bold small">Número de Identidad</label>
                                    <input type="text" name="numero_identidad" id="edit_numero_identidad"
                                        class="form-control">
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="fw-bold small">Nombre del Tutor</label>
                                    <input type="text" name="nombre_tutor" id="edit_nombre_tutor" class="form-control">
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-input');
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

            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    window.location.href = `{{ route('adolescentes.depurados') }}?search=${this.value}`;
                }, 500);
            });

            // Reutilizar lógica de edición AJAX
            document.addEventListener('blur', function (e) {
                if (e.target.classList.contains('editable')) {
                    saveChange(e.target.dataset.id, e.target.dataset.field, e.target.innerText.trim(), e.target);
                }
            }, true);

            document.addEventListener('change', function (e) {
                if (e.target.classList.contains('select-editable') || e.target.classList.contains('date-editable')) {
                    const row = e.target.closest('tr');
                    const field = e.target.dataset.field;
                    const id = e.target.dataset.id;
                    const value = e.target.value;

                    saveChange(id, field, value, e.target.closest('td') || e.target);

                    // Cálculo automático de edad en la tabla
                    if (field === 'fecha_nacimiento') {
                        const nacInput = e.target;
                        const edadSpan = row.querySelector('.text-danger'); // El span que muestra la edad en depurados

                        if (nacInput && edadSpan && nacInput.value) {
                            const nac = new Date(nacInput.value);
                            const hoy = new Date();
                            let edad = hoy.getFullYear() - nac.getFullYear();
                            const m = hoy.getMonth() - nac.getMonth();
                            if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) edad--;

                            if (edad >= 0) {
                                edadSpan.innerText = edad + ' AÑOS';
                                // En depurados la edad no es una celda editable separada usualmente, 
                                // pero la mandamos al server para sincronizar
                                saveChange(id, 'edad', edad, edadSpan);
                            }
                        }
                    }
                }
            });

            // Validación DNI duplicado en tabla depurados
            document.addEventListener('blur', function (e) {
                if (e.target.classList.contains('editable') && e.target.dataset.field === 'numero_identidad') {
                    const dni = e.target.innerText.trim();
                    if (dni.length > 5) {
                        fetch(`{{ route('adolescentes.check-dni') }}?dni=${dni}`)
                            .then(r => r.json())
                            .then(data => {
                                if (data.exists) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Identidad Duplicada',
                                        text: 'Este número de identidad ya existe.',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                }
                            });
                    }
                }
            }, true);

            // Modal Editar
            let currentEditId = null;
            document.addEventListener('click', function (e) {
                const btnEdit = e.target.closest('.btn-editar');
                if (btnEdit) {
                    currentEditId = btnEdit.dataset.id;
                    const form = document.getElementById('formEditar');
                    form.action = `/adolescentes/${currentEditId}`;
                    $('#modalEditar').modal('show');
                }
            });

            $('#modalEditar').on('shown.bs.modal', function () {
                if (currentEditId) {
                    const loading = document.getElementById('loadingEditar');
                    const campos = document.getElementById('camposEditar');
                    loading.classList.remove('d-none');
                    campos.style.opacity = '0.3';

                    fetch(`/adolescentes/${currentEditId}/edit`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => r.json())
                        .then(data => {
                            const fields = ['no_expediente', 'nombre_completo', 'sexo', 'fecha_nacimiento', 'fecha_ingreso', 'edad', 'numero_identidad', 'nombre_tutor'];
                            fields.forEach(f => {
                                const el = document.getElementById('edit_' + f);
                                if (el) el.value = data[f] ? data[f].split(' ')[0] : '';
                            });
                        })
                        .finally(() => {
                            loading.classList.add('d-none');
                            campos.style.opacity = '1';
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
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        element.classList.remove('bg-warning', 'bg-opacity-10');
                        element.classList.add('bg-success', 'bg-opacity-10');
                        setTimeout(() => element.classList.remove('bg-success', 'bg-opacity-10'), 1000);

                        // Si se cambió la fecha de nacimiento, recargar para ver si sale de depurados
                        if (field === 'fecha_nacimiento') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Fecha Actualizada',
                                text: 'El estado del paciente podría cambiar según su nueva edad.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => window.location.reload());
                        }
                    }
                });
        }
    </script>
</x-app-layout>