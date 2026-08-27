<x-app-layout>
    @section('title', 'Nuevo Registro - Adulto Mayor')

    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between py-2">
            <div>
                <h2 class="font-bold text-xl text-slate-900 leading-none mb-1">
                    {{ __('Nuevo Registro Adulto Mayor') }}
                </h2>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-[0.2em] m-0">Inscripción al Programa de Adultos Mayores</p>
            </div>
            <a href="{{ route('adulto-mayor.index') }}" class="btn btn-secondary fw-bold shadow-sm d-flex align-items-center gap-2" style="height: 35px; font-size: 13px;">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Directorio
            </a>
        </div>
    </x-slot>

    @push('css')
        <style>
            .main-container {
                max-width: 900px;
                margin: 0 auto;
                padding: 24px 16px 48px 16px;
            }

            .page-header-banner {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 24px;
            }

            .page-title-box {
                display: flex;
                align-items: center;
                gap: 16px;
            }

            .btn-back-circle {
                width: 38px;
                height: 38px;
                border-radius: 12px;
                background: white;
                border: 1px solid #e2e8f0;
                color: #64748b;
                display: flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                transition: all 0.2s;
            }

            .btn-back-circle:hover {
                background: #f1f5f9;
                color: #1e293b;
                transform: translateX(-2px);
            }

            .title-icon-wrapper {
                color: #4f46e5;
                font-size: 24px;
            }

            .page-main-title {
                font-size: 24px;
                font-weight: 800;
                color: #0f172a;
                margin: 0;
                line-height: 1.2;
            }

            .page-subtitle {
                font-size: 13px;
                color: #64748b;
                margin: 2px 0 0 0;
                font-weight: 500;
            }

            .design-wave-badge {
                background: #f0f5ff;
                border: 1px solid #c7d2fe;
                color: #4f46e5;
                padding: 6px 14px;
                border-radius: 14px;
                font-size: 13px;
                font-weight: 700;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            /* Card del Formulario */
            .form-card {
                background: #ffffff;
                border-radius: 24px;
                box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.05);
                border: 1px solid #f1f5f9;
                padding: 36px;
            }

            .form-grid-2 {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 24px;
                margin-bottom: 24px;
            }

            .form-grid-full {
                margin-bottom: 24px;
            }

            .wave-group {
                position: relative;
                width: 100%;
            }

            .wave-group .input {
                font-size: 15px;
                padding: 12px 10px 8px 0px;
                display: block;
                width: 100%;
                border: none;
                border-bottom: 2px solid #e2e8f0;
                background: transparent;
                color: #1e293b;
                font-weight: 600;
                outline: none;
                transition: border-color 0.2s ease;
            }

            .wave-group .input::placeholder {
                color: transparent;
                transition: color 0.2s ease;
            }

            .wave-group .input:focus::placeholder,
            .wave-group .input.has-value::placeholder {
                color: #94a3b8;
                font-weight: 400;
            }

            .wave-group .wave-label {
                color: #64748b;
                font-size: 13px;
                font-weight: 600;
                position: absolute;
                pointer-events: none;
                left: 0px;
                top: 10px;
                display: inline-flex;
                align-items: center;
                white-space: nowrap;
                margin: 0;
                line-height: 1;
            }

            .wave-group .label-char {
                display: inline-block;
                transition: 0.22s cubic-bezier(0.4, 0, 0.2, 1) all;
                transition-delay: calc(var(--index) * 0.025s);
            }

            .wave-group .label-asterisk {
                display: inline-block;
                color: #ef4444;
                font-weight: 700;
                margin-left: 4px;
            }

            .wave-group .input:focus ~ .wave-label .label-char,
            .wave-group .input.has-value ~ .wave-label .label-char,
            .wave-group .input:not(:placeholder-shown) ~ .wave-label .label-char {
                transform: translateY(-24px);
                font-size: 11px;
                color: #4f46e5;
                font-weight: 700;
            }

            .wave-group .bar {
                position: relative;
                display: block;
                width: 100%;
            }

            .wave-group .bar:before,
            .wave-group .bar:after {
                content: '';
                height: 2px;
                width: 0;
                bottom: 0px;
                position: absolute;
                background: #4f46e5;
                transition: 0.3s ease all;
            }

            .wave-group .bar:before { left: 50%; }
            .wave-group .bar:after { right: 50%; }

            .wave-group .input:focus ~ .bar:before,
            .wave-group .input:focus ~ .bar:after {
                width: 50%;
            }

            .colonia-select-group {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .colonia-select-label {
                font-size: 11px;
                font-weight: 800;
                color: #475569;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .colonia-custom-select {
                width: 100%;
                background-color: #f8fafc;
                border: 1px solid #cbd5e1;
                border-radius: 16px;
                padding: 12px 16px;
                font-size: 14px;
                font-weight: 500;
                color: #334155;
                outline: none;
                cursor: pointer;
                transition: all 0.2s;
                appearance: none;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 20 20' fill='%2364748b'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right 14px center;
            }

            .colonia-custom-select:focus {
                background-color: #ffffff;
                border-color: #4f46e5;
                box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            }

            .dni-alert {
                display: none;
                margin-top: 8px;
                padding: 8px 12px;
                border-radius: 10px;
                font-size: 12px;
                font-weight: 600;
            }
            .dni-alert.show { display: flex; align-items: center; gap: 6px; }
            .dni-alert.warning { background: #fef3c7; color: #92400e; border: 1px solid #f59e0b; }
            .dni-alert.danger { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
            .dni-alert.success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }

            .form-actions-footer {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 16px;
                margin-top: 32px;
            }

            .btn-cancel-link {
                color: #475569;
                font-size: 14px;
                font-weight: 700;
                text-decoration: none;
                padding: 12px 24px;
                border-radius: 14px;
                transition: background 0.2s;
            }

            .btn-cancel-link:hover {
                background: #f1f5f9;
                color: #1e293b;
            }

            .btn-guardar-main {
                background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
                color: white;
                font-size: 14px;
                font-weight: 700;
                padding: 12px 32px;
                border-radius: 16px;
                border: none;
                cursor: pointer;
                box-shadow: 0 8px 20px -4px rgba(79, 70, 229, 0.4);
                display: inline-flex;
                align-items: center;
                gap: 8px;
                transition: all 0.2s ease;
            }

            .btn-guardar-main:hover:not(:disabled) {
                background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%);
                transform: translateY(-1px);
            }

            @media (max-width: 640px) {
                .form-grid-2 {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endpush

    <div class="main-container">
        <div class="page-header-banner">
            <div class="page-title-box">
                <a href="{{ route('adulto-mayor.index') }}" class="btn-back-circle" title="Volver al Directorio">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="title-icon-wrapper">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <h1 class="page-main-title">Nuevo Registro - Adulto Mayor</h1>
                    <p class="page-subtitle">Formulario interactivo de expedientes</p>
                </div>
            </div>

            <div class="design-wave-badge">
                <i class="fas fa-magic"></i> Diseño: Wave
            </div>
        </div>

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            </div>
        @endif

        <div class="form-card">
            <form action="{{ route('adulto-mayor.store') }}" method="POST">
                @csrf

                {{-- Fila 1: Edad | Teléfono --}}
                <div class="form-grid-2">
                    <div class="wave-group">
                        <input type="number" name="edad" class="input" min="0" max="150" value="{{ old('edad') }}" placeholder="Ej. 65" required autocomplete="off">
                        <span class="bar"></span>
                        <label class="wave-label">
                            <span class="label-char" style="--index: 0">E</span>
                            <span class="label-char" style="--index: 1">d</span>
                            <span class="label-char" style="--index: 2">a</span>
                            <span class="label-char" style="--index: 3">d</span>
                        </label>
                    </div>

                    <div class="wave-group">
                        <input type="text" name="telefono" class="input" value="{{ old('telefono') }}" maxlength="15" placeholder="9999-9999" autocomplete="off">
                        <span class="bar"></span>
                        <label class="wave-label">
                            <span class="label-char" style="--index: 0">T</span>
                            <span class="label-char" style="--index: 1">e</span>
                            <span class="label-char" style="--index: 2">l</span>
                            <span class="label-char" style="--index: 3">é</span>
                            <span class="label-char" style="--index: 4">f</span>
                            <span class="label-char" style="--index: 5">o</span>
                            <span class="label-char" style="--index: 6">n</span>
                            <span class="label-char" style="--index: 7">o</span>
                        </label>
                    </div>
                </div>

                {{-- Fila 2: Nombre Completo --}}
                <div class="form-grid-full">
                    <div class="wave-group">
                        <input type="text" name="nombre_completo" class="input" value="{{ old('nombre_completo') }}" required autocomplete="off" placeholder="Nombre completo del beneficiario">
                        <span class="bar"></span>
                        <label class="wave-label">
                            <span class="label-char" style="--index: 0">N</span>
                            <span class="label-char" style="--index: 1">o</span>
                            <span class="label-char" style="--index: 2">m</span>
                            <span class="label-char" style="--index: 3">b</span>
                            <span class="label-char" style="--index: 4">r</span>
                            <span class="label-char" style="--index: 5">e</span>
                            <span class="label-char" style="--index: 6">&nbsp;</span>
                            <span class="label-char" style="--index: 7">C</span>
                            <span class="label-char" style="--index: 8">o</span>
                            <span class="label-char" style="--index: 9">m</span>
                            <span class="label-char" style="--index: 10">p</span>
                            <span class="label-char" style="--index: 11">l</span>
                            <span class="label-char" style="--index: 12">e</span>
                            <span class="label-char" style="--index: 13">t</span>
                            <span class="label-char" style="--index: 14">o</span>
                            <span class="label-asterisk">*</span>
                        </label>
                    </div>
                </div>

                {{-- Fila 3: DNI --}}
                <div class="form-grid-full">
                    <div class="wave-group">
                        <input type="text" id="dni_field" name="dni" class="input" value="{{ old('dni') }}" placeholder="xxxx-xxxx-xxxxx" maxlength="15" autocomplete="off" required>
                        <span class="bar"></span>
                        <label class="wave-label">
                            <span class="label-char" style="--index: 0">D</span>
                            <span class="label-char" style="--index: 1">N</span>
                            <span class="label-char" style="--index: 2">I</span>
                            <span class="label-char" style="--index: 3">&nbsp;</span>
                            <span class="label-char" style="--index: 4">(</span>
                            <span class="label-char" style="--index: 5">x</span>
                            <span class="label-char" style="--index: 6">x</span>
                            <span class="label-char" style="--index: 7">x</span>
                            <span class="label-char" style="--index: 8">x</span>
                            <span class="label-char" style="--index: 9">-</span>
                            <span class="label-char" style="--index: 10">x</span>
                            <span class="label-char" style="--index: 11">x</span>
                            <span class="label-char" style="--index: 12">x</span>
                            <span class="label-char" style="--index: 13">x</span>
                            <span class="label-char" style="--index: 14">-</span>
                            <span class="label-char" style="--index: 15">x</span>
                            <span class="label-char" style="--index: 16">x</span>
                            <span class="label-char" style="--index: 17">x</span>
                            <span class="label-char" style="--index: 18">x</span>
                            <span class="label-char" style="--index: 19">x</span>
                            <span class="label-char" style="--index: 20">)</span>
                        </label>
                        <div id="dni-alert-box" class="dni-alert"></div>
                    </div>
                </div>

                {{-- Fila 4: COLONIA / BARRIO | Dirección Completa --}}
                <div class="form-grid-2">
                    <div class="colonia-select-group">
                        <label class="colonia-select-label">COLONIA / BARRIO</label>
                        <select name="direccion" class="colonia-custom-select">
                            <option value="">-- Seleccione una colonia --</option>
                            @foreach($colonias as $col)
                                <option value="{{ $col->COLONIA }}" {{ old('direccion') == $col->COLONIA ? 'selected' : '' }}>
                                    {{ $col->COD_COL }} - {{ $col->COLONIA }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="wave-group" style="margin-top: 18px;">
                        <input type="text" name="direccion_detalle" class="input" value="{{ old('direccion_detalle') }}" placeholder="Dirección exacta" autocomplete="off">
                        <span class="bar"></span>
                        <label class="wave-label">
                            <span class="label-char" style="--index: 0">D</span>
                            <span class="label-char" style="--index: 1">i</span>
                            <span class="label-char" style="--index: 2">r</span>
                            <span class="label-char" style="--index: 3">e</span>
                            <span class="label-char" style="--index: 4">c</span>
                            <span class="label-char" style="--index: 5">c</span>
                            <span class="label-char" style="--index: 6">i</span>
                            <span class="label-char" style="--index: 7">ó</span>
                            <span class="label-char" style="--index: 8">n</span>
                            <span class="label-char" style="--index: 9">&nbsp;</span>
                            <span class="label-char" style="--index: 10">C</span>
                            <span class="label-char" style="--index: 11">o</span>
                            <span class="label-char" style="--index: 12">m</span>
                            <span class="label-char" style="--index: 13">p</span>
                            <span class="label-char" style="--index: 14">l</span>
                            <span class="label-char" style="--index: 15">e</span>
                            <span class="label-char" style="--index: 16">t</span>
                            <span class="label-char" style="--index: 17">a</span>
                        </label>
                    </div>
                </div>

                <div class="form-actions-footer">
                    <a href="{{ route('adulto-mayor.index') }}" class="btn-cancel-link">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-guardar-main" id="btn-submit-form">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.wave-group .input').forEach(input => {
                    const updateState = () => {
                        if (input.value && input.value.trim() !== '') {
                            input.classList.add('has-value');
                        } else {
                            input.classList.remove('has-value');
                        }
                    };
                    updateState();
                    input.addEventListener('input', updateState);
                    input.addEventListener('change', updateState);
                    input.addEventListener('blur', updateState);
                });

                const dniInput = document.getElementById('dni_field');
                const dniAlert = document.getElementById('dni-alert-box');
                const btnSubmit = document.getElementById('btn-submit-form');
                let timeoutId = null;

                if (dniInput) {
                    dniInput.addEventListener('input', function() {
                        let value = this.value.replace(/\D/g, '');
                        let formatted = '';

                        if (value.length > 0) {
                            formatted = value.substring(0, 4);
                            if (value.length > 4) {
                                formatted += '-' + value.substring(4, 8);
                                if (value.length > 8) {
                                    formatted += '-' + value.substring(8, 13);
                                }
                            }
                        }
                        this.value = formatted;

                        if (timeoutId) clearTimeout(timeoutId);

                        if (value.length === 13) {
                            dniAlert.className = 'dni-alert show warning';
                            dniAlert.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando DNI...';

                            timeoutId = setTimeout(() => {
                                fetch(`{{ route('adulto-mayor.check-dni') }}?dni=${encodeURIComponent(formatted)}`, {
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.exists) {
                                        dniAlert.className = 'dni-alert show danger';
                                        dniAlert.innerHTML = `<i class="fas fa-exclamation-triangle"></i> <strong>DNI Duplicado:</strong> Registrado a nombre de ${data.nombre}`;
                                        if (btnSubmit) btnSubmit.disabled = true;
                                    } else {
                                        dniAlert.className = 'dni-alert show success';
                                        dniAlert.innerHTML = '<i class="fas fa-check-circle"></i> DNI Válido y disponible';
                                        if (btnSubmit) btnSubmit.disabled = false;
                                    }
                                })
                                .catch(() => {
                                    dniAlert.className = 'dni-alert show warning';
                                    dniAlert.innerHTML = 'No se pudo verificar el DNI automáticamente.';
                                    if (btnSubmit) btnSubmit.disabled = false;
                                });
                            }, 400);
                        } else if (value.length > 0) {
                            dniAlert.className = 'dni-alert show warning';
                            dniAlert.innerHTML = `El DNI debe contener 13 dígitos (${value.length}/13)`;
                            if (btnSubmit) btnSubmit.disabled = false;
                        } else {
                            dniAlert.className = 'dni-alert';
                            dniAlert.innerHTML = '';
                            if (btnSubmit) btnSubmit.disabled = false;
                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>
