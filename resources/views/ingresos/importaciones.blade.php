@extends('layouts.app')

@section('title', 'Registros Importados desde Excel - Estadísticas 1.7')

@push('styles')
<style>
  .app-content {
    padding: 0.75rem 1.25rem 0.5rem !important;
    height: calc(100vh - var(--navbar-height) - var(--footer-height)) !important;
    max-height: calc(100vh - var(--navbar-height) - var(--footer-height)) !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
  }

  /* Tarjetas de Métricas en 1 fila */
  .excel-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 0.55rem;
    margin-bottom: 0.55rem;
    flex-shrink: 0;
  }

  .excel-stat-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 6px);
    padding: 0.45rem 0.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow-sm);
  }

  .excel-stat-icon {
    width: 32px;
    height: 32px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: #ffffff;
    flex-shrink: 0;
  }

  .icon-grad-excel   { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
  .icon-grad-blue    { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
  .icon-grad-amber   { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
  .icon-grad-purple  { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
  .icon-grad-slate   { background: linear-gradient(135deg, #64748b 0%, #475569 100%); }

  /* Filtros estrictamente horizontales en 1 sola línea */
  .excel-filter-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 6px);
    padding: 0.45rem 0.75rem;
    margin-bottom: 0.55rem;
    box-shadow: var(--shadow-sm);
    flex-shrink: 0;
  }

  .excel-filter-form {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 0.5rem !important;
    width: 100% !important;
    flex-wrap: nowrap !important;
    margin-bottom: 0 !important;
  }

  .excel-filter-input,
  .excel-filter-select {
    width: 100%;
    height: 32px !important;
    padding: 0 0.65rem;
    font-size: 0.8rem;
    font-weight: 600;
    line-height: 30px;
    border-radius: 4px;
    background-color: var(--bg-body, #1e293b);
    color: var(--text-primary, #f8fafc);
    border: 1px solid var(--border-color, #334155);
    outline: none;
    transition: border-color 0.15s ease;
  }

  .excel-filter-select {
    padding: 0 1.75rem 0 0.65rem;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.6rem center;
    background-size: 11px 9px;
    cursor: pointer;
  }

  .excel-filter-input:focus,
  .excel-filter-select:focus {
    border-color: var(--color-primary, #3b82f6);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
  }

  /* Tabla Contenedora */
  .excel-table-container {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 6px);
    box-shadow: var(--shadow-sm);
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  .excel-table-scroll {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: auto;
  }

  .excel-table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.82rem;
  }

  .excel-table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: var(--bg-subtle, #f8fafc);
    color: var(--text-muted, #64748b);
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.7rem;
    letter-spacing: 0.5px;
    padding: 0.55rem 0.65rem;
    border-bottom: 1px solid var(--border-color);
    white-space: nowrap;
  }

  .excel-table tbody tr {
    transition: background 0.15s ease;
  }

  .excel-table tbody tr:hover {
    background: var(--bg-hover, rgba(0, 0, 0, 0.02));
  }

  .excel-table tbody td {
    padding: 0.45rem 0.65rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
    white-space: nowrap;
  }

  /* Pie Informativo de Scroll */
  .excel-pagination-wrapper {
    padding: 0.4rem 0.75rem;
    background: var(--bg-subtle, #f8fafc);
    border-top: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
  }

  /* Timeline de Seguimiento de Paciente */
  .patient-timeline {
    position: relative;
    padding-left: 24px;
  }

  .patient-timeline::before {
    content: '';
    position: absolute;
    top: 5px;
    bottom: 5px;
    left: 8px;
    width: 2px;
    background: var(--border-color, #cbd5e1);
  }

  .timeline-item {
    position: relative;
    margin-bottom: 1rem;
  }

  .timeline-dot {
    position: absolute;
    left: -24px;
    top: 4px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #10b981;
    border: 3px solid var(--bg-surface);
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
  }
</style>
@endpush

@section('content')
<div class="app-content">

  <!-- Encabezado de Página: Ícono a la par del Título y Subtítulo -->
  <div class="page-header" style="margin-bottom: 0.55rem; flex-shrink: 0; display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; gap: 1rem; width: 100%;">
    
    <!-- Ícono + Título y Subtítulo estrictamente en fila -->
    <div style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 0.75rem;">
      <div class="excel-stat-icon icon-grad-excel shadow-sm" style="width: 40px !important; height: 40px !important; font-size: 1.3rem !important; border-radius: 8px !important; flex-shrink: 0 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
        <i class="bi bi-file-earmark-excel text-white"></i>
      </div>
      <div style="display: flex; flex-direction: column; justify-content: center;">
        <h1 class="page-title" style="font-size: 1.18rem; font-weight: 800; margin: 0; color: var(--text-primary); line-height: 1.25;">
          Registros de Importación Excel
        </h1>
        <span style="font-size: 0.76rem; color: var(--text-muted); line-height: 1.25; margin-top: 1px;">
          Registro universal dinámico con cotejo día-médico, control de duplicidad y carga continua
        </span>
      </div>
    </div>

    <!-- Botones de Acción -->
    <div style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 0.5rem; flex-shrink: 0;">
      <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold shadow-sm" onclick="sincronizarPacientesABd()" style="height: 32px; display: inline-flex; align-items: center; gap: 0.35rem;" title="Sincronizar e importar todos los datos de pacientes a la base de datos de Pacientes BD">
        <i class="bi bi-people-fill text-primary"></i> Sincronizar a Pacientes BD
      </button>
      <a href="{{ route('ingresos.create') }}" class="btn btn-sm btn-outline-secondary font-weight-bold" style="height: 32px; display: inline-flex; align-items: center; gap: 0.35rem;">
        <i class="bi bi-table"></i> Ir a Ingresos AT-1
      </a>
      <button type="button" class="btn btn-sm btn-success font-weight-bold shadow-sm" onclick="abrirModalImportacionExcel()" style="height: 32px; display: inline-flex; align-items: center; gap: 0.35rem;">
        <i class="bi bi-cloud-arrow-up-fill"></i> Nueva Importación Excel
      </button>
    </div>

  </div>

  <!-- Tarjetas de Métricas -->
  <div class="excel-stat-grid">
    <div class="excel-stat-card">
      <div>
        <span class="text-xs text-muted font-weight-bold uppercase d-block">TOTAL REGISTROS</span>
        <span class="font-weight-bold font-size-16" id="topStatTotal" style="color: var(--text-primary);">{{ number_format($stats['total_registros']) }}</span>
      </div>
      <div class="excel-stat-icon icon-grad-slate">
        <i class="bi bi-layers-fill"></i>
      </div>
    </div>

    <div class="excel-stat-card">
      <div>
        <span class="text-xs text-muted font-weight-bold uppercase d-block">IMPORTADOS A BD</span>
        <span class="font-weight-bold font-size-16 text-success" id="topStatImportados">{{ number_format($stats['importados']) }}</span>
      </div>
      <div class="excel-stat-icon icon-grad-excel">
        <i class="bi bi-check-circle-fill"></i>
      </div>
    </div>

    <div class="excel-stat-card">
      <div>
        <span class="text-xs text-muted font-weight-bold uppercase d-block">NUEVOS EN COLA</span>
        <span class="font-weight-bold font-size-16 text-primary" id="topStatNuevos">{{ number_format($stats['nuevos']) }}</span>
      </div>
      <div class="excel-stat-icon icon-grad-blue">
        <i class="bi bi-plus-circle-fill"></i>
      </div>
    </div>

    <div class="excel-stat-card">
      <div>
        <span class="text-xs text-muted font-weight-bold uppercase d-block">PENDIENTES REVISIÓN</span>
        <span class="font-weight-bold font-size-16 text-warning" id="topStatPendientes">{{ number_format($stats['pendientes']) }}</span>
      </div>
      <div class="excel-stat-icon icon-grad-amber">
        <i class="bi bi-exclamation-triangle-fill"></i>
      </div>
    </div>

    <div class="excel-stat-card">
      <div>
        <span class="text-xs text-muted font-weight-bold uppercase d-block">YA EXISTENTES</span>
        <span class="font-weight-bold font-size-16 text-secondary" id="topStatExistentes">{{ number_format($stats['ya_existentes']) }}</span>
      </div>
      <div class="excel-stat-icon icon-grad-purple">
        <i class="bi bi-clipboard-data-fill"></i>
      </div>
    </div>
  </div>

  <!-- Barra de Filtros Estrictamente en 1 Sola Línea Horizontal -->
  <div class="excel-filter-card">
    <form id="formFiltrosExcel" method="GET" action="{{ route('ingresos.importar.index') }}" class="excel-filter-form" onsubmit="aplicarFiltrosDinamicos(event)">
      
      <!-- Búsqueda General con Icono Integrado -->
      <div style="position: relative; flex: 2.2; min-width: 170px;">
        <i class="bi bi-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.8rem; pointer-events: none;"></i>
        <input type="text" id="filtroSearch" name="search" class="excel-filter-input font-weight-bold" placeholder="Buscar DNI, Paciente, Colonia..." value="{{ $search }}" style="padding-left: 30px;" oninput="debounceBuscar()">
      </div>

      <!-- Filtro Estado -->
      <div style="flex: 1.3; min-width: 140px;">
        <select id="filtroEstado" name="estado" class="excel-filter-select font-weight-bold" onchange="aplicarFiltrosDinamicos()">
          <option value="">-- Todos los Estados --</option>
          <option value="IMPORTADO" {{ $estado === 'IMPORTADO' ? 'selected' : '' }}>🟢 IMPORTADO</option>
          <option value="NUEVO" {{ $estado === 'NUEVO' ? 'selected' : '' }}>🔵 NUEVO</option>
          <option value="PENDIENTE_REVISION" {{ $estado === 'PENDIENTE_REVISION' ? 'selected' : '' }}>🟡 PENDIENTE</option>
          <option value="YA_EXISTE" {{ $estado === 'YA_EXISTE' ? 'selected' : '' }}>⚪ YA EXISTE</option>
          <option value="DUPLICADO" {{ $estado === 'DUPLICADO' ? 'selected' : '' }}>🟣 DUPLICADO</option>
          <option value="ERROR" {{ $estado === 'ERROR' ? 'selected' : '' }}>🔴 ERROR</option>
        </select>
      </div>

      <!-- Filtro Médico -->
      <div style="flex: 2; min-width: 180px;">
        <select id="filtroMedico" name="medico" class="excel-filter-select font-weight-bold" onchange="aplicarFiltrosDinamicos()">
          <option value="">-- Todos los Médicos --</option>
          @foreach($medicos as $m)
            <option value="{{ $m }}" {{ $medico === $m ? 'selected' : '' }}>{{ $m }}</option>
          @endforeach
        </select>
      </div>

      <!-- Filtro Fecha -->
      <div style="flex: 1.2; min-width: 130px;">
        <input type="date" id="filtroFecha" name="fecha" class="excel-filter-input font-weight-bold" value="{{ $fecha }}" title="Fecha de atención" onchange="aplicarFiltrosDinamicos()">
      </div>

      <!-- Botones de Acción -->
      <div style="display: flex; align-items: center; gap: 0.35rem; flex-shrink: 0;">
        <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3" style="height: 32px; display: inline-flex; align-items: center; gap: 0.35rem;">
          <i class="bi bi-funnel-fill"></i> Filtrar
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary px-2" title="Limpiar Filtros" onclick="limpiarFiltrosDinamicos()" style="height: 32px; display: inline-flex; align-items: center; justify-content: center;">
          <i class="bi bi-x-circle"></i>
        </button>
      </div>

    </form>
  </div>

  <!-- Tabla de Registros: Texto Plano con Scroll Infinito Dinámico y Cotejo Día-Médico -->
  <div class="excel-table-container">
    <div class="excel-table-scroll custom-scrollbar" id="contenedorScrollTabla" onscroll="detectarScrollInfinito()">
      <table class="excel-table" id="tablaRegistrosImportacion">
        <thead>
          <tr>
            <th class="text-center" style="width: 45px;">#</th>
            <th style="width: 90px;">Fecha</th>
            <th style="width: 180px;">Médico</th>
            <th style="width: 145px;">DNI / Historia Clínica</th>
            <th style="width: 210px;">Nombre Paciente</th>
            <th style="width: 160px;">Colonia Asignada</th>
            <th style="width: 210px;">Diagnóstico CIE-10</th>
            <th class="text-center" style="width: 100px;">Estado</th>
            <th class="text-center" style="width: 130px;">Cotejo Día-Médico</th>
            <th style="width: 130px;">Lote / Archivo</th>
            <th class="text-center" style="width: 110px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="cuerpoTablaRegistros">
          @forelse($registros as $reg)
            @php
              $dxList = is_array($reg->diagnosticos_json) ? $reg->diagnosticos_json : [];
              $dx1 = $dxList[0] ?? null;
              $dxCodigo = $dx1['codigo'] ?? '';
              $dxNombre = $dx1['diagnostico'] ?? ($dx1['original'] ?? 'Sin Diagnóstico');
            @endphp
            <tr id="row-registro-{{ $reg->id }}">
              <!-- Número -->
              <td class="text-center font-weight-bold text-muted" style="font-size: 0.75rem;">
                {{ $reg->fila_excel ?: $reg->id }}
              </td>

              <!-- Fecha -->
              <td>
                <span class="font-weight-bold">{{ $reg->fecha_atencion ? $reg->fecha_atencion->format('d/m/Y') : '-' }}</span>
              </td>

              <!-- Médico y Profesión -->
              <td>
                <span class="font-weight-bold d-block text-truncate" style="max-width: 175px;" title="{{ $reg->medico }}">
                  {{ $reg->medico ?: 'SIN MÉDICO' }}
                </span>
                <span class="text-muted d-block" style="font-size: 0.7rem;">{{ $reg->prof ?: 'MÉDICO GENERAL' }}</span>
              </td>

              <!-- DNI / Identidad e Historia Clínica (Expediente) -->
              <td>
                <span class="font-weight-bold text-success d-block" style="font-size: 0.8rem;">{{ $reg->numero_identidad ?: 'SIN DNI' }}</span>
                @if($reg->expediente)
                  <span class="badge badge-info px-1 py-0 mt-1 font-weight-bold" style="font-size: 0.68rem;"><i class="bi bi-folder2-open mr-1"></i>HC: {{ $reg->expediente }}</span>
                @else
                  <span class="text-muted d-block" style="font-size: 0.68rem;">S/HC</span>
                @endif
              </td>

              <!-- Nombre Paciente, Sexo y Edad -->
              <td>
                <span class="font-weight-bold d-block text-truncate" style="max-width: 205px; color: var(--text-primary);" title="{{ $reg->nombre_paciente }}">
                  {{ $reg->nombre_paciente ?: 'SIN NOMBRE' }}
                </span>
                <span class="text-muted d-block" style="font-size: 0.7rem;">
                  {{ $reg->sexo == 'M' || $reg->sexo == 'H' ? 'Hombre' : ($reg->sexo == 'F' ? 'Mujer' : $reg->sexo) }}
                  @if($reg->edad) • {{ $reg->edad }} años @endif
                </span>
              </td>

              <!-- Colonia Asignada en Texto Plano -->
              <td>
                <span class="font-weight-bold text-truncate d-block" style="max-width: 155px;" title="{{ $reg->colonia_normalizada ?: $reg->direccion_original }}">
                  @if($reg->cod_col)<span class="text-primary mr-1">[{{ $reg->cod_col }}]</span>@endif
                  {{ $reg->colonia_normalizada ?: ($reg->direccion_original ?: 'No asignada') }}
                </span>
              </td>

              <!-- Diagnóstico Asignado en Texto Plano -->
              <td>
                <span class="font-weight-bold text-truncate d-block" style="max-width: 205px;" title="{{ $dxNombre }}">
                  @if($dxCodigo)<span class="text-info mr-1">[{{ $dxCodigo }}]</span>@endif
                  {{ $dxNombre }}
                </span>
              </td>

              <!-- Estado -->
              <td class="text-center">
                @if($reg->estado === 'IMPORTADO')
                  <span class="badge badge-success px-2 py-1" style="font-size: 0.72rem;" title="{{ $reg->motivo_estado ?: 'Registrado en histórico' }}">
                    ● IMPORTADO
                  </span>
                @elseif($reg->estado === 'NUEVO')
                  <span class="badge badge-primary px-2 py-1" style="font-size: 0.72rem;" title="Verificado y listo para guardar">
                    ● NUEVO
                  </span>
                @elseif($reg->estado === 'PENDIENTE_REVISION')
                  <span class="badge badge-warning px-2 py-1 text-dark" style="font-size: 0.72rem;" title="{{ $reg->motivo_estado ?: 'Requiere asignación' }}">
                    ● PENDIENTE
                  </span>
                @elseif($reg->estado === 'YA_EXISTE')
                  <span class="badge badge-secondary px-2 py-1" style="font-size: 0.72rem;" title="Ya existe en la base de datos">
                    ● YA EXISTE
                  </span>
                @elseif($reg->estado === 'DUPLICADO')
                  <span class="badge badge-purple px-2 py-1" style="background: #8b5cf6; color: #fff; font-size: 0.72rem;" title="Repetido en el mismo lote">
                    ● DUPLICADO
                  </span>
                @else
                  <span class="badge badge-danger px-2 py-1" style="font-size: 0.72rem;" title="{{ $reg->motivo_estado ?: 'Error de datos' }}">
                    ● ERROR
                  </span>
                @endif
              </td>

              <!-- Cotejo Día-Médico Dinámico -->
              <td class="text-center">
                <span class="badge {{ $reg->cotejo_class }} px-2 py-1" style="font-size: 0.72rem;" title="{{ $reg->cotejo_tooltip }}">
                  {{ $reg->cotejo_label }}
                </span>
              </td>

              <!-- Lote / Archivo -->
              <td>
                <span class="text-truncate d-block text-muted" style="max-width: 125px; font-size: 0.73rem;" title="{{ $reg->importacion ? $reg->importacion->nombre_archivo : 'Lote #' . $reg->importacion_id }}">
                  <i class="bi bi-file-earmark-text mr-1"></i>{{ $reg->importacion ? $reg->importacion->nombre_archivo : 'Lote #' . $reg->importacion_id }}
                </span>
                <span class="text-muted d-block" style="font-size: 0.68rem;">{{ $reg->created_at ? $reg->created_at->format('d/m/Y H:i') : '' }}</span>
              </td>

              <!-- GESTIÓN CRUD EN FILA HORIZONTAL COMPACTA -->
              <td class="text-center">
                <div style="display: flex; align-items: center; justify-content: center; gap: 4px; flex-wrap: nowrap;">
                  <!-- Ver Historial / Seguimiento por DNI e Historia Clínica -->
                  <button type="button" class="btn btn-xs btn-outline-info p-0 font-weight-bold"
                          style="width: 28px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px;"
                          onclick="verSeguimientoPaciente('{{ $reg->numero_identidad }}', '{{ $reg->expediente }}', {{ $reg->id }})"
                          title="Ver historial clínico y seguimiento completo del paciente (DNI / Historia Clínica)">
                    <i class="bi bi-person-lines-fill"></i>
                  </button>

                  <!-- Editar / Reasignar Registro -->
                  <button type="button" class="btn btn-xs btn-outline-warning p-0 font-weight-bold"
                          style="width: 28px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px;"
                          onclick="abrirModalEdicionRegistro({{ $reg->id }})"
                          title="Editar / Reasignar Colonia o Diagnóstico">
                    <i class="bi bi-pencil-square"></i>
                  </button>

                  <!-- Eliminar Registro -->
                  <button type="button" class="btn btn-xs btn-outline-danger p-0 font-weight-bold"
                          style="width: 28px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px;"
                          onclick="eliminarRegistroIndividual({{ $reg->id }})"
                          title="Eliminar este registro de la lista de importación">
                    <i class="bi bi-trash-fill"></i>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr id="rowSinRegistros">
              <td colspan="11" class="text-center py-5">
                <div class="d-flex flex-column align-items-center justify-content-center">
                  <div class="rounded-circle p-3 mb-2" style="background: var(--bg-subtle, #f1f5f9); color: var(--text-muted); font-size: 2rem;">
                    <i class="bi bi-file-earmark-excel"></i>
                  </div>
                  <h6 class="font-weight-bold mb-1" style="color: var(--text-primary);">No se encontraron registros</h6>
                  <p class="text-muted text-xs mb-3">Suba un nuevo archivo Excel o modifique los filtros de búsqueda.</p>
                  <button type="button" class="btn btn-sm btn-success font-weight-bold px-4" onclick="abrirModalImportacionExcel()">
                    <i class="bi bi-cloud-arrow-up-fill mr-1"></i> Nueva Importación Excel
                  </button>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>

      <!-- Spinner Inferior de Carga Dinámica Continua -->
      <div id="scrollLoadingSpinner" class="d-none py-3 text-center" style="background: var(--bg-surface);">
        <div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div>
        <span class="text-muted font-weight-bold text-xs">Cargando más pacientes cotejados...</span>
      </div>

    </div>

    <!-- Pie de Estado de Registros Cargados -->
    <div class="excel-pagination-wrapper">
      <div class="text-muted text-xs">
        Mostrando <strong id="contadorItemsCargados">{{ $registros->count() }}</strong> de <strong id="contadorTotalRegistros">{{ $registros->total() }}</strong> registros cotejados
      </div>
      <div class="text-xs font-weight-bold text-muted">
        <i class="bi bi-arrow-down-up mr-1"></i> Desplácese hacia abajo para cargar más registros continuamente
      </div>
    </div>
  </div>

</div>

<!-- Modal 1: Seguimiento Clínico e Historial Completo del Paciente por DNI -->
<div class="modal fade" id="modalSeguimientoPaciente" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 2400;">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-2xl rounded-xl overflow-hidden" style="background: var(--bg-surface, #ffffff); color: var(--text-primary, #1e293b);">
      
      <div class="modal-header py-3 px-4 border-0 d-flex align-items-center justify-content-between"
           style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff;">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-person-badge-fill font-size-22 mr-2"></i>
          <div>
            <h6 class="modal-title font-weight-bold mb-0 text-white">Seguimiento Clínico e Historial del Paciente</h6>
            <small class="text-white-50">Línea de tiempo cronológica de atenciones indexadas por Identidad / DNI</small>
          </div>
        </div>
        <button type="button" class="close text-white opacity-80" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
        
        <!-- Spinner Cargando -->
        <div id="patientTimelineSpinner" class="text-center py-5">
          <div class="spinner-border text-info mb-2" role="status" style="width: 2.5rem; height: 2.5rem;"></div>
          <p class="text-muted font-weight-bold text-xs mb-0">Buscando historial clínico en base de datos e importaciones...</p>
        </div>

        <div id="patientTimelineContent" class="d-none">
          <!-- Tarjeta de Ficha del Paciente -->
          <div class="p-3 mb-4 rounded-xl border" style="background: var(--bg-subtle, #f8f9fa); border-color: var(--border-color) !important;">
            <div class="row align-items-center">
              <div class="col-md-6 col-12 mb-2 mb-md-0">
                <span class="text-xs text-muted font-weight-bold uppercase d-block">NOMBRE DEL PACIENTE</span>
                <h5 class="font-weight-bold mb-1" id="tlPacienteNombre" style="color: var(--text-primary);">-</h5>
                <span class="badge badge-success font-size-12 px-2 py-1 mr-2" id="tlPacienteDni">DNI: -</span>
                <span class="badge badge-secondary font-size-12 px-2 py-1" id="tlPacienteExp">Exp: -</span>
              </div>
              <div class="col-md-3 col-6">
                <span class="text-xs text-muted font-weight-bold uppercase d-block">EDAD & SEXO</span>
                <span class="font-weight-bold" id="tlPacienteEdadSexo" style="color: var(--text-primary);">-</span>
                <small class="text-muted d-block" id="tlPacienteFecNac">F. Nac: -</small>
              </div>
              <div class="col-md-3 col-6 text-md-right">
                <span class="text-xs text-muted font-weight-bold uppercase d-block">COLONIA / PROCEDENCIA</span>
                <span class="font-weight-bold text-truncate d-block" id="tlPacienteColonia" style="color: var(--text-primary);">-</span>
                <span class="badge badge-info px-2 py-1 mt-1" id="tlTotalConsultasBadge">0 Atenciones Registradas</span>
              </div>
            </div>
          </div>

          <!-- Línea de Tiempo de Consultas -->
          <h6 class="font-weight-bold mb-3 d-flex align-items-center" style="color: var(--text-primary);">
            <i class="bi bi-clock-history text-primary mr-2"></i> Cronología de Atenciones y Consultas
          </h6>

          <div class="patient-timeline" id="patientTimelineList">
            <!-- Consultas inyectadas dinámicamente -->
          </div>
        </div>

      </div>

      <div class="modal-footer py-2 px-4 border-top" style="background: var(--bg-subtle, #f8f9fa); border-color: var(--border-color) !important;">
        <button type="button" class="btn btn-sm btn-secondary px-3" data-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

<!-- Modal 2: Edición Rápida de Registro -->
<div class="modal fade" id="modalEdicionRegistroRapida" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 2400;">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-2xl rounded-xl overflow-hidden" style="background: var(--bg-surface, #ffffff); color: var(--text-primary, #1e293b);">
      
      <div class="modal-header py-3 px-4 border-0 d-flex align-items-center justify-content-between"
           style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff;">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-pencil-square font-size-20 mr-2"></i>
          <div>
            <h6 class="modal-title font-weight-bold mb-0 text-white">Editar / Reasignar Registro</h6>
            <small class="text-white-50">Actualizar colonia, diagnóstico CIE-10 o datos del paciente</small>
          </div>
        </div>
        <button type="button" class="close text-white opacity-80" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
        <input type="hidden" id="editRegId">

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="font-weight-bold text-xs">Nombre Paciente:</label>
            <input type="text" id="editRegNombre" class="form-control form-control-sm font-weight-bold">
          </div>
          <div class="col-md-6">
            <label class="font-weight-bold text-xs">DNI / Identidad:</label>
            <input type="text" id="editRegDni" class="form-control form-control-sm font-weight-bold">
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="font-weight-bold text-xs">Fecha Atención:</label>
            <input type="date" id="editRegFecha" class="form-control form-control-sm font-weight-bold">
          </div>
          <div class="col-md-5">
            <label class="font-weight-bold text-xs">Médico Responsable:</label>
            <input type="text" id="editRegMedico" class="form-control form-control-sm font-weight-bold">
          </div>
          <div class="col-md-3">
            <label class="font-weight-bold text-xs">Edad / Sexo:</label>
            <div class="input-group input-group-sm">
              <input type="text" id="editRegEdad" class="form-control form-control-sm" placeholder="Edad">
              <select id="editRegSexo" class="form-control form-control-sm">
                <option value="M">M</option>
                <option value="F">F</option>
              </select>
            </div>
          </div>
        </div>

        <hr style="border-color: var(--border-color);">

        <!-- Sección Colonia -->
        <div class="form-group mb-3">
          <label class="font-weight-bold text-xs d-block">Colonia Asignada (Catálogo Oficial):</label>
          <div class="row g-2">
            <div class="col-md-3">
              <input type="text" id="editRegCodCol" class="form-control form-control-sm font-weight-bold text-center" placeholder="Cód Col" oninput="sincronizarColoniaPorCodigo(this.value)">
            </div>
            <div class="col-md-9">
              <select id="editRegColoniaSelect" class="form-select form-select-sm form-control form-control-sm" onchange="sincronizarCodigoPorSelect(this)">
                <option value="">-- Seleccione Colonia Oficial --</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Sección Diagnóstico -->
        <div class="form-group mb-3">
          <label class="font-weight-bold text-xs d-block">Diagnóstico Principal (CIE-10):</label>
          <div class="row g-2">
            <div class="col-md-3">
              <input type="text" id="editRegDxCodigo" class="form-control form-control-sm font-weight-bold text-center" placeholder="Código" oninput="sincronizarDxPorCodigo(this.value)">
            </div>
            <div class="col-md-9">
              <input type="text" id="editRegDxNombre" class="form-control form-control-sm font-weight-bold" placeholder="Escriba o busque patología...">
              <input type="hidden" id="editRegDxId">
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer py-2 px-4 border-top d-flex justify-content-between" style="background: var(--bg-subtle, #f8f9fa); border-color: var(--border-color) !important;">
        <button type="button" class="btn btn-sm btn-secondary px-3" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-sm btn-warning text-dark font-weight-bold px-4 shadow-sm" onclick="guardarEdicionRegistroRapida()">
          <i class="bi bi-check2-circle mr-1"></i> Guardar Cambios
        </button>
      </div>

    </div>
  </div>
</div>

<!-- Modal Profesional de Importación Excel (Asistente Wizard) -->
@include('ingresos.modal-importar-excel')

@endsection

@push('scripts')
<script>
  var catalogoColoniasCache = [];
  var catalogoDiagnosticosCache = [];

  // Variables para Infinite Scroll Dinámico
  var paginaActual = {{ $registros->currentPage() }};
  var ultimaPagina = {{ $registros->lastPage() }};
  var tieneMasPaginas = {{ $registros->hasMorePages() ? 'true' : 'false' }};
  var cargandoPagina = false;
  var timerBusqueda = null;

  document.addEventListener('DOMContentLoaded', function() {
    cargarCatalogosEdicion();
  });

  function cargarCatalogosEdicion() {
    $.ajax({
      url: '{{ route("ingresos.importar.catalogos") }}',
      type: 'GET',
      success: function(res) {
        if (res.success) {
          catalogoColoniasCache = res.colonias || [];
          catalogoDiagnosticosCache = res.diagnosticos || [];
          poblarSelectColoniasModal();
        }
      }
    });
  }

  function poblarSelectColoniasModal() {
    var select = document.getElementById('editRegColoniaSelect');
    if (!select) return;
    select.innerHTML = '<option value="">-- Seleccione Colonia Oficial --</option>';
    catalogoColoniasCache.forEach(function(c) {
      var opt = document.createElement('option');
      opt.value = c.colonia;
      opt.dataset.codCol = c.cod_col;
      opt.dataset.colId = c.id;
      opt.textContent = `[${c.cod_col}] ${c.colonia}`;
      select.appendChild(opt);
    });
  }

  function sincronizarColoniaPorCodigo(cod) {
    if (!cod) return;
    var select = document.getElementById('editRegColoniaSelect');
    var found = Array.from(select.options).find(opt => opt.dataset.codCol == String(cod).trim());
    if (found) {
      select.value = found.value;
    }
  }

  function sincronizarCodigoPorSelect(sel) {
    var opt = sel.selectedOptions[0];
    var codInput = document.getElementById('editRegCodCol');
    if (opt && opt.dataset.codCol) {
      codInput.value = opt.dataset.codCol;
    } else {
      codInput.value = '';
    }
  }

  function sincronizarDxPorCodigo(cod) {
    if (!cod || !catalogoDiagnosticosCache.length) return;
    var found = catalogoDiagnosticosCache.find(d => String(d.codigo).trim() === String(cod).trim());
    if (found) {
      document.getElementById('editRegDxNombre').value = found.patologia;
      document.getElementById('editRegDxId').value = found.id;
    }
  }

  // ═════════════════════════════════════════════════════════════════════════
  // SISTEMA DE CARGA DINÁMICA CONTINUA AL HACER SCROLL (INFINITE SCROLL)
  // ═════════════════════════════════════════════════════════════════════════

  function detectarScrollInfinito() {
    var scrollContainer = document.getElementById('contenedorScrollTabla');
    if (!scrollContainer || cargandoPagina || !tieneMasPaginas) return;

    // Disparar carga cuando el usuario se acerca a 120px del fondo
    var scrollBottom = scrollContainer.scrollHeight - scrollContainer.scrollTop - scrollContainer.clientHeight;
    if (scrollBottom < 120) {
      cargarSiguientePagina();
    }
  }

  function cargarSiguientePagina() {
    if (cargandoPagina || !tieneMasPaginas) return;
    cargandoPagina = true;

    var spinner = document.getElementById('scrollLoadingSpinner');
    if (spinner) spinner.classList.remove('d-none');

    var params = obtenerParametrosFiltros();
    params.page = paginaActual + 1;
    params.ajax = 1;

    $.ajax({
      url: '{{ route("ingresos.importar.index") }}',
      type: 'GET',
      data: params,
      success: function(res) {
        if (spinner) spinner.classList.add('d-none');
        cargandoPagina = false;

        if (res.success && res.registros && res.registros.length > 0) {
          paginaActual = res.current_page;
          ultimaPagina = res.last_page;
          tieneMasPaginas = res.has_more;

          renderizarFilasNuevas(res.registros, false);

          var contador = document.getElementById('contadorItemsCargados');
          if (contador) {
            var filasActuales = document.querySelectorAll('#cuerpoTablaRegistros tr[id^="row-registro-"]').length;
            contador.innerText = filasActuales;
          }
          var contadorTotal = document.getElementById('contadorTotalRegistros');
          if (contadorTotal) contadorTotal.innerText = res.total;
        } else {
          tieneMasPaginas = false;
        }
      },
      error: function() {
        if (spinner) spinner.classList.add('d-none');
        cargandoPagina = false;
      }
    });
  }

  function debounceBuscar() {
    clearTimeout(timerBusqueda);
    timerBusqueda = setTimeout(function() {
      aplicarFiltrosDinamicos();
    }, 350);
  }

  function obtenerParametrosFiltros() {
    return {
      search: document.getElementById('filtroSearch').value.trim(),
      estado: document.getElementById('filtroEstado').value,
      medico: document.getElementById('filtroMedico').value,
      fecha: document.getElementById('filtroFecha').value,
    };
  }

  function aplicarFiltrosDinamicos(e) {
    if (e && typeof e.preventDefault === 'function') {
      e.preventDefault();
    }

    cargandoPagina = true;
    paginaActual = 1;
    tieneMasPaginas = true;

    var tbody = document.getElementById('cuerpoTablaRegistros');
    tbody.innerHTML = `<tr><td colspan="11" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary mr-2"></div>Buscando registros cotejados...</td></tr>`;

    var params = obtenerParametrosFiltros();
    params.page = 1;
    params.ajax = 1;

    $.ajax({
      url: '{{ route("ingresos.importar.index") }}',
      type: 'GET',
      data: params,
      success: function(res) {
        cargandoPagina = false;
        if (res.success && res.registros) {
          paginaActual = res.current_page;
          ultimaPagina = res.last_page;
          tieneMasPaginas = res.has_more;

          tbody.innerHTML = '';
          if (res.registros.length === 0) {
            tbody.innerHTML = `<tr><td colspan="11" class="text-center py-4 text-muted font-weight-bold">No se encontraron registros con los filtros seleccionados.</td></tr>`;
          } else {
            renderizarFilasNuevas(res.registros, true);
          }

          var contador = document.getElementById('contadorItemsCargados');
          if (contador) contador.innerText = res.registros.length;
          var contadorTotal = document.getElementById('contadorTotalRegistros');
          if (contadorTotal) contadorTotal.innerText = res.total;
        }
      },
      error: function() {
        cargandoPagina = false;
        tbody.innerHTML = `<tr><td colspan="11" class="text-center py-4 text-danger">Error al cargar registros.</td></tr>`;
      }
    });
  }

  function limpiarFiltrosDinamicos() {
    document.getElementById('filtroSearch').value = '';
    document.getElementById('filtroEstado').value = '';
    document.getElementById('filtroMedico').value = '';
    document.getElementById('filtroFecha').value = '';
    aplicarFiltrosDinamicos();
  }

  function renderizarFilasNuevas(registros, esReemplazo) {
    var tbody = document.getElementById('cuerpoTablaRegistros');
    var fragment = document.createDocumentFragment();

    registros.forEach(function(reg) {
      var tr = document.createElement('tr');
      tr.id = 'row-registro-' + reg.id;

      var estadoHtml = '';
      if (reg.estado === 'IMPORTADO') {
        estadoHtml = `<span class="badge badge-success px-2 py-1" style="font-size: 0.72rem;">● IMPORTADO</span>`;
      } else if (reg.estado === 'NUEVO') {
        estadoHtml = `<span class="badge badge-primary px-2 py-1" style="font-size: 0.72rem;">● NUEVO</span>`;
      } else if (reg.estado === 'PENDIENTE_REVISION') {
        estadoHtml = `<span class="badge badge-warning px-2 py-1 text-dark" style="font-size: 0.72rem;">● PENDIENTE</span>`;
      } else if (reg.estado === 'YA_EXISTE') {
        estadoHtml = `<span class="badge badge-secondary px-2 py-1" style="font-size: 0.72rem;">● YA EXISTE</span>`;
      } else if (reg.estado === 'DUPLICADO') {
        estadoHtml = `<span class="badge badge-purple px-2 py-1" style="background: #8b5cf6; color: #fff; font-size: 0.72rem;">● DUPLICADO</span>`;
      } else {
        estadoHtml = `<span class="badge badge-danger px-2 py-1" style="font-size: 0.72rem;">● ERROR</span>`;
      }

      tr.innerHTML = `
        <td class="text-center font-weight-bold text-muted" style="font-size: 0.75rem;">${reg.fila_excel}</td>
        <td><span class="font-weight-bold">${reg.fecha_formato}</span></td>
        <td>
          <span class="font-weight-bold d-block text-truncate" style="max-width: 175px;" title="${reg.medico}">${reg.medico}</span>
          <span class="text-muted d-block" style="font-size: 0.7rem;">${reg.prof}</span>
        </td>
        <td>
          <span class="font-weight-bold text-success d-block" style="font-size: 0.8rem;">${reg.identidad}</span>
          ${reg.expediente ? '<span class="badge badge-info px-1 py-0 mt-1 font-weight-bold" style="font-size: 0.68rem;"><i class="bi bi-folder2-open mr-1"></i>HC: ' + reg.expediente + '</span>' : '<span class="text-muted d-block" style="font-size: 0.68rem;">S/HC</span>'}
        </td>
        <td>
          <span class="font-weight-bold d-block text-truncate" style="max-width: 205px; color: var(--text-primary);" title="${reg.nombre_paciente}">${reg.nombre_paciente}</span>
          <span class="text-muted d-block" style="font-size: 0.7rem;">${reg.sexo_edad}</span>
        </td>
        <td>
          <span class="font-weight-bold text-truncate d-block" style="max-width: 155px;" title="${reg.colonia}">${reg.colonia}</span>
        </td>
        <td>
          <span class="font-weight-bold text-truncate d-block" style="max-width: 205px;" title="${reg.diagnostico}">${reg.diagnostico}</span>
        </td>
        <td class="text-center">${estadoHtml}</td>
        <td class="text-center">
          <span class="badge ${reg.cotejo_class} px-2 py-1" style="font-size: 0.72rem;" title="${reg.cotejo_tooltip}">${reg.cotejo_label}</span>
        </td>
        <td>
          <span class="text-truncate d-block text-muted" style="max-width: 125px; font-size: 0.73rem;" title="${reg.archivo_lote}"><i class="bi bi-file-earmark-text mr-1"></i>${reg.archivo_lote}</span>
          <span class="text-muted d-block" style="font-size: 0.68rem;">${reg.created_at_formato}</span>
        </td>
        <td class="text-center">
          <div style="display: flex; align-items: center; justify-content: center; gap: 4px; flex-wrap: nowrap;">
            <button type="button" class="btn btn-xs btn-outline-info p-0 font-weight-bold" style="width: 28px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px;" onclick="verSeguimientoPaciente('${reg.identidad}', '${reg.expediente || ''}', ${reg.id})" title="Ver Historial Clínico (DNI / Historia Clínica)"><i class="bi bi-person-lines-fill"></i></button>
            <button type="button" class="btn btn-xs btn-outline-warning p-0 font-weight-bold" style="width: 28px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px;" onclick="abrirModalEdicionRegistro(${reg.id})" title="Editar / Reasignar"><i class="bi bi-pencil-square"></i></button>
            <button type="button" class="btn btn-xs btn-outline-danger p-0 font-weight-bold" style="width: 28px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px;" onclick="eliminarRegistroIndividual(${reg.id})" title="Eliminar Registro"><i class="bi bi-trash-fill"></i></button>
          </div>
        </td>
      `;

      fragment.appendChild(tr);
    });

    tbody.appendChild(fragment);
  }

  // ═════════════════════════════════════════════════════════════════════════
  // MODAL 1: SEGUIMIENTO CLÍNICO E HISTORIAL COMPLETO (DNI / HISTORIA CLÍNICA)
  // ═════════════════════════════════════════════════════════════════════════

  function verSeguimientoPaciente(dni, expediente, registroId) {
    $('#modalSeguimientoPaciente').modal('show');
    document.getElementById('patientTimelineSpinner').classList.remove('d-none');
    document.getElementById('patientTimelineContent').classList.add('d-none');

    $.ajax({
      url: '{{ route("ingresos.importar.paciente-historial") }}',
      type: 'GET',
      data: { dni: dni, expediente: expediente, registro_id: registroId },
      success: function(res) {
        document.getElementById('patientTimelineSpinner').classList.add('d-none');
        document.getElementById('patientTimelineContent').classList.remove('d-none');

        if (res.success) {
          var p = res.paciente || {};
          document.getElementById('tlPacienteNombre').innerText = p.nombre_completo || 'Paciente sin nombre';
          document.getElementById('tlPacienteDni').innerText = 'DNI: ' + (p.dni || 'No registrado');
          document.getElementById('tlPacienteExp').innerText = 'HC / Exp: ' + (p.expediente || 'S/N');
          document.getElementById('tlPacienteEdadSexo').innerText = (p.edad ? p.edad + ' años • ' : '') + (p.sexo || '-');
          document.getElementById('tlPacienteFecNac').innerText = 'F. Nac: ' + (p.fecha_nacimiento || '-');
          document.getElementById('tlPacienteColonia').innerText = p.colonia || 'No especificada';
          document.getElementById('tlTotalConsultasBadge').innerText = res.total_atenciones + ' Atenciones Registradas';

          var list = document.getElementById('patientTimelineList');
          list.innerHTML = '';

          if (!res.timeline || res.timeline.length === 0) {
            list.innerHTML = '<p class="text-muted font-italic py-3">No hay atenciones previas registradas para este paciente.</p>';
            return;
          }

          res.timeline.forEach(function(item) {
            var itemDiv = document.createElement('div');
            itemDiv.className = 'timeline-item';

            var dxHtml = '';
            if (item.diagnosticos && item.diagnosticos.length > 0) {
              item.diagnosticos.forEach(function(d) {
                var cod = d.codigo || '';
                var pat = d.diagnostico || d.original || '';
                dxHtml += `<span class="mr-2 font-weight-bold" style="font-size: 0.8rem; color: var(--text-primary);">
                  ${cod ? '<span class="text-primary mr-1">[' + cod + ']</span>' : ''}${pat}
                </span>`;
              });
            } else {
              dxHtml = '<span class="text-muted font-italic text-xs">Sin diagnósticos cargados</span>';
            }

            var badgeOrigen = item.origen === 'BASE_DATOS_HISTORICA'
              ? '<span class="badge badge-success px-2 py-1 text-xs"><i class="bi bi-database-check mr-1"></i>Histórico Oficial AT-1</span>'
              : '<span class="badge badge-primary px-2 py-1 text-xs"><i class="bi bi-file-earmark-excel mr-1"></i>Importación Excel (' + (item.estado || 'Procesado') + ')</span>';

            itemDiv.innerHTML = `
              <div class="timeline-dot"></div>
              <div class="card border p-3 rounded-lg mb-2" style="background: var(--bg-surface); border-color: var(--border-color) !important; box-shadow: var(--shadow-sm);">
                <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap">
                  <div class="d-flex align-items-center gap-2">
                    <span class="font-weight-bold" style="font-size: 0.95rem; color: var(--text-primary);">
                      <i class="bi bi-calendar-event text-primary mr-1"></i> ${item.fecha_formato}
                    </span>
                    ${badgeOrigen}
                  </div>
                  <span class="text-xs text-muted font-weight-bold">
                    <i class="bi bi-geo-alt mr-1"></i> ${item.colonia || 'Sin colonia'}
                  </span>
                </div>
                <div class="text-xs text-muted mb-2">
                  <strong>Médico:</strong> ${item.medico} (${item.prof || 'MÉDICO GENERAL'}) ${item.cm ? '• CM: ' + item.cm : ''}
                </div>
                <div>
                  <small class="text-muted font-weight-bold d-block mb-1">Diagnósticos de la Consulta:</small>
                  <div class="d-flex flex-wrap">${dxHtml}</div>
                </div>
              </div>
            `;
            list.appendChild(itemDiv);
          });

        }
      },
      error: function() {
        document.getElementById('patientTimelineSpinner').classList.add('d-none');
        Swal.fire('Error', 'No se pudo cargar el historial del paciente.', 'error');
      }
    });
  }

  // ═════════════════════════════════════════════════════════════════════════
  // MODAL 2: EDICIÓN RÁPIDA DE REGISTRO
  // ═════════════════════════════════════════════════════════════════════════

  function abrirModalEdicionRegistro(id) {
    $.ajax({
      url: `/ingresos/importar/registro/${id}`,
      type: 'GET',
      success: function(res) {
        if (res.success && res.registro) {
          var r = res.registro;
          document.getElementById('editRegId').value = r.id;
          document.getElementById('editRegNombre').value = r.nombre_paciente || '';
          document.getElementById('editRegDni').value = r.numero_identidad || '';
          document.getElementById('editRegFecha').value = r.fecha_atencion ? r.fecha_atencion.substring(0, 10) : '';
          document.getElementById('editRegMedico').value = r.medico || '';
          document.getElementById('editRegEdad').value = r.edad || '';
          document.getElementById('editRegSexo').value = r.sexo || 'M';
          document.getElementById('editRegCodCol').value = r.cod_col || '';
          document.getElementById('editRegColoniaSelect').value = r.colonia_normalizada || '';

          var dxList = Array.isArray(r.diagnosticos_json) ? r.diagnosticos_json : [];
          var dx1 = dxList[0] || {};
          document.getElementById('editRegDxCodigo').value = dx1.codigo || '';
          document.getElementById('editRegDxNombre').value = dx1.diagnostico || (dx1.original || '');
          document.getElementById('editRegDxId').value = dx1.diagnostico_id || '';

          $('#modalEdicionRegistroRapida').modal('show');
        }
      }
    });
  }

  function guardarEdicionRegistroRapida() {
    var id = document.getElementById('editRegId').value;
    var colSel = document.getElementById('editRegColoniaSelect');
    var colOpt = colSel.selectedOptions[0];

    var data = {
      _token: '{{ csrf_token() }}',
      nombre_paciente: document.getElementById('editRegNombre').value.trim(),
      numero_identidad: document.getElementById('editRegDni').value.trim(),
      fecha_atencion: document.getElementById('editRegFecha').value,
      medico: document.getElementById('editRegMedico').value.trim(),
      edad: document.getElementById('editRegEdad').value.trim(),
      sexo: document.getElementById('editRegSexo').value,
      colonia_normalizada: colSel.value,
      cod_col: document.getElementById('editRegCodCol').value.trim(),
      colonia_id: colOpt ? colOpt.dataset.colId : null,
      codigo: document.getElementById('editRegDxCodigo').value.trim(),
      diagnostico: document.getElementById('editRegDxNombre').value.trim(),
      diagnostico_id: document.getElementById('editRegDxId').value
    };

    $.ajax({
      url: `/ingresos/importar/registro/${id}/editar`,
      type: 'POST',
      data: data,
      success: function(res) {
        $('#modalEdicionRegistroRapida').modal('hide');
        Swal.fire({
          icon: 'success',
          title: 'Registro Actualizado',
          text: 'Los cambios fueron guardados exitosamente.',
          timer: 1400,
          showConfirmButton: false
        });
        aplicarFiltrosDinamicos();
      },
      error: function(xhr) {
        Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error al guardar cambios', 'error');
      }
    });
  }

  // ═════════════════════════════════════════════════════════════════════════
  // ELIMINAR REGISTRO INDIVIDUAL
  // ═════════════════════════════════════════════════════════════════════════

  function eliminarRegistroIndividual(id) {
    Swal.fire({
      title: '¿Eliminar este registro?',
      text: 'Se removerá de la lista de importación.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: `/ingresos/importar/registro/${id}`,
          type: 'DELETE',
          data: { _token: '{{ csrf_token() }}' },
          success: function(res) {
            var row = document.getElementById('row-registro-' + id);
            if (row) {
              row.style.transition = 'all 0.3s ease';
              row.style.opacity = '0';
              setTimeout(() => row.remove(), 300);
            }
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Registro eliminado',
              showConfirmButton: false,
              timer: 1400
            });
          },
          error: function() {
            Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
          }
        });
      }
    });
  }

  // ═════════════════════════════════════════════════════════════════════════
  // SINCRONIZAR A PACIENTES BD
  // ═════════════════════════════════════════════════════════════════════════

  function sincronizarPacientesABd() {
    Swal.fire({
      title: '¿Sincronizar a Pacientes BD?',
      text: 'Se importarán y actualizarán las fichas demográficas de todos los pacientes en el módulo oficial Pacientes BD.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-people-fill mr-1"></i> Sí, sincronizar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#3b82f6',
      cancelButtonColor: '#64748b'
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Sincronizando Pacientes...',
          text: 'Procesando identidades y actualizando base de datos demográfica...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        $.ajax({
          url: '{{ route("ingresos.importar.sincronizar-pacientes") }}',
          type: 'POST',
          data: { _token: '{{ csrf_token() }}' },
          success: function(res) {
            if (res.success) {
              Swal.fire({
                icon: 'success',
                title: '¡Sincronización Exitosa!',
                html: `<p class="mb-2 font-weight-bold">${res.message}</p><span class="badge badge-info px-3 py-2 font-size-13 font-weight-bold">Total Pacientes en BD: ${res.total_pacientes}</span>`,
                confirmButtonColor: '#10b981'
              }).then(() => {
                aplicarFiltrosDinamicos();
              });
            } else {
              Swal.fire('Error', res.message || 'No se pudo completar la sincronización.', 'error');
            }
          },
          error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message : 'Error al sincronizar pacientes.', 'error');
          }
        });
      }
    });
  }
</script>
@endpush
