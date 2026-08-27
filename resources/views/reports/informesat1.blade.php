@extends('layouts.app')

@section('title', 'Informes AT1 - Segmentado por Diagnóstico')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/sing-excel-table.css') }}">
  <style>
    .app-footer {
      display: none !important;
    }

    .app-content {
      padding: 0.6rem 0.85rem !important;
      height: calc(100vh - var(--navbar-height)) !important;
      max-height: calc(100vh - var(--navbar-height)) !important;
      display: flex !important;
      flex-direction: column !important;
      overflow: hidden !important;
      width: 100% !important;
      min-width: 0 !important;
      max-width: 100% !important;
    }

    /* Specific Column Widths for Informes AT1 */
    .col-diag-num {
      min-width: 75px !important;
      max-width: 85px !important;
      text-align: center !important;
      font-weight: 700 !important;
      color: var(--color-primary) !important;
    }

    .col-diag-code {
      min-width: 90px !important;
      max-width: 110px !important;
      font-weight: 700 !important;
      color: #38bdf8 !important;
      text-align: center !important;
    }

    .col-diag-desc {
      min-width: 260px !important;
      max-width: 380px !important;
      font-weight: 600 !important;
      color: var(--text-primary) !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
    }

    .col-cond-badge {
      min-width: 75px !important;
      max-width: 85px !important;
      text-align: center !important;
      font-weight: 600 !important;
    }

    .col-paciente-text {
      min-width: 220px !important;
      max-width: 300px !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
    }

    .col-medico-text {
      min-width: 180px !important;
      max-width: 240px !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
    }

    .badge-diag-indicator {
      padding: 0.15rem 0.45rem;
      font-size: 0.72rem;
      font-weight: 700;
      border-radius: var(--radius-xs, 4px);
      background: rgba(var(--color-primary-rgb, 77, 124, 254), 0.16);
      color: var(--color-primary, #60a5fa);
      border: 1px solid rgba(var(--color-primary-rgb, 77, 124, 254), 0.35);
    }
  </style>
@endpush

@section('content')
  <!-- =========================================================================
       TABLA EXCEL INTERACTIVA INFORMES AT1 (SEGMENTADA POR DIAGNÓSTICO)
       ========================================================================= -->
  <div class="sing-card-excel-fullscreen">
    <!-- Card Header con Título, Búsqueda Rápida, Filtros de Período y Acciones -->
    <div class="card-header"
      style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; padding: 0.45rem 0.85rem; border-bottom: 1px solid var(--border-color);">
      <!-- Título de la Tabla -->
      <div style="display: flex; align-items: center; gap: 0.55rem;">
        <div
          style="width: 28px; height: 28px; border-radius: var(--radius-xs, 4px); background: linear-gradient(135deg, #0ea5e9, #6366f1); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; box-shadow: 0 2px 6px rgba(14, 165, 233, 0.35); flex-shrink: 0;">
          <i class="bi bi-file-earmark-bar-graph-fill"></i>
        </div>
        <div>
          <h2 class="card-title"
            style="font-size: 0.92rem; margin-bottom: 0; font-weight: 700; color: var(--text-primary); line-height: 1.2;">
            Informes AT1 <span style="font-size: 0.76rem; font-weight: 500; color: var(--color-primary);">(Segmentado por
              Diagnóstico)</span>
          </h2>
          <span style="font-size: 0.72rem; color: var(--text-muted);">
            Total Diagnósticos:
            <strong>{{ number_format($stats['total_diagnosticos'] ?? count($segmentedRows)) }}</strong> | Consultas:
            <strong>{{ number_format($stats['total_consultas'] ?? 0) }}</strong>
          </span>
        </div>
      </div>

      <!-- Filtros de Período y Acciones Integradas Arriba -->
      <form id="filterPeriodForm" method="GET" action="{{ route('informesat1') }}"
        style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap; margin: 0;">
        <!-- Búsqueda Rápida Integrada Arriba -->
        <div style="position: relative; width: 205px;">
          <i class="bi bi-search"
            style="position: absolute; left: 0.65rem; top: 0.52rem; color: var(--text-muted); font-size: 0.78rem;"></i>
          <input type="text" id="excelGlobalSearch" class="form-control form-control-sm"
            style="padding-left: 1.85rem; height: 32px; font-size: 0.82rem; border-radius: var(--radius-xs, 4px);"
            placeholder="Búsqueda rápida...">
        </div>

        <!-- Select2 Años -->
        <div style="display: inline-flex; width: auto; min-width: 110px; max-width: 260px;">
          <select id="selectYear" name="years[]" class="form-control select2-filter" multiple="multiple">
            @foreach($anos as $ano)
              <option value="{{ $ano }}" {{ in_array((string) $ano, $selectedYears ?? []) ? 'selected' : '' }}>{{ $ano }}
              </option>
            @endforeach
          </select>
        </div>

        <!-- Select2 Meses -->
        <div style="display: inline-flex; width: auto; min-width: 140px; max-width: 320px;">
          <select id="selectMonth" name="months[]" class="form-control select2-filter" multiple="multiple">
            @foreach($mesesDisponibles as $m)
              <option value="{{ $m }}" {{ in_array((string) $m, $selectedMonths ?? []) ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
          </select>
        </div>

        <!-- Botones Consultar / Reset Período -->
        <button type="submit" id="btnApplyFilter" class="btn btn-toolbar-consultar btn-sm"
          style="height: 32px; padding: 0 0.8rem; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem;">
          <i class="bi bi-search"></i> Consultar
        </button>
        <a href="{{ route('informesat1') }}" id="btnResetFilter" class="btn btn-toolbar-reset btn-sm btn-icon"
          style="height: 32px; width: 32px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;"
          title="Restablecer período">
          <i class="bi bi-arrow-counterclockwise"></i>
        </a>

        <button type="button" class="btn btn-toolbar-clear btn-sm" id="btnResetAllExcelFilters"
          title="Limpiar todos los filtros de columna"
          style="display: none; height: 32px; font-size: 0.78rem; padding: 0 0.65rem;">
          <i class="bi bi-funnel-fill"></i> Limpiar
        </button>

        <div style="height: 20px; width: 1px; background-color: var(--border-color); margin: 0 0.15rem;"></div>

        <!-- Botones de Acción -->
        <button type="button" class="btn btn-toolbar-xlsx btn-sm" id="btnExportExcelXLSX"
          style="height: 32px; padding: 0 0.75rem; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem;"
          title="Exportar a Excel (.xlsx)">
          <i class="bi bi-file-earmark-spreadsheet-fill"></i> XLSX
        </button>
        <button type="button" class="btn btn-toolbar-reset btn-sm btn-icon" data-action="fullscreen"
          title="Pantalla Completa" style="height: 32px; width: 32px;"><i class="bi bi-fullscreen"></i></button>
      </form>
    </div>

    <!-- Contenedor con Scroll de la Tabla Excel -->
    <div class="excel-table-scroll">
      <table class="sing-table-excel" id="excelInformesAt1Table">
        <thead>
          <tr>
            <th class="col-row-num">#</th>
            <th data-col="0" data-title="N°">
              <div class="excel-th-content"><span>N°</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="1" data-title="CM">
              <div class="excel-th-content"><span>CM</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="2" data-title="MÉDICO">
              <div class="excel-th-content"><span>MÉDICO</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="3" data-title="PROF">
              <div class="excel-th-content"><span>PROF</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="4" data-title="FECHA" data-type="date">
              <div class="excel-th-content"><span>FECHA</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="5" data-title="SE">
              <div class="excel-th-content"><span>SE</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="6" data-title="EXP">
              <div class="excel-th-content"><span>EXP</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="7" data-title="PACIENTE">
              <div class="excel-th-content"><span>PACIENTE</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="8" data-title="IDENTIDAD">
              <div class="excel-th-content"><span>IDENTIDAD</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="9" data-title="TELÉFONO">
              <div class="excel-th-content"><span>TELÉFONO</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="10" data-title="F. NACIMIENTO" data-type="date">
              <div class="excel-th-content"><span>F. NACIMIENTO</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="11" data-title="ETNIA">
              <div class="excel-th-content"><span>ETNIA</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="12" data-title="SEXO">
              <div class="excel-th-content"><span>SEXO</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="13" data-title="EDAD">
              <div class="excel-th-content"><span>EDAD</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="14" data-title="TIPO">
              <div class="excel-th-content"><span>TIPO</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="15" data-title="RANGO">
              <div class="excel-th-content"><span>RANGO</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="16" data-title="COLONIA">
              <div class="excel-th-content"><span>COLONIA</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="17" data-title="N° DIAG" style="background-color: rgba(14, 165, 233, 0.18);">
              <div class="excel-th-content"><span style="color:#38bdf8;">N° DIAG</span><button type="button"
                  class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="18" data-title="CÓDIGO CIE-10" style="background-color: rgba(14, 165, 233, 0.18);">
              <div class="excel-th-content"><span style="color:#38bdf8;">CÓDIGO</span><button type="button"
                  class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="19" data-title="DIAGNÓSTICO" style="background-color: rgba(14, 165, 233, 0.18);">
              <div class="excel-th-content"><span style="color:#38bdf8;">DIAGNÓSTICO</span><button type="button"
                  class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="20" data-title="CONDICIÓN" style="background-color: rgba(14, 165, 233, 0.18);">
              <div class="excel-th-content"><span style="color:#38bdf8;">COND</span><button type="button"
                  class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="21" data-title="SG">
              <div class="excel-th-content"><span>SG</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="22" data-title="REF. ENVIADA">
              <div class="excel-th-content"><span>REF. ENVIADA</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="23" data-title="REF. RECIBIDA">
              <div class="excel-th-content"><span>REF. RECIBIDA</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="24" data-title="POBLACIÓN GENERAL">
              <div class="excel-th-content"><span>POBLACIÓN GENERAL</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="25" data-title="JORNADA">
              <div class="excel-th-content"><span>JORNADA</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="26" data-title="AÑO">
              <div class="excel-th-content"><span>AÑO</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="27" data-title="MES">
              <div class="excel-th-content"><span>MES</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
            <th data-col="28" data-title="ID REGISTRO">
              <div class="excel-th-content"><span>ID REGISTRO</span><button type="button" class="excel-filter-btn"
                  title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
            </th>
          </tr>
        </thead>
        <tbody id="excelTableBody">
          <!-- Renderizado Progresivo en Chunks de 200 registros via SingExcelTable Engine -->
        </tbody>
      </table>
    </div>

    <!-- Footer Dinámico Tipo Excel (Status Bar) -->
    <div class="table-excel-footer">
      <div class="table-excel-footer-left">
        <span class="excel-status-badge"
          style="background: var(--bg-surface-hover); padding: 0.2rem 0.5rem; border-radius: var(--radius-xs, 4px); border: 1px solid var(--border-color);">
          <i class="bi bi-activity text-primary"></i> <span id="excelStatTotal">{{ count($segmentedRows) }}</span>
          Diagnósticos
        </span>
        <span id="excelStatFiltered" class="text-primary font-weight-bold" style="font-size: 0.78rem;"></span>
        <span id="excelProgressiveBadge" class="badge badge-subtle-info" style="display:none; font-size: 0.72rem;">
          <span id="excelProgressiveText"></span>
        </span>
        <span id="excelActiveFiltersBadge" class="badge badge-subtle-primary" style="display:none; font-size: 0.72rem;">
          <i class="bi bi-funnel-fill"></i> <span id="excelActiveFiltersCount">0</span> filtro(s) activo(s)
        </span>
      </div>
      <div class="table-excel-footer-right">
        <span style="font-size: 0.76rem; color: var(--text-muted);">Período: [{{ $anoActual ?: 'Todos' }}] -
          [{{ $mesSeleccionado ?: 'Todos' }}]</span>
      </div>
    </div>
  </div>

  <!-- Dataset serializado en JSON para carga instantánea en memoria sin saturar el DOM -->
  <script id="informesDataJson" type="application/json">@json($segmentedRows)</script>
@endsection

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
  <script src="{{ asset('assets/js/sing-excel-table.js') }}"></script>
  <script>
    $(document).ready(function () {
      const activeCard = document.querySelector('.sing-card-excel-fullscreen');
      const select2Parent = activeCard ? $(activeCard) : $(document.body);

      // Initialize Top Period Select2
      $('#selectYear').select2({
        placeholder: 'Años...',
        closeOnSelect: false,
        allowClear: false,
        dropdownAutoWidth: false,
        dropdownParent: select2Parent,
        width: 'style'
      });

      $('#selectMonth').select2({
        placeholder: 'Meses...',
        closeOnSelect: false,
        allowClear: false,
        dropdownAutoWidth: false,
        dropdownParent: select2Parent,
        width: 'style'
      });

      // Initialize Excel Table Engine for Informes AT1
      window.informesExcel = new SingExcelTable('excelInformesAt1Table', {
        dataScriptId: 'informesDataJson',
        storagePrefix: 'sing_informesat1',
        exportSheetName: 'Informes AT1',
        exportFileName: 'informes_at1_diagnosticos',
        chunkSize: 200
      });
    });
  </script>
@endpush