<!-- 1. Encabezado de la Vista -->
<div class="svs-header-card no-print">
    <div class="svs-header-title">
        <div class="svs-header-icon">
            <i class="fas fa-file-medical"></i>
        </div>
        <div>
            <h5 class="mb-0 font-weight-bold" style="font-size: 0.95rem; color: var(--text-primary);">
                Consulta y Registro SNVS
            </h5>
            <small style="color: var(--text-muted); font-size: 0.72rem; font-weight: 500;">
                Notificaciones Sanitarias Obligatorias de Vigilancia Epidemiológica
            </small>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 font-weight-bold px-3" style="height: 32px; font-size: 0.8rem; border-color: var(--border-color); color: var(--text-primary);">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>
</div>

<!-- 2. Tarjetas de Métricas en 1 Sola Línea Horizontal -->
<div class="svs-stat-grid no-print">
    <div class="svs-stat-card">
        <div>
            <span class="text-xs text-muted font-weight-bold uppercase d-block" style="font-size: 0.68rem;">TOTAL CASOS</span>
            <span class="font-weight-bold font-size-16" style="color: var(--text-primary);">{{ number_format($totalCasos ?? count($rows)) }}</span>
        </div>
        <div class="svs-stat-icon icon-grad-blue">
            <i class="fas fa-users"></i>
        </div>
    </div>

    <div class="svs-stat-card">
        <div>
            <span class="text-xs text-muted font-weight-bold uppercase d-block" style="font-size: 0.68rem;">NOTIFICADOS</span>
            <span class="font-weight-bold font-size-16 text-success">{{ number_format($totalNotificados ?? 0) }}</span>
        </div>
        <div class="svs-stat-icon icon-grad-emerald">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>

    <div class="svs-stat-card">
        <div>
            <span class="text-xs text-muted font-weight-bold uppercase d-block" style="font-size: 0.68rem;">PENDIENTES</span>
            <span class="font-weight-bold font-size-16 text-warning">{{ number_format($totalPendientes ?? 0) }}</span>
        </div>
        <div class="svs-stat-icon icon-grad-amber">
            <i class="fas fa-clock"></i>
        </div>
    </div>

    <div class="svs-stat-card">
        <div>
            <span class="text-xs text-muted font-weight-bold uppercase d-block" style="font-size: 0.68rem;">DIARREAS</span>
            <span class="font-weight-bold font-size-16 text-danger">{{ number_format($totalDiarreas ?? 0) }}</span>
        </div>
        <div class="svs-stat-icon icon-grad-rose">
            <i class="fas fa-biohazard"></i>
        </div>
    </div>

    <div class="svs-stat-card">
        <div>
            <span class="text-xs text-muted font-weight-bold uppercase d-block" style="font-size: 0.68rem;">DENGUE / ARBO</span>
            <span class="font-weight-bold font-size-16" style="color: #8b5cf6;">{{ number_format($totalDengue ?? 0) }}</span>
        </div>
        <div class="svs-stat-icon icon-grad-purple">
            <i class="fas fa-mosquito"></i>
        </div>
    </div>
</div>

