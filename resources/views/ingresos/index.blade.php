@extends('layouts.app')

@section('title', 'Ingresos AT-1 - Estadísticas 1.7')

@push('styles')
<style>
  /* ═══════════════════════════════════════════════════════════════════════════
     MÓDULO INGRESOS: CONTENEDOR CON SCROLL CÓMODO Y COLORES ESTADÍSTICAS 1.5
     ═══════════════════════════════════════════════════════════════════════════ */

  .app-content {
    padding: 0.75rem 1.25rem 0.5rem !important;
    height: calc(100vh - var(--navbar-height) - var(--footer-height)) !important;
    max-height: calc(100vh - var(--navbar-height) - var(--footer-height)) !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
  }

  /* Tarjetas de Resumen */
  .ingresos-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 0.65rem;
    margin-bottom: 0.65rem;
    flex-shrink: 0;
  }

  .ingreso-stat-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 8px);
    padding: 0.55rem 0.85rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow-sm);
  }

  .stat-icon-wrap {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-xs, 5px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    color: #ffffff;
    flex-shrink: 0;
  }

  .icon-gradient-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
  .icon-gradient-primary { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
  .icon-gradient-purple  { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }

  /* Filtros Modernos */
  .ingresos-filter-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 8px);
    padding: 0.55rem 0.85rem;
    margin-bottom: 0.65rem;
    box-shadow: var(--shadow-sm);
    flex-shrink: 0;
  }

  .filter-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(145px, 1fr));
    gap: 0.55rem;
    align-items: flex-end;
  }

  .filter-field-label {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-muted);
    margin-bottom: 0.2rem;
  }

  .filter-input-control {
    width: 100%;
    height: 32px;
    padding: 0 0.6rem;
    background: var(--bg-body);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xs, 4px);
    color: var(--text-primary);
    font-size: 0.8rem;
    font-family: inherit;
    transition: border-color var(--transition-fast);
  }

  .filter-input-control:focus {
    outline: none;
    border-color: var(--color-primary);
  }

  /* Caja Contenedora Principal con Scroll Cómodo */
  .fechas-wrapper-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 8px);
    padding: 0.65rem;
    box-shadow: var(--shadow-sm);
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  .fechas-scroll-container {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: var(--border-color) transparent;
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    padding-right: 4px;
  }

  .fechas-scroll-container::-webkit-scrollbar {
    width: 6px;
  }

  .fechas-scroll-container::-webkit-scrollbar-thumb {
    background-color: var(--border-color);
    border-radius: 4px;
  }

  /* Tarjeta de Fecha - TAMAÑO CÓMODO Y COLORES ORIGINALES */
  .fecha-card {
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.12);
    border: 1px solid rgba(0, 0, 0, 0.1);
    flex-shrink: 0 !important; /* PREVIENE CUALQUIER APLASTAMIENTO */
  }

  .fecha-card-header {
    padding: 0.55rem 0.95rem;
    min-height: 48px;
    background: linear-gradient(135deg, #1a9db3 0%, #138fa0 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    user-select: none;
    transition: filter var(--transition-fast);
  }

  .fecha-card-header:hover {
    filter: brightness(1.1);
  }

  /* Dark mode: restore the original deeper teal */
  [data-theme="dark"] .fecha-card-header {
    background: linear-gradient(135deg, #0f6674 0%, #0c5460 100%);
  }

  .fecha-title-info {
    display: flex;
    align-items: center;
    gap: 0.65rem;
  }

  .fecha-icon-box {
    width: 32px;
    height: 32px;
    border-radius: 5px;
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
  }

  .fecha-text-main {
    font-size: 0.92rem;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.15;
    letter-spacing: 0.02em;
  }

  .fecha-text-sub {
    font-size: 0.74rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.88);
    text-transform: capitalize;
  }

  .fecha-stats-and-actions {
    display: flex;
    align-items: center;
    gap: 0.55rem;
  }

  .fecha-badge-count {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.32);
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 4px;
  }

  .btn-fecha-action {
    width: 30px;
    height: 30px;
    border-radius: 4px;
    border: 1px solid rgba(255, 255, 255, 0.35);
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    cursor: pointer;
    transition: all var(--transition-fast);
    text-decoration: none;
  }

  .btn-fecha-action:hover {
    background: rgba(255, 255, 255, 0.35);
    color: #ffffff;
  }

  .expand-chevron {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.85rem;
    transition: transform var(--transition-fast);
  }

  .fecha-card.open .expand-chevron {
    transform: rotate(180deg);
  }

  /* Médicos Container */
  .medicos-list-body {
    padding: 0.65rem 0.85rem;
    background: var(--bg-body);
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    border-top: 1px solid rgba(0, 0, 0, 0.12);
  }

  .medico-item-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xs, 4px);
    padding: 0.55rem 0.85rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.65rem;
    flex-wrap: wrap;
    transition: background var(--transition-fast);
  }

  .medico-item-card:hover {
    background: var(--bg-surface-hover);
  }

  .medico-primary-info {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    min-width: 220px;
  }

  .medico-badge-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-primary), #6366f1);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 800;
    flex-shrink: 0;
  }

  .medico-name-title {
    font-size: 0.84rem;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.4rem;
  }

  .medico-badges-wrap {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
  }

  .stat-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.72rem;
    padding: 0.18rem 0.5rem;
    border-radius: 4px;
    border: 1px solid var(--border-color);
    background: var(--bg-body);
    color: var(--text-secondary);
    font-weight: 600;
  }

  .stat-pill.pill-user { border-color: rgba(139, 92, 246, 0.25); color: #8b5cf6; }
  .stat-pill.pill-jornada { border-color: rgba(245, 158, 11, 0.25); color: #f59e0b; }
  .stat-pill.pill-atenciones { border-color: rgba(59, 130, 246, 0.25); color: #3b82f6; font-weight: 700; }
  .stat-pill.pill-diagnosticos { border-color: rgba(16, 185, 129, 0.25); color: #10b981; font-weight: 700; }
  .stat-pill.pill-menores { border-color: rgba(239, 68, 68, 0.25); color: #ef4444; font-weight: 700; }

  .subregistros-container {
    padding-left: 1.85rem;
    margin-top: 0.35rem;
    border-left: 2px dashed rgba(var(--color-primary-rgb, 77, 124, 254), 0.4);
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
  }

  .subregistro-row {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xs, 4px);
    padding: 0.4rem 0.65rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.55rem;
    font-size: 0.76rem;
  }
</style>
<link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2.min.css') }}">
<style>
  /* ── SweetAlert2 - Integración de Tema ───────────────────────────────── */
  .swal2-popup {
    background: var(--bg-surface, #ffffff) !important;
    color: var(--text-primary, #1e293b) !important;
    border: 1px solid var(--border-color, #cbd5e1) !important;
    border-radius: 12px !important;
    font-family: var(--font-family-base, 'Inter', sans-serif) !important;
  }
  .swal2-title  { color: var(--text-primary, #1e293b) !important; font-weight: 700 !important; }
  .swal2-html-container { color: var(--text-secondary, #475569) !important; }

  [data-theme="dark"] .swal2-popup {
    background: #182238 !important;
    color: #f8fafc !important;
    border: 1px solid #3b4863 !important;
    box-shadow: 0 24px 45px -8px rgba(0,0,0,.75) !important;
  }
  [data-theme="dark"] .swal2-title          { color: #f8fafc !important; }
  [data-theme="dark"] .swal2-html-container { color: #cbd5e1 !important; }
  [data-theme="dark"] .swal2-icon.swal2-warning {
    border-color: #f59e0b !important;
    color: #f59e0b !important;
  }
  [data-theme="dark"] .swal2-icon.swal2-success {
    border-color: #22c55e !important;
    color: #22c55e !important;
  }
  [data-theme="dark"] .swal2-icon.swal2-error {
    border-color: #ef4444 !important;
    color: #ef4444 !important;
  }
  .swal2-actions button {
    border-radius: 6px !important;
    font-weight: 600 !important;
    font-size: 0.88rem !important;
    padding: 0.5rem 1.2rem !important;
  }
</style>
@endpush


@section('content')
<!-- Header y Navegación Rápida con Cards de Información a la par del Título -->
<div class="page-header" style="margin-bottom: 0.55rem; flex-shrink: 0; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
  <div style="display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;">
    <!-- Título y Breadcrumb -->
    <div>
      <h1 class="page-title" style="font-size: 1.2rem; margin-bottom: 0; display: flex; align-items: center; gap: 0.45rem;">
        <i class="bi bi-pencil-square text-primary" style="font-size: 1.2rem;"></i>
        Ingresos AT-1
      </h1>
      <div style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.78rem; color: var(--text-muted); margin-top: 0.1rem;">
        <span style="font-weight: 700; font-size: 0.66rem; letter-spacing: 0.05em; text-transform: uppercase; color: var(--color-primary); background: var(--color-primary-light); padding: 0.1rem 0.4rem; border-radius: var(--radius-xs);">USTED ESTÁ AQUÍ</span>
        <span>App</span>
        <i class="bi bi-chevron-right" style="font-size: 0.62rem;"></i>
        <span style="color: var(--text-primary); font-weight: 600;">Ingresos y Lotes de Atención</span>
      </div>
    </div>

    <!-- Cards de Información a la par del Título -->
    <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
      <!-- Total Registros -->
      <div class="ingreso-stat-card" style="padding: 0.35rem 0.65rem; display: inline-flex; align-items: center; gap: 0.55rem; border-radius: 8px;">
        <div class="stat-icon-wrap icon-gradient-success" style="width: 28px; height: 28px; font-size: 0.9rem; border-radius: 6px;">
          <i class="bi bi-file-earmark-medical-fill"></i>
        </div>
        <div>
          <div style="font-size: 0.60rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); line-height: 1;">Total Registros</div>
          <div style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary); line-height: 1.1;">{{ number_format($estadisticas['total_registros'] ?? 0) }}</div>
        </div>
      </div>

      <!-- Médicos Activos -->
      <div class="ingreso-stat-card" style="padding: 0.35rem 0.65rem; display: inline-flex; align-items: center; gap: 0.55rem; border-radius: 8px;">
        <div class="stat-icon-wrap icon-gradient-primary" style="width: 28px; height: 28px; font-size: 0.9rem; border-radius: 6px;">
          <i class="bi bi-person-badge-fill"></i>
        </div>
        <div>
          <div style="font-size: 0.60rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); line-height: 1;">Médicos Activos</div>
          <div style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary); line-height: 1.1;">{{ number_format($estadisticas['total_medicos'] ?? 0) }}</div>
        </div>
      </div>

      <!-- Registros Hoy -->
      <div class="ingreso-stat-card" style="padding: 0.35rem 0.65rem; display: inline-flex; align-items: center; gap: 0.55rem; border-radius: 8px;">
        <div class="stat-icon-wrap icon-gradient-purple" style="width: 28px; height: 28px; font-size: 0.9rem; border-radius: 6px;">
          <i class="bi bi-calendar2-check-fill"></i>
        </div>
        <div>
          <div style="font-size: 0.60rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); line-height: 1;">Registros Hoy</div>
          <div style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary); line-height: 1.1;">{{ number_format($estadisticas['registros_hoy'] ?? 0) }}</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Botones y Acciones del Header -->
  <div class="page-actions" style="display: flex; align-items: center; gap: 0.45rem; flex-shrink: 0;">
    <a href="{{ route('ingresos.create') }}" class="btn btn-primary btn-sm" style="height: 32px; padding: 0 0.85rem; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 700;">
      <i class="bi bi-plus-lg"></i> Nuevo Registro
    </a>
    <a href="{{ route('registrosat1') }}" class="btn btn-subtle btn-sm" style="height: 32px; width: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;" title="Ver Registros AT1 (Más información de los días de ingresos)">
      <i class="bi bi-table" style="font-size: 0.95rem;"></i>
    </a>
    <a href="{{ route('informesat1') }}" class="btn btn-subtle btn-sm" style="height: 32px; width: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;" title="Ver Informes AT1 (Consolidados e información de ingresos)">
      <i class="bi bi-file-earmark-bar-graph-fill text-primary" style="font-size: 0.95rem;"></i>
    </a>
  </div>
</div>

<!-- =========================================================================
     2. FORMULARIO DE FILTROS MODERNOS
     ========================================================================= -->
<div class="ingresos-filter-card">
  <form method="GET" action="{{ route('ingresos.index') }}" id="filterForm">
    <div class="filter-form-grid">
      <!-- Año -->
      <div>
        <label class="filter-field-label">
          <i class="bi bi-calendar3"></i> Año
        </label>
        <select name="ano" class="filter-input-control" onchange="document.getElementById('filterForm').submit()">
          @foreach($anos as $year)
            <option value="{{ $year }}" {{ (string)$ano === (string)$year ? 'selected' : '' }}>{{ $year }}</option>
          @endforeach
        </select>
      </div>

      <!-- Mes -->
      <div>
        <label class="filter-field-label">
          <i class="bi bi-calendar-month"></i> Mes
        </label>
        <select name="mes" class="filter-input-control" onchange="document.getElementById('filterForm').submit()">
          @php
            $mesesList = [
              '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
              '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
              '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
            ];
          @endphp
          @foreach($mesesList as $num => $nombre)
            <option value="{{ $nombre }}" {{ strtoupper(trim($mes ?? '')) === strtoupper(trim($nombre)) ? 'selected' : '' }}>{{ $nombre }}</option>
          @endforeach
        </select>
      </div>

      <!-- Fecha Específica -->
      <div>
        <label class="filter-field-label">
          <i class="bi bi-calendar-event"></i> Fecha Específica
        </label>
        <input type="date" name="fecha_calendario" class="filter-input-control" value="{{ request('fecha_calendario', '') }}" onchange="document.getElementById('filterForm').submit()">
      </div>

      <!-- Médico -->
      <div>
        <label class="filter-field-label">
          <i class="bi bi-person"></i> Médico
        </label>
        <select name="medico" class="filter-input-control" onchange="document.getElementById('filterForm').submit()">
          <option value="">Todos los médicos</option>
          @foreach($medicosUnicos as $med)
            <option value="{{ $med }}" {{ request('medico') == $med ? 'selected' : '' }}>{{ $med }}</option>
          @endforeach
        </select>
      </div>

      <!-- Jornada -->
      <div>
        <label class="filter-field-label">
          <i class="bi bi-clock"></i> Jornada
        </label>
        <select name="jornada" class="filter-input-control" onchange="document.getElementById('filterForm').submit()">
          <option value="">Todas las jornadas</option>
          @foreach($jornadas as $j)
            <option value="{{ $j }}" {{ request('jornada') == $j ? 'selected' : '' }}>{{ ucfirst(strtolower($j)) }}</option>
          @endforeach
        </select>
      </div>

      <!-- Acciones de Filtro -->
      <div style="display: flex; gap: 0.35rem;">
        <button type="submit" class="btn btn-primary btn-sm" style="height: 32px; flex: 1; font-size: 0.78rem; font-weight: 600;">
          <i class="bi bi-funnel"></i> Filtrar
        </button>
        <a href="{{ route('ingresos.index') }}" class="btn btn-subtle btn-sm" style="height: 32px; padding: 0 0.55rem; font-size: 0.78rem;" title="Limpiar filtros">
          <i class="bi bi-x-lg"></i>
        </a>
      </div>
    </div>
  </form>
</div>

<!-- =========================================================================
     3. CONTENEDOR CON SCROLL CÓMODO PARA LAS FECHAS
     ========================================================================= -->
<div class="fechas-wrapper-card">
  @if(isset($fechasConMedicos) && $fechasConMedicos->count() > 0)
    <div class="fechas-scroll-container">
      @foreach($fechasConMedicos as $idx => $fechaGrupo)
        @php
          $fechaFormatted = \Carbon\Carbon::parse($fechaGrupo->fecha)->format('Y-m-d');
          $fechaId = 'fecha-' . $fechaFormatted;
          $isFirst = $idx === 0;
        @endphp
        <div class="fecha-card {{ $isFirst ? 'open' : '' }}" id="card-{{ $fechaId }}" data-fecha="{{ $fechaFormatted }}">
          
          <!-- Encabezado de Fecha con Gradiente Original Estadísticas 1.5 (#0f6674 -> #0c5460) -->
          <div class="fecha-card-header" onclick="toggleFecha('{{ $fechaId }}')">
            <div class="fecha-title-info">
              <div class="fecha-icon-box">
                <i class="bi bi-calendar-date"></i>
              </div>
              <div>
                <div class="fecha-text-main">{{ \Carbon\Carbon::parse($fechaGrupo->fecha)->format('d-m-Y') }}</div>
                <div class="fecha-text-sub">{{ \Carbon\Carbon::parse($fechaGrupo->fecha)->locale('es')->isoFormat('dddd') }}</div>
              </div>
            </div>

            <div class="fecha-stats-and-actions">
              <span class="fecha-badge-count">
                {{ number_format($fechaGrupo->total_atenciones_fecha) }} atenciones
              </span>

              <div onclick="event.stopPropagation();" style="display: flex; gap: 0.3rem;">
                <a href="{{ route('ingresos.detalles-fecha', $fechaGrupo->fecha) }}" class="btn-fecha-action" title="Ver todos los registros de esta fecha">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('ingresos.create', ['fecha' => $fechaGrupo->fecha]) }}" class="btn-fecha-action" title="Agregar registros en esta fecha">
                  <i class="bi bi-plus-lg"></i>
                </a>
              </div>

              <i class="bi bi-chevron-down expand-chevron" id="chevron-{{ $fechaId }}"></i>
            </div>
          </div>

          <!-- Cuerpo con Lista de Médicos en esta Fecha -->
          <div class="medicos-list-body" id="body-{{ $fechaId }}" style="{{ $isFirst ? '' : 'display: none;' }}">
            @forelse($fechaGrupo->medicos as $medico)
              @php
                $medicoSlug = \Str::slug($fechaGrupo->fecha . '-' . $medico->nom_med);
                $subId = 'sub-' . $medicoSlug;
                $hasSubRegistros = isset($medico->sub_registros) && count($medico->sub_registros) > 0;
                $hasMultiple = isset($medico->sub_registros) && count($medico->sub_registros) > 1;
              @endphp
              <div>
                <!-- Tarjeta del Médico -->
                <div class="medico-item-card" onclick="toggleSubregistros('{{ $subId }}')" style="cursor: pointer;">
                  <div class="medico-primary-info">
                    @if($hasSubRegistros)
                      <button type="button" class="btn-icon" style="border: none; background: transparent; color: var(--text-muted); cursor: pointer; padding: 0; width: 18px;" id="icon-{{ $subId }}">
                        <i class="bi bi-chevron-right" style="font-size: 0.8rem;"></i>
                      </button>
                    @endif

                    <div class="medico-badge-avatar">
                      {{ strtoupper(substr($medico->nom_med ?? 'M', 0, 2)) }}
                    </div>

                    <div>
                      <div class="medico-name-title">
                        <span>{{ $medico->nom_med ?? 'Sin Nombre' }}</span>
                        @if($hasMultiple)
                          <span class="badge badge-subtle-primary" style="font-size: 0.68rem; padding: 0.15rem 0.45rem;">
                            {{ count($medico->sub_registros) }} envíos
                          </span>
                        @endif
                      </div>
                    </div>
                  </div>

                  <!-- Badges de Estadísticas del Médico -->
                  <div class="medico-badges-wrap">
                    <div class="stat-pill pill-user" title="Usuario que ingresó">
                      <i class="bi bi-person-circle"></i>
                      <span>{{ $medico->user_name ?: 'S/U' }}</span>
                    </div>

                    <div class="stat-pill pill-jornada" title="Jornada de atención">
                      <i class="bi bi-clock-history"></i>
                      <span>{{ $medico->jornada ?: 'Sin jornada' }}</span>
                    </div>

                    <div class="stat-pill pill-atenciones" title="Total atenciones">
                      <span>{{ $medico->total_registros }} Atenc.</span>
                    </div>

                    <div class="stat-pill pill-diagnosticos" title="Total diagnósticos">
                      <span>{{ $medico->total_diagnosticos }} Diag.</span>
                    </div>

                    @if($medico->total_menores_5 > 0)
                      <div class="stat-pill pill-menores" title="Menores de 5 años">
                        <span>{{ $medico->total_menores_5 }} &lt;5A</span>
                      </div>
                    @endif
                  </div>

                  <!-- Botones de Acción del Médico -->
                  <div class="medico-actions-group" onclick="event.stopPropagation();" style="display: flex; gap: 0.3rem;">
                    <a href="{{ route('ingresos.detalles-medico', ['fecha' => $fechaGrupo->fecha, 'medico' => $medico->nom_med]) }}" class="btn btn-subtle btn-sm btn-icon" style="width: 28px; height: 28px; font-size: 0.76rem;" title="Ver todas las atenciones del médico">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('ingresos.create', ['fecha' => $fechaGrupo->fecha, 'medico' => $medico->nom_med]) }}" class="btn btn-subtle btn-sm btn-icon" style="width: 28px; height: 28px; font-size: 0.76rem;" title="Agregar más atenciones a este médico">
                      <i class="bi bi-plus-lg"></i>
                    </a>
                    <button type="button" class="btn btn-subtle btn-sm btn-icon text-danger" style="width: 28px; height: 28px; font-size: 0.76rem;" title="Eliminar registros de este médico en esta fecha" onclick="eliminarGrupoMedico('{{ $fechaGrupo->fecha }}', '{{ addslashes($medico->nom_med) }}')">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </div>

                <!-- Sub-despliegue de Envíos / Lotes del Médico -->
                @if($hasSubRegistros)
                  <div class="subregistros-container" id="{{ $subId }}" style="display: none;">
                    @foreach($medico->sub_registros as $sIdx => $sub)
                      <div class="subregistro-row">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                          <span style="width: 20px; height: 20px; border-radius: 50%; background: var(--bg-surface-hover); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.7rem;">
                            {{ $sIdx + 1 }}
                          </span>
                          <div>
                            <strong>{{ $sub->user_name }}</strong>
                            @if($sub->created_at_formatted)
                              <span style="color: var(--text-muted); font-size: 0.72rem; margin-left: 0.25rem;">({{ $sub->created_at_formatted }})</span>
                            @endif
                            <span style="color: var(--text-muted); margin-left: 0.4rem;">• Jornada: <strong>{{ $sub->jornada }}</strong></span>
                          </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.45rem;">
                          <span class="badge badge-subtle-primary" style="font-size: 0.72rem;">{{ $sub->total_registros }} Atenc.</span>
                          <span class="badge badge-subtle-success" style="font-size: 0.72rem;">{{ $sub->total_diagnosticos }} Diag.</span>
                          @if($sub->total_menores_5 > 0)
                            <span class="badge badge-subtle-danger" style="font-size: 0.72rem;">{{ $sub->total_menores_5 }} &lt;5A</span>
                          @endif

                          <div style="display: flex; gap: 0.25rem; margin-left: 0.35rem;">
                            <a href="{{ route('ingresos.detalles-medico', ['fecha' => $fechaGrupo->fecha, 'medico' => $medico->nom_med]) }}?ids={{ implode(',', $sub->record_ids) }}" class="btn btn-subtle btn-sm btn-icon" style="width: 24px; height: 24px; font-size: 0.72rem;" title="Ver solo este lote">
                              <i class="bi bi-eye"></i>
                            </a>
                            <button type="button" class="btn btn-subtle btn-sm btn-icon text-danger" style="width: 24px; height: 24px; font-size: 0.72rem;" title="Eliminar solo este lote" onclick="eliminarLoteIds('{{ implode(',', $sub->record_ids) }}')">
                              <i class="bi bi-trash"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                @endif
              </div>
            @empty
              <div style="text-align: center; padding: 1rem; color: var(--text-muted); font-size: 0.8rem;">
                <i class="bi bi-person-x" style="font-size: 1.4rem; display: block; margin-bottom: 0.25rem; opacity: 0.5;"></i>
                <span>No se encontraron registros de médicos en esta fecha</span>
              </div>
            @endforelse
          </div>
        </div>
      @endforeach
    </div>
  @else
    <!-- Estado Vacío -->
    <div style="text-align: center; padding: 3rem 1.5rem; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;">
      <div style="width: 58px; height: 58px; border-radius: 50%; background: var(--bg-surface-hover); color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 0.85rem;">
        <i class="bi bi-calendar-x"></i>
      </div>
      <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.3rem;">No se encontraron registros</h3>
      <p style="font-size: 0.84rem; color: var(--text-muted); max-width: 400px; margin: 0 auto 1.15rem;">No hay atenciones ingresadas que coincidan con los filtros seleccionados de fecha, médico o jornada.</p>
      <a href="{{ route('ingresos.create') }}" class="btn btn-primary btn-sm" style="font-weight: 600;">
        <i class="bi bi-plus-lg"></i> Crear Nuevo Registro
      </a>
    </div>
  @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/sweetalert2.min.js') }}"></script>
<script>
  // Control de Apertura / Cierre de Fechas
  function toggleFecha(fechaId) {
    const body = document.getElementById('body-' + fechaId);
    const card = document.getElementById('card-' + fechaId);
    if (!body || !card) return;

    if (body.style.display === 'none') {
      body.style.display = 'flex';
      card.classList.add('open');
    } else {
      body.style.display = 'none';
      card.classList.remove('open');
    }
  }

  // Control de Apertura / Cierre de Subregistros por Médico
  function toggleSubregistros(subId) {
    const container = document.getElementById(subId);
    const iconBtn = document.getElementById('icon-' + subId);
    if (!container) return;

    if (container.style.display === 'none') {
      container.style.display = 'flex';
      if (iconBtn) iconBtn.innerHTML = '<i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>';
    } else {
      container.style.display = 'none';
      if (iconBtn) iconBtn.innerHTML = '<i class="bi bi-chevron-right" style="font-size: 0.8rem;"></i>';
    }
  }

  // ─── Eliminar Grupo Completo de Médico en Fecha ───────────────────────────
  async function eliminarGrupoMedico(fecha, medico) {
    const result = await Swal.fire({
      title: '¿Eliminar grupo?',
      html: `<p style="margin:0;font-size:0.95rem;">Se eliminarán <strong>todas las atenciones</strong> de<br><span style="font-weight:800;color:#ef4444;">${medico}</span><br>registradas el <strong>${fecha}</strong>.</p><p style="margin-top:0.6rem;font-size:0.82rem;color:#94a3b8;">Esta acción no se puede deshacer.</p>`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-trash3-fill"></i> Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
      focusCancel: true,
      reverseButtons: true,
    });

    if (!result.isConfirmed) return;

    try {
      const res  = await fetch('{{ route("ingresos.eliminar-grupo") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ fecha, cod_med: medico })
      });
      const data = await res.json();

      if (data.success) {
        await Swal.fire({
          icon: 'success',
          title: 'Eliminado',
          text: data.message || 'Registros eliminados correctamente',
          timer: 1800,
          showConfirmButton: false,
        });
        location.reload();
      } else {
        Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Ocurrió un error al eliminar los registros' });
      }
    } catch (err) {
      console.error(err);
      Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo completar la eliminación.' });
    }
  }

  // ─── Eliminar Lote Específico por IDs ────────────────────────────────────
  async function eliminarLoteIds(ids) {
    const result = await Swal.fire({
      title: '¿Eliminar lote?',
      html: '<p style="margin:0;font-size:0.95rem;">Se eliminará este <strong>lote de atenciones</strong>.</p><p style="margin-top:0.6rem;font-size:0.82rem;color:#94a3b8;">Esta acción no se puede deshacer.</p>',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-trash3-fill"></i> Sí, eliminar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6b7280',
      focusCancel: true,
      reverseButtons: true,
    });

    if (!result.isConfirmed) return;

    try {
      await fetch('{{ route("ingresos.eliminar-grupo") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ ids: ids.split(',') })
      });
      await Swal.fire({
        icon: 'success',
        title: 'Lote eliminado',
        text: 'El lote de atenciones fue eliminado correctamente.',
        timer: 1800,
        showConfirmButton: false,
      });
      location.reload();
    } catch (err) {
      console.error(err);
      Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo completar la eliminación.' });
    }
  }
</script>
@endpush
