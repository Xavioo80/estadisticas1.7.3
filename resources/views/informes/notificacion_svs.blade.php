<x-app-layout>
    @section('title', 'Consulta y Registro Pacientes SNVS')

    <div id="notificacion-svs-container" class="h-full flex flex-col bg-slate-100 dark:bg-slate-950 p-2 sm:p-4 overflow-hidden">
        @include('informes.notificacion_svs_content')
    </div>

    @push('scripts')
    <script>
        $(document).ready(function () {
            // 1. Manejador AJAX para Filtros
            $(document).on('change', '.ajax-filter-svs', function () {
                let formData = $('#filter-form-svs').serialize();
                let url = "{{ route('informes.notificacion_svs') }}?" + formData;

                $('#notificacion-svs-container').addClass('opacity-50 pointer-events-none');

                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (html) {
                        $('#notificacion-svs-container').html(html).removeClass('opacity-50 pointer-events-none');
                    },
                    error: function () {
                        $('#notificacion-svs-container').removeClass('opacity-50 pointer-events-none');
                        alert('Error al cargar la tabla de consultas SNVS.');
                    }
                });
            });

            // 2. Cambio rápido de enfermedad SVS
            $(document).on('change', '.svs-disease-select', function () {
                let select = $(this);
                $.post("{{ route('informes.notificacion_svs.update_disease') }}", {
                    _token: "{{ csrf_token() }}",
                    informe_id: select.data('informe-id'),
                    enfermedad_svs: select.val()
                });
            });

            // 3. Toggle Notificado (Instantáneo)
            $(document).on('change', '.chk-notificado-svs', function () {
                let chk = $(this);
                let isChecked = chk.is(':checked');
                chk.closest('label').find('.lbl-notificado-svs').text(isChecked ? 'Notificado' : 'Pendiente');

                $.post("{{ route('informes.notificacion_svs.toggle_notificado') }}", {
                    _token: "{{ csrf_token() }}",
                    informe_id: chk.data('informe-id'),
                    notificado: isChecked
                }).fail(function() {
                    chk.prop('checked', !isChecked);
                });
            });
        });

        // --- MOTOR DE BÚSQUEDA INSTANTÁNEA EN TIEMPO REAL ---

        // Formato de DNI + Búsqueda proactiva al completar dígitos
        function formatearDniFila(inputEl) {
            let value = inputEl.value.replace(/\D/g, '');
            let formatted = '';

            if (value.length > 0) formatted = value.substring(0, 4);
            if (value.length >= 5) formatted += '-' + value.substring(4, 8);
            if (value.length >= 9) formatted += '-' + value.substring(8, 13);

            inputEl.value = formatted;

            // Disparar búsqueda automática al tener los 13 dígitos
            if (value.length === 13 && inputEl.dataset.lastSearched !== formatted) {
                inputEl.dataset.lastSearched = formatted;
                buscarPacienteEnFila(inputEl, $(inputEl).data('informe-id'));
            }
        }

        // Búsqueda AJAX ultra-rápida (Respuesta y Guardado en 1 solo viaje HTTP)
        function buscarPacienteEnFila(inputOrBtn, informeId) {
            let row = $(inputOrBtn).closest('tr');
            let inputDni = row.find('.input-dni-row');
            let formatted = inputDni.val().trim();

            if (!formatted) return;

            // Feedback visual inmediato
            let statusIcon = row.find('.btn-guardar-row i');
            statusIcon.removeClass('fa-save fa-check fa-exclamation-triangle text-emerald-500 text-red-500').addClass('fa-spinner fa-spin');

            $.ajax({
                url: "{{ route('informes.notificacion_svs.buscar_paciente') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    identidad: formatted,
                    informe_id: informeId,
                    fecha_inicio_sintomas: row.find('.input-fecha-sintomas-row').val(),
                    enfermedad_svs: row.find('.svs-disease-select').val()
                },
                success: function (res) {
                    statusIcon.removeClass('fa-spinner fa-spin').addClass('fa-check text-emerald-500');
                    if (res && (res.success || res.data)) {
                        renderizarDatosEnFila(row, res.data || res);
                    }
                    setTimeout(() => statusIcon.removeClass('fa-check text-emerald-500').addClass('fa-save'), 1200);
                },
                error: function () {
                    statusIcon.removeClass('fa-spinner fa-spin').addClass('fa-exclamation-triangle text-red-500');
                    setTimeout(() => statusIcon.removeClass('fa-exclamation-triangle text-red-500').addClass('fa-save'), 2000);
                }
            });
        }

        // Botón Guardar / Búsqueda manual
        function guardarRegistroFila(btnEl, informeId) {
            buscarPacienteEnFila(btnEl, informeId);
        }

        // Guardar fecha de síntomas
        function guardarFechaSintomasFila(inputEl, informeId) {
            let val = $(inputEl).val();
            $.post("{{ route('informes.notificacion_svs.update_disease') }}", {
                _token: "{{ csrf_token() }}",
                informe_id: informeId,
                fecha_inicio_sintomas: val
            });
        }

        // Edición inline de Teléfono en la vista notificacion_svs
        $(document).on('click dblclick', '.inline-edit-tel-wrap .inline-edit-tel-disp, .inline-edit-tel-wrap .fa-pencil-alt', function (e) {
            e.stopPropagation();
            let wrap = $(this).closest('.inline-edit-tel-wrap');
            let disp = wrap.find('.inline-edit-tel-disp');
            let pencil = wrap.find('.fa-pencil-alt');
            let input = wrap.find('.inline-edit-tel-input');

            disp.addClass('hidden');
            pencil.addClass('hidden');
            input.removeClass('hidden').focus().select();
        });

        $(document).on('keydown', '.inline-edit-tel-input', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                guardarTelefonoInline($(this));
            }
            if (e.key === 'Escape') {
                cancelarTelefonoInline($(this));
            }
        });

        $(document).on('blur', '.inline-edit-tel-input', function () {
            guardarTelefonoInline($(this));
        });

        function guardarTelefonoInline($input) {
            let wrap = $input.closest('.inline-edit-tel-wrap');
            if (wrap.data('saving')) return;

            let informeId = wrap.data('informe-id');
            let val = $input.val().trim();
            let disp = wrap.find('.inline-edit-tel-disp');
            let pencil = wrap.find('.fa-pencil-alt');

            wrap.data('saving', true);
            $input.addClass('hidden');
            disp.removeClass('hidden').html('<i class="fas fa-spinner fa-spin text-amber-600"></i>');

            $.post("{{ route('informes.notificacion_svs.update_telefono') }}", {
                _token: "{{ csrf_token() }}",
                informe_id: informeId,
                telefono: val
            }, function (res) {
                wrap.data('saving', false);
                pencil.removeClass('hidden');
                let newTel = (res && res.telefono) ? res.telefono : (val || '-');
                wrap.data('value', newTel);
                if (newTel && newTel !== '-') {
                    disp.removeClass('italic text-slate-400').addClass('text-amber-700 font-semibold').text(newTel);
                } else {
                    disp.removeClass('text-amber-700 font-semibold').addClass('italic text-slate-400').text('-');
                }
            }).fail(function () {
                wrap.data('saving', false);
                pencil.removeClass('hidden');
                disp.text(wrap.data('value') || '-');
            });
        }

        function cancelarTelefonoInline($input) {
            let wrap = $input.closest('.inline-edit-tel-wrap');
            $input.addClass('hidden');
            wrap.find('.inline-edit-tel-disp').removeClass('hidden');
            wrap.find('.fa-pencil-alt').removeClass('hidden');
        }

        // Actualización síncrona del DOM (Sin peticiones extra)
        function renderizarDatosEnFila(row, d) {
            if (!d) return;
            let nombreFull = d.nombre_completo || ((d.nombres || '') + ' ' + (d.apellidos || '')).trim();

            if (nombreFull && nombreFull !== 'undefined' && !nombreFull.startsWith('PACIENTE DE')) {
                row.find('.cell-paciente').text(nombreFull);
            }
            if (d.fecha_nacimiento && d.fecha_nacimiento !== '-') {
                row.find('.cell-fecha-nac').text(d.fecha_nacimiento);
            }
            if (d.edad !== undefined && d.edad !== null && d.edad !== '') {
                row.find('.cell-edad').text(d.edad);
            }
            if (d.sexo && d.sexo !== '-') {
                row.find('.cell-sexo').text(d.sexo);
            }
            if (d.telefono && d.telefono !== '-') {
                let telWrap = row.find('.inline-edit-tel-wrap');
                if (telWrap.length) {
                    telWrap.data('value', d.telefono);
                    telWrap.find('.inline-edit-tel-disp').removeClass('italic text-slate-400').addClass('text-amber-700 font-semibold').text(d.telefono);
                    telWrap.find('.inline-edit-tel-input').val(d.telefono);
                } else {
                    row.find('.cell-telefono').text(d.telefono);
                }
            }
            let direccion = d.colonia || d.direccion;
            if (direccion && direccion !== '-' && direccion !== 'undefined') {
                row.find('.cell-direccion').text(direccion);
            }
        }
    </script>
    @endpush
</x-app-layout>