<!-- 3. Barra de Filtros en 1 Sola Línea Horizontal -->
<div class="svs-filter-card no-print">
    <form id="filter-form-svs" action="{{ route('informes.notificacion_svs') }}" method="GET" class="svs-filter-form">
        <!-- Búsqueda General con Icono Integrado -->
        <div style="position: relative; flex: 2.5; min-width: 220px; display: flex; align-items: center;">
            <i class="bi bi-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.8rem; pointer-events: none;"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar DNI, Paciente, Expediente, Médico, Colonia..." 
                   class="ajax-filter-svs svs-filter-input font-weight-bold" style="padding-left: 30px;">
        </div>

        <!-- Filtro Año -->
        <div style="width: 100px; flex-shrink: 0;">
            <select name="ano" class="ajax-filter-svs svs-filter-select font-weight-bold">
                @foreach($anos as $a)
                    <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filtro Mes -->
        <div style="width: 125px; flex-shrink: 0;">
            <select name="mes" class="ajax-filter-svs svs-filter-select font-weight-bold">
                @foreach($meses as $m)
                    <option value="{{ $m }}" {{ $m == $mes ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filtro SE -->
        <div style="width: 145px; flex-shrink: 0;">
            <select name="se" class="ajax-filter-svs svs-filter-select font-weight-bold">
                <option value="TODAS">SEMANA (TODAS)</option>
                @foreach($semanas as $s)
                    <option value="{{ $s }}" {{ $s == $se ? 'selected' : '' }}>SE {{ $s }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filtro Enfermedad SNVS -->
        <div style="width: 240px; flex-shrink: 0;">
            <select name="enfermedad" class="ajax-filter-svs svs-filter-select font-weight-bold">
                <option value="TODAS">ENFERMEDAD SNVS (TODAS)</option>
                @foreach($enfermedadesList as $enf)
                    <option value="{{ $enf }}" {{ $enf == $enfermedadFiltro ? 'selected' : '' }}>{{ $enf }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

<!-- 4. Tabla de Notificaciones Sanitarias Obligatorias: Texto Plano con Scroll Horizontal Fijo -->
<div class="svs-table-container">
    <div class="svs-table-scroll custom-scrollbar">
        <table class="svs-table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">#</th>
                    <th style="width: 105px; text-align: center;">Notificación</th>
                    <th style="width: 95px; text-align: center;">Fecha Consulta</th>
                    <th style="width: 60px; text-align: center;">SE</th>
                    <th style="width: 120px; text-align: center;">Inicio Síntomas</th>
                    <th style="width: 100px; text-align: center;">Expediente</th>
                    <th style="width: 155px; text-align: center;">DNI / Identidad</th>
                    <th style="width: 230px;">Nombre Paciente</th>
                    <th style="width: 95px; text-align: center;">Fecha Nac.</th>
                    <th style="width: 60px; text-align: center;">Edad</th>
                    <th style="width: 60px; text-align: center;">Sexo</th>
                    <th style="width: 120px; text-align: center;">Teléfono</th>
                    <th style="width: 200px;">Colonia / Procedencia</th>
                    <th style="width: 190px;">Médico</th>
                    <th style="width: 200px;">Diagnóstico Consignado</th>
                    <th style="width: 220px;">Diagnóstico Correspondiente (SNVS)</th>
                    <th style="width: 140px;">Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $idx => $r)
                    <tr data-informe-id="{{ $r->informe_id }}">
                        <!-- # -->
                        <td class="text-center font-weight-bold text-muted" style="font-size: 0.75rem;">
                            {{ $idx + 1 }}
                        </td>

                        <!-- ESTADO DE NOTIFICACIÓN: BOTÓN CHECK TIPO ENVIAR AL PRINCIPIO -->
                        <td class="text-center">
                            <button type="button" 
                                    class="btn-notificar-toggle font-weight-bold {{ $r->estado_notificacion === 'Notificado' ? 'is-notificado' : 'is-pendiente' }}" 
                                    data-informe-id="{{ $r->informe_id }}" 
                                    onclick="toggleEstadoNotificacion(this, '{{ $r->informe_id }}')" 
                                    title="Clic para cambiar estado de envío">
                                @if($r->estado_notificacion === 'Notificado')
                                    <i class="bi bi-check-circle-fill"></i> Enviado
                                @else
                                    <i class="bi bi-send"></i> Enviar
                                @endif
                            </button>
                        </td>

                        <!-- Fecha Consulta -->
                        <td class="text-center font-weight-bold">
                            {{ $r->fecha_consulta }}
                        </td>

                        <!-- SE -->
                        <td class="text-center">
                            <span class="badge badge-info px-1.5 py-0.5" style="font-size: 0.72rem; font-weight: 700;">SE {{ $r->se }}</span>
                        </td>
                        
                        <!-- Inicio Síntomas -->
                        <td class="text-center">
                            <input type="date" 
                                   class="table-input-plain input-fecha-sintomas-row font-weight-bold" 
                                   style="width: 110px;"
                                   value="{{ $r->fecha_inicio_sintomas }}" 
                                   data-informe-id="{{ $r->informe_id }}" 
                                   onchange="guardarFechaSintomasFila(this, '{{ $r->informe_id }}')">
                        </td>

                        <!-- Expediente / Historia Clínica -->
                        <td class="cell-expediente text-center">
                            @if(!empty($r->expediente) && $r->expediente !== '-')
                                <span class="badge badge-secondary px-2 py-0.5 font-weight-bold" style="font-size: 0.74rem;">
                                    <i class="bi bi-folder2-open mr-1"></i>{{ $r->expediente }}
                                </span>
                            @else
                                <span class="text-muted" style="font-size: 0.72rem;">S/EXP</span>
                            @endif
                        </td>
                        
                        <!-- DNI / Identidad con Guardado Rápido -->
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center justify-content-center gap-1">
                                <input type="text" 
                                       class="table-input-plain input-dni-row font-monospace font-weight-bold text-success" 
                                       style="width: 115px; font-size: 0.78rem;"
                                       value="{{ $r->no_documento }}" 
                                       data-informe-id="{{ $r->informe_id }}" 
                                       placeholder="0000-0000-00000" 
                                       maxlength="15"
                                       oninput="formatearDniFila(this)" 
                                       onkeydown="if(event.key==='Enter'){ buscarPacienteEnFila(this, '{{ $r->informe_id }}'); }">
                                <button type="button" 
                                        class="btn btn-sm btn-success btn-guardar-row p-0 d-inline-flex align-items-center justify-content-center" 
                                        style="width: 22px; height: 22px; border-radius: 3px;"
                                        onclick="guardarRegistroFila(this, '{{ $r->informe_id }}')" 
                                        title="Guardar DNI y consultar datos">
                                    <i class="fas fa-save" style="font-size: 0.68rem;"></i>
                                </button>
                            </div>
                        </td>

                        <!-- Nombre Paciente -->
                        <td class="cell-paciente">
                            <span class="font-weight-bold d-block text-truncate" style="max-width: 220px; color: var(--text-primary);" title="{{ $r->nombre_paciente }}">
                                {{ $r->nombre_paciente }}
                            </span>
                        </td>

                        <!-- Fecha Nacimiento -->
                        <td class="cell-fecha-nac text-center text-muted" style="font-size: 0.75rem;">
                            {{ $r->fecha_nacimiento ?: '-' }}
                        </td>

                        <!-- Edad -->
                        <td class="cell-edad text-center font-weight-bold">
                            {{ ($r->edad !== null && $r->edad !== '') ? $r->edad . ' a' : '-' }}
                        </td>

                        <!-- Sexo -->
                        <td class="cell-sexo text-center font-weight-bold" style="color: {{ $r->sexo === 'M' ? '#ec4899' : '#3b82f6' }};">
                            {{ $r->sexo === 'M' ? 'Mujer' : ($r->sexo === 'H' ? 'Hombre' : ($r->sexo ?: '-')) }}
                        </td>

                        <!-- Teléfono con Edición Inline -->
                        <td class="cell-telefono text-center">
                            <div class="inline-edit-tel-wrap position-relative d-inline-flex align-items-center justify-content-center gap-1 cursor-pointer" 
                                 data-informe-id="{{ $r->informe_id }}" 
                                 data-value="{{ $r->telefono }}"
                                 title="Clic para editar teléfono">
                                <span class="inline-edit-tel-disp font-monospace {{ $r->telefono && $r->telefono !== '-' ? 'font-weight-bold text-primary' : 'text-muted fst-italic' }}" style="font-size: 0.76rem;">{{ $r->telefono ?: '-' }}</span>
                                <i class="fas fa-pencil-alt text-muted" style="font-size: 0.6rem; opacity: 0.5;"></i>
                                <input type="text" 
                                       class="table-input-plain inline-edit-tel-input d-none text-center font-monospace font-weight-bold" 
                                       style="width: 90px;"
                                       value="{{ $r->telefono !== '-' ? $r->telefono : '' }}" 
                                       placeholder="Teléfono...">
                            </div>
                        </td>

                        <!-- Colonia / Procedencia en Texto Plano -->
                        <td class="cell-direccion">
                            <span class="font-weight-bold d-block text-truncate" style="max-width: 195px;" title="{{ $r->direccion }}">
                                {{ $r->direccion }}
                            </span>
                        </td>

                        <!-- Médico -->
                        <td>
                            <span class="font-weight-bold d-block text-truncate" style="max-width: 185px;" title="{{ $r->medico }}">
                                {{ $r->medico }}
                            </span>
                        </td>
                        
                        <!-- Diagnóstico Consignado -->
                        <td class="cell-diagnostico-consignado">
                            <span class="d-block text-truncate" style="max-width: 195px; color: var(--text-muted);" title="{{ $r->diagnostico_consignado }}">
                                {{ $r->diagnostico_consignado }}
                            </span>
                        </td>

                        <!-- Diagnóstico Correspondiente SNVS -->
                        <td>
                            <select data-informe-id="{{ $r->informe_id }}" 
                                    class="svs-disease-select table-select-plain w-100 font-weight-bold">
                                @foreach($enfermedadesList as $enf)
                                    <option value="{{ $enf }}" {{ $enf == $r->enfermedad_svs ? 'selected' : '' }}>
                                        {{ $enf }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <!-- Observaciones -->
                        <td>
                            <span class="d-block text-truncate text-muted" style="max-width: 135px;" title="{{ $r->observaciones }}">
                                {{ $r->observaciones ?: '-' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="17" class="text-center py-5 text-muted">
                            <i class="fas fa-clipboard-check fa-2x mb-2 d-block opacity-50"></i>
                            No se encontraron casos de notificación obligatoria para los filtros seleccionados
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pie Informativo con Scroll Horizontal Fijo -->
    <div class="svs-pagination-wrapper no-print">
        <div>
            Mostrando <strong class="text-primary">{{ count($rows) }}</strong> casos de notificación obligatoria
        </div>
        <div class="d-flex align-items-center gap-3">
            <span><i class="fas fa-circle text-success" style="font-size: 0.55rem;"></i> Notificados: <strong class="text-success">{{ $totalNotificados ?? 0 }}</strong></span>
            <span><i class="fas fa-circle text-warning" style="font-size: 0.55rem;"></i> Pendientes: <strong class="text-warning">{{ $totalPendientes ?? 0 }}</strong></span>
        </div>
    </div>
</div>
