<x-app-layout>
    @section('title', 'Adulto Mayor')
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between py-2">
            <div>
                <h2 class="font-bold text-xl text-slate-900 leading-none mb-1">
                    {{ __('Registro Adulto Mayor') }}
                </h2>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-[0.2em] m-0">Administración de
                    Adultos Mayores</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                {{-- Buscador Animado --}}
                <form class="search-form" style="width: 250px;">
                    <label>
                        <input type="text" id="search-input" value="{{ request('search') }}" placeholder="Buscar..."
                            required>
                        <div class="icon">
                            <svg class="swap-on" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path
                                    d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z" />
                            </svg>
                            <svg class="swap-off" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path
                                    d="M504.1 256C504.1 119 393 7.9 256 7.9S7.9 119 7.9 256 119 504.1 256 504.1 504.1 393 504.1 256zM256 456c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200z" />
                            </svg>
                        </div>
                        <button class="close-btn" id="btn-clear-search" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill="currentColor"
                                    d="M14.348 14.849a1 1 0 0 1-1.414 0L10 11.414l-2.93 2.435a1 1 0 1 1-1.414-1.414l2.93-2.93-2.93-2.93a1 1 0 0 1 1.414-1.414l2.93 2.93 2.93-2.93a1 1 0 0 1 1.414 1.414l-2.93 2.93 2.93 2.93a1 1 0 0 1 0 1.414z" />
                            </svg>
                        </button>
                    </label>
                </form>

                <button type="button"
                    class="btn btn-primary fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2"
                    onclick="abrirModalNuevo()" style="height: 35px; font-size: 13px;">
                    <i class="fas fa-plus mr-2"></i> NUEVO
                </button>
            </div>
        </div>
    </x-slot>

    @push('css')
        <style>
            /* ══════════════════════════════════════════════════════════════
                   BUSCADOR ANIMADO (Inspirado en WhatsApp)
                   ══════════════════════════════════════════════════════════════ */
            .search-form {
                --input-bg: #fff;
                --padding: 1em;
                --rotate: 80deg;
                --gap: 1.5em;
                --icon-change-color: #15A986;
                --height: 35px;
                padding-inline-end: 0.8em;
                background: var(--input-bg);
                position: relative;
                border-radius: 6px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                transition: box-shadow 0.3s;
            }

            .search-form:focus-within {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            .search-form label {
                display: flex;
                align-items: center;
                width: 100%;
                height: var(--height);
            }

            .search-form input {
                width: 100%;
                padding-inline-start: calc(var(--padding) + var(--gap));
                outline: none;
                background: none;
                border: 0;
                font-size: 0.9rem;
                color: #333;
            }

            .search-form input::placeholder {
                color: #999;
            }

            /* Iconos SVG */
            .search-form svg {
                color: #666;
                transition: 0.3s cubic-bezier(.4, 0, .2, 1);
                position: absolute;
                height: 16px;
                width: 16px;
            }

            /* Icono de búsqueda */
            .search-form .icon {
                position: absolute;
                left: var(--padding);
                transition: 0.3s cubic-bezier(.4, 0, .2, 1);
                display: flex;
                justify-content: center;
                align-items: center;
                pointer-events: none;
            }

            /* Icono alternativo (check circle) */
            .search-form .swap-off {
                transform: rotate(-80deg);
                opacity: 0;
                visibility: hidden;
            }

            /* Botón de cerrar */
            .search-form .close-btn {
                background: none;
                border: none;
                right: calc(var(--padding) - var(--gap) + 0.5em);
                box-sizing: border-box;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #666;
                padding: 0.1em;
                width: 22px;
                height: 22px;
                border-radius: 50%;
                transition: 0.3s;
                opacity: 0;
                transform: scale(0);
                visibility: hidden;
                cursor: pointer;
                position: absolute;
            }

            .search-form .close-btn:hover {
                background: #f0f0f0;
                color: #333;
            }

            /* Animaciones al hacer focus */
            .search-form input:focus~.icon {
                transform: rotate(var(--rotate)) scale(1.3);
            }

            .search-form input:focus~.icon .swap-off {
                opacity: 1;
                transform: rotate(-80deg);
                visibility: visible;
                color: var(--icon-change-color);
            }

            .search-form input:focus~.icon .swap-on {
                opacity: 0;
                visibility: visible;
            }

            /* Animaciones cuando tiene valor */
            .search-form input:valid~.icon {
                transform: scale(1.3) rotate(var(--rotate));
            }

            .search-form input:valid~.icon .swap-off {
                opacity: 1;
                visibility: visible;
                color: var(--icon-change-color);
            }

            .search-form input:valid~.icon .swap-on {
                opacity: 0;
                visibility: visible;
            }

            .search-form input:valid~.close-btn {
                opacity: 1;
                visibility: visible;
                transform: scale(1);
                transition: 0.3s;
            }

            /* ══════════════════════════════════════════════════════════════
                   MODAL PERSONALIZADO RESPONSIVE (COMPACTO)
                   ══════════════════════════════════════════════════════════════ */
            .custom-modal-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(4px);
                z-index: 9999;
                padding: 20px;
                overflow-y: auto;
                animation: fadeIn 0.2s ease;
            }

            .custom-modal-overlay.active {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .custom-modal {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                width: 100%;
                max-width: 600px;
                max-height: 85vh;
                display: flex;
                flex-direction: column;
                animation: slideUp 0.3s ease;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Header del modal - Elegante y Oscuro */
            .custom-modal-header {
                background: #0f172a;
                color: white;
                padding: 12px 20px;
                border-radius: 20px 20px 0 0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-shrink: 0;
                border-bottom: 1px solid #1e293b;
            }

            /* Body del modal */
            .custom-modal-body {
                padding: 16px 20px 8px 20px;
                overflow-y: auto;
                flex: 1;
            }

            /* Wave Group Inputs (Inspirados en Uiverse & Registro Adulto Mayor) */
            .wave-group {
                position: relative;
                margin-top: 6px;
                margin-bottom: 6px;
                width: 100%;
            }

            .wave-group .input {
                font-size: 14px;
                padding: 6px 10px 4px 5px;
                display: block;
                width: 100%;
                border: none;
                border-bottom: 1.5px solid #cbd5e1;
                background: transparent;
                color: #0f172a;
                font-weight: 500;
                transition: border-color 0.2s ease;
            }

            .wave-group .input:focus {
                outline: none;
                box-shadow: none !important;
            }

            .wave-group .label {
                color: #94a3b8;
                font-size: 14px;
                font-weight: 500;
                position: absolute;
                pointer-events: none;
                left: 5px;
                top: 6px;
                display: flex;
            }

            .wave-group .label-char {
                transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1) all;
                transition-delay: calc(var(--index) * 0.04s);
                display: inline-block;
            }

            .wave-group .input:focus~.label .label-char,
            .wave-group .input:not(:placeholder-shown)~.label .label-char,
            .wave-group .input.has-value~.label .label-char {
                transform: translateY(-16px);
                font-size: 11px;
                color: #4f46e5;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
            }

            .wave-group .input:focus~.label .label-char.text-danger,
            .wave-group .input:not(:placeholder-shown)~.label .label-char.text-danger,
            .wave-group .input.has-value~.label .label-char.text-danger {
                color: #ef4444 !important;
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
                transition: 0.25s ease all;
            }

            .wave-group .bar:before {
                left: 50%;
            }

            .wave-group .bar:after {
                right: 50%;
            }

            .wave-group .input:focus~.bar:before,
            .wave-group .input:focus~.bar:after {
                width: 50%;
            }

            /* Selector de Colonia estilo redondeado Imagen 2 */
            .colonia-select-group {
                display: flex;
                flex-direction: column;
                gap: 4px;
                margin-top: 6px;
            }

            .colonia-select-label {
                font-size: 11px;
                font-weight: 800;
                color: #475569;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin: 0;
            }

            .colonia-custom-select {
                width: 100%;
                background-color: #f8fafc;
                border: 1px solid #cbd5e1;
                border-radius: 12px;
                padding: 8px 12px;
                font-size: 13px;
                font-weight: 500;
                color: #334155;
                outline: none;
                cursor: pointer;
                transition: all 0.2s;
                appearance: none;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 20 20' fill='%2364748b'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: right 12px center;
            }

            .colonia-custom-select:focus {
                background-color: #ffffff;
                border-color: #2563eb;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            }

            /* Required indicator */
            .wave-group .label .text-danger {
                margin-left: 3px;
            }

            /* Alertas DNI - Compactas */
            .dni-alert {
                display: none;
                margin-top: 6px;
                padding: 8px 10px;
                border-radius: 8px;
                font-size: 11px;
                font-weight: 600;
                animation: slideDown 0.2s ease;
            }

            .dni-alert.show {
                display: block;
            }

            .dni-alert.warning {
                background: #fef3c7;
                color: #92400e;
                border: 2px solid #fbbf24;
            }

            .dni-alert.success {
                background: #d1fae5;
                color: #065f46;
                border: 2px solid #10b981;
            }

            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Footer del modal - Compacto */
            .custom-modal-footer {
                padding: 10px 16px;
                border-top: 2px solid #f1f5f9;
                display: flex;
                gap: 10px;
                justify-content: flex-end;
                flex-shrink: 0;
                background: #f8fafc;
                border-radius: 0 0 20px 20px;
            }

            .btn-cancel,
            .btn-save {
                padding: 8px 16px;
                border-radius: 10px;
                font-size: 13px;
                font-weight: 700;
                cursor: pointer;
                border: none;
                transition: all 0.2s;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            .btn-cancel {
                background: #e2e8f0;
                color: #475569;
            }

            .btn-cancel:hover {
                background: #cbd5e1;
                transform: translateY(-1px);
            }

            .btn-save {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                color: white;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            }

            .btn-save:hover {
                box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
                transform: translateY(-2px);
            }

            .btn-save:active {
                transform: translateY(0);
            }

            /* Responsive */
            @media (max-width: 768px) {
                .custom-modal {
                    max-width: 100%;
                    margin: 10px;
                    max-height: 92vh;
                    border-radius: 16px;
                }

                .custom-modal-header {
                    padding: 14px 16px;
                    border-radius: 16px 16px 0 0;
                }

                .custom-modal-header h3 {
                    font-size: 15px;
                }

                .custom-modal-body {
                    padding: 16px;
                }

                .form-grid {
                    grid-template-columns: 1fr;
                    gap: 10px;
                }

                .form-group {
                    margin-bottom: 10px;
                }

                .custom-modal-footer {
                    flex-direction: column-reverse;
                    padding: 12px 16px;
                    border-radius: 0 0 16px 16px;
                }

                .btn-cancel,
                .btn-save {
                    width: 100%;
                    justify-content: center;
                    padding: 11px 20px;
                }
            }

            /* ── Cards Estadísticas Modernas ────────────────────────────── */
            .stats-cards-container {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 16px;
                margin-bottom: 20px;
            }

            .stat-card {
                background: #ffffff;
                border: 1px solid #cbd5e1;
                border-radius: 16px;
                padding: 16px 20px;
                display: flex;
                align-items: center;
                gap: 16px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                transition: all 0.2s ease;
            }

            .stat-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                border-color: #94a3b8;
            }

            .stat-icon-wrapper {
                width: 46px;
                height: 46px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 18px;
            }

            .stat-icon-wrapper.indigo {
                background: #eef2ff;
                color: #4f46e5;
            }

            .stat-icon-wrapper.emerald {
                background: #ecfdf5;
                color: #10b981;
            }

            .stat-icon-wrapper.amber {
                background: #fffbeb;
                color: #f59e0b;
            }

            .stat-info {
                display: flex;
                flex-direction: column;
            }

            .stat-label {
                font-size: 11px;
                font-weight: 700;
                color: #475569;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin: 0;
            }

            .stat-value {
                font-size: 22px;
                font-weight: 900;
                color: #0f172a;
                line-height: 1.2;
                margin: 2px 0 0 0;
            }

            .stat-subtext {
                font-size: 11px;
                color: #64748b;
                font-weight: 500;
                margin: 0;
            }

            /* ── Estilo Tabla Compacta con Texto Plano ──────────────── */
            .ata-table-container {
                background-color: #f1f5f9;
                padding: 16px;
                min-height: calc(100vh - 80px);
                display: flex;
                flex-direction: column;
            }

            .ata-card {
                background: #ffffff;
                border: 1px solid #cbd5e1;
                border-radius: 8px;
                flex: 1;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            }

            .ata-table-wrapper {
                flex: 1;
                overflow: auto;
            }

            .table-ata {
                font-family: 'Aptos Narrow', 'Arial Narrow', Arial, sans-serif !important;
                font-size: 13px !important;
                border-collapse: separate;
                border-spacing: 0;
                width: 100%;
            }

            .table-ata thead th {
                background: #0f172a !important;
                color: #ffffff !important;
                font-size: 11px !important;
                font-weight: 800 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
                border-bottom: 2px solid #334155 !important;
                border-right: 1px solid #334155 !important;
                text-align: left !important;
                height: 28px !important;
                line-height: 28px !important;
                padding: 0 8px !important;
                position: sticky;
                top: 0;
                z-index: 10;
                white-space: nowrap;
            }

            .table-ata thead th:last-child {
                border-right: none !important;
            }

            .table-ata tbody td {
                height: 24px !important;
                line-height: 24px !important;
                padding: 0 6px !important;
                border-right: 1px solid #cbd5e1 !important;
                border-bottom: 1px solid #cbd5e1 !important;
                color: #000000 !important;
                font-size: 13px !important;
                font-weight: 400 !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                vertical-align: middle;
            }

            .table-ata tbody td:last-child {
                border-right: none !important;
            }

            .table-ata tbody tr {
                transition: background-color 0.15s ease;
            }

            .table-ata tbody tr:nth-child(odd) td {
                background-color: #ffffff !important;
            }

            .table-ata tbody tr:nth-child(even) td {
                background-color: #f8fafc !important;
            }

            .table-ata tbody tr:hover td {
                background-color: #fefce8 !important;
            }

            .editable:focus {
                outline: 2px solid #3b82f6 !important;
                background-color: #ffffff !important;
                border-radius: 2px;
                z-index: 5;
            }

            /* Footer Estilo ATA */
            .ata-footer {
                background: #ffffff;
                border-top: 1px solid #cbd5e1;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 15px;
                font-size: 11px;
                font-weight: 700;
                color: #475569;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .badge-ata {
                background: #e0e7ff;
                color: #3730a3;
                padding: 2px 8px;
                border-radius: 6px;
                margin-left: 6px;
                font-weight: 800;
            }

            /* Loading Spinner */
            #infinite-scroll-loader {
                display: none;
                text-align: center;
                padding: 8px;
                background: white;
                font-weight: 700;
                font-size: 11px;
                color: #4f46e5;
            }
        </style>
    @endpush

    <div class="ata-table-container">
        {{-- Cards de Estadísticas --}}
        <div class="stats-cards-container">
            <!-- Card: Total Registros -->
            <div class="stat-card">
                <div class="stat-icon-wrapper indigo">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Total Registros</span>
                    <h3 class="stat-value">{{ number_format($total) }}</h3>
                    <span class="stat-subtext">Adultos Mayores</span>
                </div>
            </div>

            <!-- Card: Nuevos este mes -->
            <div class="stat-card">
                <div class="stat-icon-wrapper emerald">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Nuevos este Mes</span>
                    <h3 class="stat-value">{{ number_format($nuevosEsteMes) }}</h3>
                    <span
                        class="stat-subtext">{{ ucfirst(\Carbon\Carbon::now()->locale('es')->translatedFormat('F Y')) }}</span>
                </div>
            </div>

            <!-- Card: Promedio Edad -->
            <div class="stat-card">
                <div class="stat-icon-wrapper amber">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Promedio Edad</span>

                    <h3 class="stat-value">{{ number_format($promedioEdad, 0) }}</h3>
                    <span class="stat-subtext">Años cumplidos</span>
                </div>
            </div>
        </div>

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

        @if(session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: "{{ session('error') }}",
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Entendido'
                    });
                });
            </script>
        @endif

        <div class="ata-card">
            <div class="ata-table-wrapper" id="table-wrapper">
                <table class="table-ata">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">#</th>
                            <th style="width: 120px;">EXPEDIENTE</th>
                            <th style="min-width: 240px;">NOMBRE COMPLETO</th>
                            <th style="width: 130px;">DNI</th>
                            <th class="text-center" style="width: 70px;">EDAD</th>
                            <th style="min-width: 220px;">DIRECCIÓN</th>
                            <th style="width: 110px;">TELÉFONO</th>
                            <th class="text-center" style="width: 80px;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        @foreach($registros as $index => $reg)
                            <tr>
                                <td class="text-center">{{ $registros->firstItem() + $index }}</td>
                                <td contenteditable="true" class="editable" data-id="{{ $reg->id }}"
                                    data-field="expediente">{{ $reg->expediente }}</td>
                                <td contenteditable="true" class="editable" data-id="{{ $reg->id }}"
                                    data-field="nombre_completo">{{ $reg->nombre_completo }}</td>
                                <td contenteditable="true" class="editable" data-id="{{ $reg->id }}" data-field="dni">
                                    {{ $reg->dni }}</td>
                                <td contenteditable="true" class="editable text-center" data-id="{{ $reg->id }}"
                                    data-field="edad">{{ $reg->edad }}</td>
                                <td contenteditable="true" class="editable" data-id="{{ $reg->id }}" data-field="direccion">
                                    {{ $reg->direccion }}</td>
                                <td contenteditable="true" class="editable" data-id="{{ $reg->id }}" data-field="telefono">
                                    {{ $reg->telefono }}</td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button class="btn btn-sm btn-primary py-0 px-1.5 btn-editar"
                                            data-id="{{ $reg->id }}" title="Editar"
                                            style="font-size: 11px; height: 20px; line-height: 1;">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('adulto-mayor.destroy', $reg) }}" method="POST"
                                            class="d-inline form-eliminar m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger py-0 px-1.5" title="Eliminar"
                                                style="font-size: 11px; height: 20px; line-height: 1;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div id="infinite-scroll-loader">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Cargando más registros...
                </div>
            </div>

            <div class="ata-footer">
                <div class="d-flex align-items-center">
                    <span>REGISTROS: <span class="badge-ata"
                            id="total-count">{{ number_format($registros->total()) }}</span></span>
                    <span class="mx-3 text-slate-300">|</span>
                    <span>CARGADOS EN VISTA: <span class="badge-ata"
                            id="loaded-count">{{ $registros->count() }}</span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo (Diseño Dual: Wave / Clásico) -->
    <div class="custom-modal-overlay" id="modalNuevoOverlay">
        <div class="custom-modal">
            <!-- Header con botón de regresar / alternar diseño -->
            <div class="custom-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" onclick="cerrarModalNuevo()"
                        class="text-slate-400 hover:text-indigo-400 p-2 rounded-lg hover:bg-slate-800 transition-colors border-0 bg-transparent"
                        title="Regresar">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-black text-white tracking-tight flex items-center gap-2 m-0">
                            <i class="fas fa-user-plus text-indigo-400"></i>
                            Nuevo Registro - Adulto Mayor
                        </h2>
                        <p class="text-xs text-slate-400 m-0">Formulario interactivo de expedientes</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <!-- Botón para alternar entre el diseño Wave y el Clásico -->
                    <button type="button" id="btn-toggle-design" onclick="toggleDesignMode()"
                        class="text-xs px-3 py-2 rounded-xl border flex items-center gap-2 transition-all font-semibold bg-indigo-600/30 text-indigo-200 border-indigo-400/40 hover:bg-indigo-600/50 cursor-pointer"
                        title="Cambiar entre diseño estilizado (Wave) y clásico">
                        <i class="fas fa-magic text-indigo-400"></i>
                        <span id="txt-toggle-design">Diseño: Wave</span>
                    </button>

                    <button type="button" onclick="cerrarModalNuevo()"
                        class="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition-colors border-0 bg-transparent cursor-pointer">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="custom-modal-body">
                <!-- Alerta de validación JS -->
                <div id="js-error-alert"
                    class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-medium mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-red-500 shrink-0"></i>
                    <span id="js-error-text"></span>
                </div>

                <form id="registro-form" action="{{ route('adulto-mayor.store') }}" method="POST"
                    onsubmit="return handleFormSubmit(event)">
                    @csrf


                    <!-- MODO DISEÑO WAVE ANIMADO -->
                    <div id="modal-wave-fields" class="space-y-2">
                        <!-- FILA 1: DNI (IDENTIDAD) | EXPEDIENTE -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                            <div class="wave-group">
                                <input type="text" id="wave_dni" name="dni" placeholder=" " maxlength="15" class="input"
                                    autocomplete="off" oninput="handleDniInput('wave')">
                                <span class="bar"></span>
                                <label class="label">
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
                            </div>

                            <div class="wave-group">
                                <input type="text" id="wave_expediente" name="expediente" placeholder=" " class="input"
                                    autocomplete="off" oninput="syncFields('wave')">
                                <span class="bar"></span>
                                <label class="label">
                                    <span class="label-char" style="--index: 0">E</span>
                                    <span class="label-char" style="--index: 1">x</span>
                                    <span class="label-char" style="--index: 2">p</span>
                                    <span class="label-char" style="--index: 3">e</span>
                                    <span class="label-char" style="--index: 4">d</span>
                                    <span class="label-char" style="--index: 5">i</span>
                                    <span class="label-char" style="--index: 6">e</span>
                                    <span class="label-char" style="--index: 7">n</span>
                                    <span class="label-char" style="--index: 8">t</span>
                                    <span class="label-char" style="--index: 9">e</span>
                                </label>
                            </div>
                        </div>

                        <!-- FILA 2: NOMBRE COMPLETO | EDAD -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                            <div class="wave-group">
                                <input type="text" id="wave_nombre_completo" name="nombre_completo" placeholder=" "
                                    class="input" autocomplete="off" oninput="syncFields('wave')">
                                <span class="bar"></span>
                                <label class="label">
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
                                    <span class="label-char text-danger font-bold ml-1" style="--index: 15">*</span>
                                </label>
                            </div>

                            <div class="wave-group">
                                <input type="number" id="wave_edad" name="edad" min="0" max="150" placeholder=" "
                                    class="input" autocomplete="off" oninput="syncFields('wave')">
                                <span class="bar"></span>
                                <label class="label">
                                    <span class="label-char" style="--index: 0">E</span>
                                    <span class="label-char" style="--index: 1">d</span>
                                    <span class="label-char" style="--index: 2">a</span>
                                    <span class="label-char" style="--index: 3">d</span>
                                </label>
                            </div>
                        </div>

                        <!-- FILA 3: COLONIA / BARRIO (DIRECCIÓN) | TELÉFONO -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                            <div class="colonia-select-group" style="margin-top: 10px;">
                                <label class="colonia-select-label">COLONIA / BARRIO (DIRECCIÓN)</label>
                                <select id="wave_colonia" name="direccion" class="colonia-custom-select"
                                    onchange="syncFields('wave')">
                                    <option value="">-- Seleccione una colonia --</option>
                                    @foreach($colonias as $col)
                                        <option value="{{ $col->COLONIA }}">{{ $col->COD_COL }} - {{ $col->COLONIA }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="wave-group" style="margin-top: 10px;">
                                <input type="text" id="wave_telefono" name="telefono" maxlength="10" placeholder=" "
                                    class="input" autocomplete="off" oninput="handleTelefonoInput('wave')">
                                <span class="bar"></span>
                                <label class="label">
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
                    </div>

                    <!-- MODO DISEÑO CLÁSICO (HTML Estándar) -->
                    <div id="modal-clasico-fields" class="space-y-2 hidden">
                        <!-- FILA 1: DNI (IDENTIDAD) | EXPEDIENTE -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    DNI <span class="text-slate-400 font-normal lowercase">(xxxx-xxxx-xxxxx)</span>
                                </label>
                                <input type="text" id="clasico_dni" placeholder="xxxx-xxxx-xxxxx" maxlength="15"
                                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 outline-none transition-all"
                                    autocomplete="off" oninput="handleDniInput('clasico')">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Expediente
                                </label>
                                <input type="text" id="clasico_expediente" placeholder="Ej: EXP-2026-0000"
                                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 outline-none transition-all"
                                    autocomplete="off" oninput="syncFields('clasico')">
                            </div>
                        </div>

                        <!-- FILA 2: NOMBRE COMPLETO | EDAD -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Nombre Completo <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="clasico_nombre_completo" placeholder="Apellidos y nombres"
                                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 outline-none transition-all"
                                    autocomplete="off" oninput="syncFields('clasico')">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Edad
                                </label>
                                <input type="number" id="clasico_edad" min="0" max="150" placeholder="Ej: 65"
                                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 outline-none transition-all"
                                    autocomplete="off" oninput="syncFields('clasico')">
                            </div>
                        </div>

                        <!-- FILA 3: COLONIA / BARRIO (DIRECCIÓN) | TELÉFONO -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Colonia / Barrio (Dirección)
                                </label>
                                <select id="clasico_colonia"
                                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 outline-none transition-all"
                                    onchange="syncFields('clasico')">
                                    <option value="">-- Seleccione una colonia --</option>
                                    @foreach($colonias as $col)
                                        <option value="{{ $col->COLONIA }}">{{ $col->COD_COL }} - {{ $col->COLONIA }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
                                    Teléfono <span class="text-slate-400 font-normal lowercase">(xxxx-xx-xx)</span>
                                </label>
                                <input type="text" id="clasico_telefono" placeholder="xxxx-xx-xx" maxlength="10"
                                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2 text-sm focus:bg-white focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 outline-none transition-all"
                                    autocomplete="off" oninput="handleTelefonoInput('clasico')">
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER CON MENSAJE DE DUPLICADO / DISPONIBLE A LA PAR DE CANCELAR -->
                    <div class="custom-modal-footer mt-3 flex items-center justify-between gap-3">
                        <div class="flex-1 min-w-0 pr-2">
                            <div id="dni-checking-spinner"
                                class="hidden text-xs text-indigo-600 font-semibold flex items-center gap-2 animate-pulse">
                                <div
                                    class="w-4 h-4 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin shrink-0">
                                </div>
                                <span>Verificando DNI en el sistema...</span>
                            </div>
                            <div id="dni-alert-nuevo"
                                class="hidden text-xs font-semibold px-3 py-2 rounded-xl flex items-center gap-2 border transition-all truncate">
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" class="btn-cancel" onclick="cerrarModalNuevo()">
                                Cancelar
                            </button>
                            <button type="submit" id="btn-submit" class="btn-save">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Editar Registro Adulto Mayor</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form id="formEditar" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div id="loadingEditar" class="d-none text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-3 text-muted">Cargando datos...</p>
                        </div>
                        <div id="camposEditar">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="font-bold text-sm text-slate-700">Expediente</label>
                                    <input type="text" id="edit_expediente" name="expediente" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="font-bold text-sm text-slate-700">DNI</label>
                                    <input type="text" id="edit_dni" name="dni" class="form-control"
                                        placeholder="xxxx-xxxx-xxxxx" maxlength="15">
                                    <div id="dni-alert-edit" class="hidden mt-2 p-2 rounded text-sm"></div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="font-bold text-sm text-slate-700">Nombre Completo <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="edit_nombre_completo" name="nombre_completo"
                                        class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="font-bold text-sm text-slate-700">Edad</label>
                                    <input type="number" id="edit_edad" name="edad" class="form-control" min="0"
                                        max="150">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="font-bold text-sm text-slate-700">Teléfono</label>
                                    <input type="text" id="edit_telefono" name="telefono" class="form-control"
                                        maxlength="15">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="font-bold text-sm text-slate-700">Dirección</label>
                                    <select id="edit_direccion" name="direccion" class="form-control">
                                        <option value="">-- Seleccione una dirección --</option>
                                        @foreach($colonias as $col)
                                            <option value="{{ $col->COLONIA }}">{{ $col->COD_COL }} - {{ $col->COLONIA }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning text-white"><i
                                class="fas fa-save mr-2"></i>Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tableWrapper = document.getElementById('table-wrapper');
                const tableBody = document.getElementById('table-body');
                const searchInput = document.getElementById('search-input');
                const loader = document.getElementById('infinite-scroll-loader');
                const loadedCountBadge = document.getElementById('loaded-count');
                const btnClearSearch = document.getElementById('btn-clear-search');

                let currentPage = 1;
                let lastPage = {{ $registros->lastPage() }};
                let isLoading = false;
                let searchTimeout = null;

                // ══════════════════════════════════════════════════════════════
                // MÁSCARAS Y VERIFICACIÓN DE DNI
                // ══════════════════════════════════════════════════════════════

                // Función para aplicar máscara de DNI
                function applyDniMask(input) {
                    let value = input.value.replace(/\D/g, ''); // Solo números
                    let formatted = '';

                    // Aplicar formato: xxxx-xxxx-xxxxx (4-4-5)
                    if (value.length > 0) {
                        formatted = value.substring(0, 4);
                    }
                    if (value.length >= 5) {
                        formatted += '-' + value.substring(4, 8);
                    }
                    if (value.length >= 9) {
                        formatted += '-' + value.substring(8, 13);
                    }

                    input.value = formatted;
                    return formatted;
                }

                // Función para verificar DNI
                function checkDniExists(dni, alertElement, originalDni = null) {
                    if (dni.replace(/\D/g, '').length < 13) {
                        alertElement.classList.add('hidden');
                        return;
                    }

                    // Si es edición y el DNI no cambió, no verificar
                    if (originalDni && dni === originalDni) {
                        alertElement.classList.add('hidden');
                        return;
                    }

                    fetch(`{{ route('adulto-mayor.check-dni') }}?dni=${encodeURIComponent(dni)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.exists) {
                                showDniAlert(
                                    alertElement,
                                    `Este DNI ya existe: <strong>${data.nombre}</strong> - Exp: <strong>${data.expediente || 'Sin exp.'}</strong>`,
                                    'warning'
                                );
                            } else {
                                showDniAlert(alertElement, 'DNI disponible ✓', 'success');
                            }
                        })
                        .catch(error => {
                            console.error('Error al verificar DNI:', error);
                        });
                }

                function showDniAlert(element, message, type) {
                    element.classList.remove('hidden', 'bg-warning', 'bg-success', 'bg-danger', 'text-dark', 'text-white', 'border');
                    element.classList.add('border');

                    if (type === 'warning') {
                        element.classList.add('bg-warning', 'text-dark');
                        element.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>${message}`;
                    } else if (type === 'success') {
                        element.classList.add('bg-success', 'text-white');
                        element.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${message}`;
                    }
                }

                // Modal NUEVO - DNI
                const dniNuevo = document.getElementById('dni_nuevo');
                const dniAlertNuevo = document.getElementById('dni-alert-nuevo');
                let checkTimeoutNuevo = null;

                if (dniNuevo) {
                    dniNuevo.addEventListener('input', function () {
                        const formatted = applyDniMask(this);
                        clearTimeout(checkTimeoutNuevo);
                        checkTimeoutNuevo = setTimeout(() => {
                            checkDniExists(formatted, dniAlertNuevo);
                        }, 500);
                    });
                }

                // Modal EDITAR - DNI
                const dniEdit = document.getElementById('edit_dni');
                const dniAlertEdit = document.getElementById('dni-alert-edit');
                let checkTimeoutEdit = null;
                let originalEditDni = null;

                if (dniEdit) {
                    dniEdit.addEventListener('input', function () {
                        const formatted = applyDniMask(this);
                        clearTimeout(checkTimeoutEdit);
                        checkTimeoutEdit = setTimeout(() => {
                            checkDniExists(formatted, dniAlertEdit, originalEditDni);
                        }, 500);
                    });
                }

                // ══════════════════════════════════════════════════════════════
                // FIN MÁSCARAS Y VERIFICACIÓN DE DNI
                // ══════════════════════════════════════════════════════════════

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
                        search: searchInput.value
                    });

                    fetch(`{{ route('adulto-mayor.index') }}?${params.toString()}`, {
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

                btnClearSearch.addEventListener('click', () => {
                    searchInput.value = '';
                    triggerSearch();
                });

                // Variable para almacenar el ID del registro a editar
                let currentEditId = null;

                // DELEGACIÓN DE EVENTOS PARA EDITAR
                document.addEventListener('click', function (e) {
                    const btnEdit = e.target.closest('.btn-editar');
                    if (btnEdit) {
                        e.preventDefault();
                        currentEditId = btnEdit.dataset.id;

                        const form = document.getElementById('formEditar');
                        form.action = `/adulto-mayor/${currentEditId}`;

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
                        const loading = document.getElementById('loadingEditar');
                        const campos = document.getElementById('camposEditar');

                        loading.style.setProperty('display', 'flex', 'important');
                        campos.style.opacity = '0.3';

                        fetch(`/adulto-mayor/${currentEditId}/edit`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(r => {
                                if (!r.ok) return r.text().then(t => { throw new Error(t) });
                                return r.json();
                            })
                            .then(data => {
                                if (data.error) throw new Error(data.error);

                                document.getElementById('edit_expediente').value = data.expediente || '';
                                document.getElementById('edit_dni').value = data.dni || '';
                                originalEditDni = data.dni || ''; // Guardar DNI original
                                document.getElementById('edit_nombre_completo').value = data.nombre_completo || '';
                                document.getElementById('edit_edad').value = data.edad || '';
                                document.getElementById('edit_telefono').value = data.telefono || '';
                                document.getElementById('edit_direccion').value = data.direccion || '';
                            })
                            .catch(err => {
                                console.error('Error loading data:', err);
                                alert('Error al cargar datos: ' + err.message);
                            })
                            .finally(() => {
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

                // Eventos de edición inline (Delegados)
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

            let currentDesignMode = localStorage.getItem('preferred_modal_design') || 'wave';
            let dniCheckResultGlobal = null;
            let checkDniTimeoutGlobal = null;

            window.toggleDesignMode = function () {
                currentDesignMode = (currentDesignMode === 'wave') ? 'clasico' : 'wave';
                localStorage.setItem('preferred_modal_design', currentDesignMode);
                applyDesignMode(currentDesignMode);
            };

            window.applyDesignMode = function (mode) {
                currentDesignMode = mode;
                const waveFields = document.getElementById('modal-wave-fields');
                const clasicoFields = document.getElementById('modal-clasico-fields');
                const btnToggle = document.getElementById('btn-toggle-design');
                const txtToggle = document.getElementById('txt-toggle-design');

                if (!waveFields || !clasicoFields) return;

                if (currentDesignMode === 'wave') {
                    syncFields('clasico');
                    waveFields.classList.remove('hidden');
                    clasicoFields.classList.add('hidden');
                    if (btnToggle && txtToggle) {
                        btnToggle.className = 'text-xs px-3 py-2 rounded-xl border flex items-center gap-2 transition-all font-semibold bg-indigo-600/30 text-indigo-200 border-indigo-400/40 hover:bg-indigo-600/50 cursor-pointer';
                        txtToggle.innerText = 'Diseño: Wave';
                    }
                } else {
                    syncFields('wave');
                    clasicoFields.classList.remove('hidden');
                    waveFields.classList.add('hidden');
                    if (btnToggle && txtToggle) {
                        btnToggle.className = 'text-xs px-3 py-2 rounded-xl border flex items-center gap-2 transition-all font-semibold bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700 cursor-pointer';
                        txtToggle.innerText = 'Diseño: Clásico';
                    }
                }
            };

            window.syncFields = function (sourceMode) {
                if (sourceMode === 'wave') {
                    document.getElementById('clasico_edad').value = document.getElementById('wave_edad').value;
                    document.getElementById('clasico_telefono').value = document.getElementById('wave_telefono').value;
                    document.getElementById('clasico_nombre_completo').value = document.getElementById('wave_nombre_completo').value;
                    document.getElementById('clasico_dni').value = document.getElementById('wave_dni').value;
                    document.getElementById('clasico_colonia').value = document.getElementById('wave_colonia').value;
                    document.getElementById('clasico_expediente').value = document.getElementById('wave_expediente').value;
                } else {
                    document.getElementById('wave_edad').value = document.getElementById('clasico_edad').value;
                    document.getElementById('wave_telefono').value = document.getElementById('clasico_telefono').value;
                    document.getElementById('wave_nombre_completo').value = document.getElementById('clasico_nombre_completo').value;
                    document.getElementById('wave_dni').value = document.getElementById('clasico_dni').value;
                    document.getElementById('wave_colonia').value = document.getElementById('clasico_colonia').value;
                    document.getElementById('wave_expediente').value = document.getElementById('clasico_expediente').value;
                }
                updateWaveInputStates();
            };

            function updateWaveInputStates() {
                document.querySelectorAll('.wave-group input').forEach(input => {
                    if (input.value && input.value.trim() !== '') {
                        input.classList.add('has-value');
                    } else {
                        input.classList.remove('has-value');
                    }
                });
            }

            window.handleTelefonoInput = function (sourceMode) {
                const targetInput = (sourceMode === 'wave') ? document.getElementById('wave_telefono') : document.getElementById('clasico_telefono');
                let value = targetInput.value.replace(/\D/g, '');
                let formatted = '';

                if (value.length > 0) formatted = value.substring(0, 4);
                if (value.length >= 5) formatted += '-' + value.substring(4, 6);
                if (value.length >= 7) formatted += '-' + value.substring(6, 8);

                document.getElementById('wave_telefono').value = formatted;
                document.getElementById('clasico_telefono').value = formatted;

                updateWaveInputStates();
            };

            window.applyPhoneMask = function (input) {
                let value = input.value.replace(/\D/g, '');
                let formatted = '';

                if (value.length > 0) formatted = value.substring(0, 4);
                if (value.length >= 5) formatted += '-' + value.substring(4, 6);
                if (value.length >= 7) formatted += '-' + value.substring(6, 8);

                input.value = formatted;
                return formatted;
            };

            window.calculateAgeFromBirthDate = function (dateStr) {
                if (!dateStr) return null;
                const parts = dateStr.split('/');
                if (parts.length === 3) {
                    const day = parseInt(parts[0], 10);
                    const month = parseInt(parts[1], 10) - 1;
                    const year = parseInt(parts[2], 10);
                    const birthDate = new Date(year, month, day);
                    const today = new Date();
                    let age = today.getFullYear() - birthDate.getFullYear();
                    const m = today.getMonth() - birthDate.getMonth();
                    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                        age--;
                    }
                    return (age >= 0 && age < 130) ? age : null;
                }
                return null;
            };

            window.handleDniInput = function (sourceMode) {
                const targetInput = (sourceMode === 'wave') ? document.getElementById('wave_dni') : document.getElementById('clasico_dni');
                let value = targetInput.value.replace(/\D/g, '');
                let formatted = '';

                if (value.length > 0) formatted = value.substring(0, 4);
                if (value.length >= 5) formatted += '-' + value.substring(4, 8);
                if (value.length >= 9) formatted += '-' + value.substring(8, 13);

                document.getElementById('wave_dni').value = formatted;
                document.getElementById('clasico_dni').value = formatted;

                updateWaveInputStates();

                const jsErrorAlert = document.getElementById('js-error-alert');
                if (jsErrorAlert) jsErrorAlert.classList.add('hidden');

                clearTimeout(checkDniTimeoutGlobal);
                const alertContainer = document.getElementById('dni-alert-nuevo');
                const spinner = document.getElementById('dni-checking-spinner');

                if (value.length >= 13) {
                    spinner.classList.remove('hidden');
                    alertContainer.classList.add('hidden');

                    checkDniTimeoutGlobal = setTimeout(() => {
                        fetch(`{{ route('adulto-mayor.check-dni') }}?dni=${encodeURIComponent(formatted)}`)
                            .then(r => r.json())
                            .then(data => {
                                dniCheckResultGlobal = data;

                                if (data.exists) {
                                    spinner.classList.add('hidden');
                                    alertContainer.classList.remove('hidden');
                                    alertContainer.className = 'text-xs font-semibold px-3 py-2 rounded-xl flex items-center gap-2 border transition-all truncate bg-amber-50 border-amber-300 text-amber-900';
                                    alertContainer.innerHTML = `
                                        <i class="fas fa-exclamation-triangle text-amber-600 text-sm shrink-0"></i>
                                        <div class="truncate">
                                            <span class="font-bold">⚠️ DNI Registrado:</span>
                                            <span class="text-[11px]">${data.nombre} (${data.expediente || 'Sin exp.'})</span>
                                        </div>
                                    `;
                                } else {
                                    // Si no existe en BD local, consultar a SESAL en segundo plano
                                    spinner.classList.remove('hidden');
                                    spinner.innerHTML = `
                                        <div class="w-4 h-4 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin shrink-0"></div>
                                        <span>Consultando datos en SESAL...</span>
                                    `;

                                    fetch(`{{ route('prueba.consulta.buscar') }}?identidad=${encodeURIComponent(formatted)}`, {
                                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                        .then(r => r.json())
                                        .then(sesalData => {
                                            console.log('SESAL DATA RECEIVED:', sesalData);
                                            spinner.classList.add('hidden');
                                            alertContainer.classList.remove('hidden');

                                            if (sesalData && sesalData.success) {
                                                if (sesalData.nombre_completo) {
                                                    const wNom = document.getElementById('wave_nombre_completo');
                                                    const cNom = document.getElementById('clasico_nombre_completo');
                                                    if (wNom) wNom.value = sesalData.nombre_completo;
                                                    if (cNom) cNom.value = sesalData.nombre_completo;
                                                }
                                                if (sesalData.fecha_nacimiento) {
                                                    const calculatedAge = calculateAgeFromBirthDate(sesalData.fecha_nacimiento);
                                                    if (calculatedAge !== null) {
                                                        const wEdad = document.getElementById('wave_edad');
                                                        const cEdad = document.getElementById('clasico_edad');
                                                        if (wEdad) wEdad.value = calculatedAge;
                                                        if (cEdad) cEdad.value = calculatedAge;
                                                    }
                                                }
                                                if (sesalData.telefono) {
                                                    const wTel = document.getElementById('wave_telefono');
                                                    const cTel = document.getElementById('clasico_telefono');
                                                    if (wTel) wTel.value = sesalData.telefono;
                                                    if (cTel) cTel.value = sesalData.telefono;
                                                    handleTelefonoInput(currentDesignMode);
                                                }
                                                updateWaveInputStates();

                                                alertContainer.className = 'text-xs font-semibold px-3 py-2 rounded-xl flex items-center gap-2 border transition-all truncate bg-emerald-50 border-emerald-300 text-emerald-800';
                                                alertContainer.innerHTML = `
                                                    <i class="fas fa-check-circle text-emerald-600 text-sm shrink-0"></i>
                                                    <span class="font-bold text-xs text-emerald-700">✓ Datos cargados desde SESAL</span>
                                                `;
                                            } else {
                                                alertContainer.className = 'text-xs font-semibold px-3 py-2 rounded-xl flex items-center gap-2 border transition-all truncate bg-emerald-50 border-emerald-300 text-emerald-800';
                                                alertContainer.innerHTML = `
                                                    <i class="fas fa-check-circle text-emerald-600 text-sm shrink-0"></i>
                                                    <span class="font-bold text-xs text-emerald-700">✓ DNI disponible para registro</span>
                                                `;
                                            }
                                        })
                                        .catch(err => {
                                            console.error('Error al consultar SESAL:', err);
                                            spinner.classList.add('hidden');
                                            alertContainer.classList.remove('hidden');
                                            alertContainer.className = 'text-xs font-semibold px-3 py-2 rounded-xl flex items-center gap-2 border transition-all truncate bg-emerald-50 border-emerald-300 text-emerald-800';
                                            alertContainer.innerHTML = `
                                                <i class="fas fa-check-circle text-emerald-600 text-sm shrink-0"></i>
                                                <span class="font-bold text-xs text-emerald-700">✓ DNI disponible para registro</span>
                                            `;
                                        });
                                }
                            })
                            .catch(err => {
                                spinner.classList.add('hidden');
                                console.error('Error al verificar DNI:', err);
                            });
                    }, 300);
                } else {
                    spinner.classList.add('hidden');
                    alertContainer.classList.add('hidden');
                    dniCheckResultGlobal = null;
                }
            };

            window.handleFormSubmit = function (e) {
                syncFields(currentDesignMode);

                const nombre = document.getElementById('wave_nombre_completo').value.trim();
                const jsErrorAlert = document.getElementById('js-error-alert');
                const jsErrorText = document.getElementById('js-error-text');

                if (!nombre) {
                    e.preventDefault();
                    jsErrorText.innerText = 'Por favor complete todos los campos obligatorios (*).';
                    jsErrorAlert.classList.remove('hidden');
                    return false;
                }

                if (dniCheckResultGlobal && dniCheckResultGlobal.exists) {
                    e.preventDefault();
                    jsErrorText.innerText = 'No se puede guardar el registro. El DNI ingresado ya pertenece a otra persona.';
                    jsErrorAlert.classList.remove('hidden');
                    return false;
                }

                if (jsErrorAlert) jsErrorAlert.classList.add('hidden');
                return true;
            };

            window.abrirModalNuevo = function () {
                const modal = document.getElementById('modalNuevoOverlay');
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';

                const form = document.getElementById('registro-form');
                if (form) form.reset();

                document.querySelectorAll('.wave-group input').forEach(input => {
                    input.classList.remove('has-value');
                });

                const alertDni = document.getElementById('dni-alert-nuevo');
                if (alertDni) {
                    alertDni.classList.add('hidden');
                    alertDni.innerHTML = '';
                }
                const jsErrorAlert = document.getElementById('js-error-alert');
                if (jsErrorAlert) jsErrorAlert.classList.add('hidden');
                const spinner = document.getElementById('dni-checking-spinner');
                if (spinner) spinner.classList.add('hidden');

                dniCheckResultGlobal = null;

                const savedMode = localStorage.getItem('preferred_modal_design') || 'wave';
                applyDesignMode(savedMode);
            };

            window.cerrarModalNuevo = function () {
                const modal = document.getElementById('modalNuevoOverlay');
                if (modal) modal.classList.remove('active');
                document.body.style.overflow = '';
            };

            document.getElementById('modalNuevoOverlay').addEventListener('click', function (e) {
                if (e.target === this) {
                    cerrarModalNuevo();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    const modal = document.getElementById('modalNuevoOverlay');
                    if (modal && modal.classList.contains('active')) {
                        cerrarModalNuevo();
                    }
                }
            });

            // ══════════════════════════════════════════════════════════════
            // FIN FUNCIONES MODAL PERSONALIZADO
            // ══════════════════════════════════════════════════════════════

            function saveChange(id, field, value, element) {
                element.classList.add('bg-warning', 'bg-opacity-10');
                fetch(`/adulto-mayor/${id}/ajax`, {
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
        </script>
    @endpush
</x-app-layout>