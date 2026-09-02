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

    .modal-custom-backdrop {
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1040;
      width: 100vw;
      height: 100vh;
      background-color: rgba(0, 0, 0, 0.5);
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
            <th class="col-actions text-center" style="width: 80px; min-width: 80px; background-color: rgba(14, 165, 233, 0.22); color: #38bdf8; font-weight: 700;">
              ACCIONES
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

  <!-- Modal para Editar Diagnóstico de Informes AT1 -->
  <div class="modal fade" id="modalEditarDiagnostico" tabindex="-1" aria-labelledby="modalEditarDiagLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
      <div class="modal-content" style="background: var(--bg-surface, #1e293b); color: var(--text-primary, #f8fafc); border: 1px solid var(--border-color, #334155); border-radius: var(--radius-md, 8px); box-shadow: 0 12px 30px rgba(0,0,0,0.45);">
        <div class="modal-header" style="border-bottom: 1px solid var(--border-color, #334155); padding: 0.8rem 1.2rem;">
          <div class="d-flex align-items-center gap-2">
            <div style="width: 30px; height: 30px; border-radius: 6px; background: rgba(14, 165, 233, 0.15); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
              <i class="bi bi-pencil-square"></i>
            </div>
            <div>
              <h5 class="modal-title font-weight-bold mb-0" id="modalEditarDiagLabel" style="font-size: 0.92rem; color: var(--text-primary, #f8fafc);">Editar Diagnóstico AT-1</h5>
              <small id="modalSubTitle" style="color: var(--text-muted, #94a3b8); font-size: 0.74rem;">Modificar diagnóstico, condición y código CIE-10</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <form id="formEditarDiagnostico">
          @csrf
          <input type="hidden" id="edit_registro_id" name="registro_id">
          <input type="hidden" id="edit_diag_index" name="diag_index">

          <div class="modal-body" style="padding: 1.15rem;">
            <!-- Información Resumen del Paciente -->
            <div class="p-2 mb-3" style="background: var(--bg-surface-hover, #0f172a); border-radius: 6px; border: 1px solid var(--border-color, #334155); font-size: 0.78rem;">
              <div class="d-flex justify-content-between mb-1">
                <span style="color: var(--text-muted);">Paciente:</span>
                <strong id="edit_paciente_nombre" style="color: var(--text-primary);">--</strong>
              </div>
              <div class="d-flex justify-content-between mb-1">
                <span style="color: var(--text-muted);">Expediente / Fecha:</span>
                <span id="edit_paciente_exp" style="color: var(--text-primary);">--</span>
              </div>
              <div class="d-flex justify-content-between">
                <span style="color: var(--text-muted);">Médico Responsable:</span>
                <span id="edit_paciente_medico" style="color: var(--text-primary);">--</span>
              </div>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-4">
                <label class="form-label font-weight-bold mb-1" style="font-size: 0.78rem;">N° Diagnóstico</label>
                <input type="text" id="edit_diag_label" class="form-control form-control-sm" readonly style="background: var(--bg-surface-hover); color: #38bdf8; font-weight: 700; text-align: center;">
              </div>
              <div class="col-4">
                <label class="form-label font-weight-bold mb-1" style="font-size: 0.78rem;">Código CIE-10</label>
                <input type="text" id="edit_cod" name="cod" class="form-control form-control-sm" list="codigosDatalist" placeholder="Ej: 4 / A09 / 102" autocomplete="off" style="background: var(--input-bg, #0f172a); color: var(--text-primary, #fff); border: 1px solid var(--border-color); font-weight: 700;">
                <datalist id="codigosDatalist"></datalist>
              </div>
              <div class="col-4">
                <label class="form-label font-weight-bold mb-1" style="font-size: 0.78rem;">Condición</label>
                <select id="edit_cond" name="cond" class="form-select form-select-sm" style="background: var(--input-bg, #0f172a); color: var(--text-primary, #fff); border: 1px solid var(--border-color);">
                  <option value="">(Ninguna)</option>
                  <option value="N">N - Nuevo</option>
                  <option value="S">S - Subsiguiente</option>
                </select>
              </div>
            </div>

            <div class="mb-1">
              <label class="form-label font-weight-bold mb-1" style="font-size: 0.78rem;">Diagnóstico / Patología</label>
              <input type="text" id="edit_diagnostico" name="diagnostico" class="form-control form-control-sm" list="diagnosticosDatalist" placeholder="Escribe para buscar o ingresar diagnóstico..." autocomplete="off" style="background: var(--input-bg, #0f172a); color: var(--text-primary, #fff); border: 1px solid var(--border-color);">
              <datalist id="diagnosticosDatalist"></datalist>
              <small style="font-size: 0.71rem; color: var(--text-muted); display: block; margin-top: 0.25rem;">Se actualizará simultáneamente en <strong>Registros Globales</strong> y en la vista <strong>Informes AT1</strong>.</small>
            </div>
          </div>
          <div class="modal-footer" style="border-top: 1px solid var(--border-color, #334155); padding: 0.65rem 1.2rem;">
            <button type="button" class="btn btn-sm btn-subtle" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" id="btnGuardarEditDiag" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 0.35rem;">
              <i class="bi bi-check-lg"></i> Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Dataset serializado en JSON para carga instantánea en memoria sin saturar el DOM -->
  <script id="informesDataJson" type="application/json">@json($segmentedRows)</script>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
  <script src="{{ asset('assets/js/sing-excel-table.js') }}"></script>
  <script>
    $(document).ready(function () {
      const activeCard = document.querySelector('.sing-card-excel-fullscreen');
      const select2Parent = activeCard ? $(activeCard) : $(document.body);

      // Helpers robustos para apertura y cierre de modal
      function abrirModalEditar() {
        const $modal = $('#modalEditarDiagnostico');
        if (typeof $modal.modal === 'function') {
          $modal.modal('show');
        } else if (window.bootstrap && typeof bootstrap.Modal === 'function') {
          try {
            const m = bootstrap.Modal.getInstance ? bootstrap.Modal.getInstance($modal[0]) : null;
            (m || new bootstrap.Modal($modal[0])).show();
          } catch(e) {
            $modal.addClass('show').css('display', 'block');
            $('body').addClass('modal-open');
          }
        } else {
          $modal.addClass('show').css('display', 'block');
          $('body').addClass('modal-open').append('<div class="modal-backdrop fade show modal-custom-backdrop"></div>');
        }
      }

      function cerrarModalEditar() {
        const $modal = $('#modalEditarDiagnostico');
        if (typeof $modal.modal === 'function') {
          $modal.modal('hide');
        } else if (window.bootstrap && typeof bootstrap.Modal === 'function') {
          try {
            const m = bootstrap.Modal.getInstance ? bootstrap.Modal.getInstance($modal[0]) : null;
            if (m) m.hide();
          } catch(e) {}
        }
        $modal.removeClass('show').css('display', 'none');
        $('body').removeClass('modal-open');
        $('.modal-custom-backdrop').remove();
        $('.modal-backdrop').remove();
      }

      // Eventos para cerrar modal con botones data-dismiss
      $(document).on('click', '#modalEditarDiagnostico [data-dismiss="modal"], #modalEditarDiagnostico [data-bs-dismiss="modal"]', function(e) {
        e.preventDefault();
        cerrarModalEditar();
      });

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

      // Cargar catálogo de diagnósticos para autocompletado en Datalists
      let catalogoDiags = [];

      function poblarDatalists(data) {
        catalogoDiags = data || [];
        const diagDatalist = document.getElementById('diagnosticosDatalist');
        const codDatalist = document.getElementById('codigosDatalist');

        if (diagDatalist && catalogoDiags.length > 0) {
          diagDatalist.innerHTML = catalogoDiags.map(d => 
            `<option value="${d.patologia}">${d.codigo ? d.codigo + ' - ' : ''}${d.patologia}</option>`
          ).join('');
        }

        if (codDatalist && catalogoDiags.length > 0) {
          codDatalist.innerHTML = catalogoDiags
            .filter(d => d.codigo && String(d.codigo).trim() !== '')
            .map(d => `<option value="${d.codigo}">${d.codigo} - ${d.patologia}</option>`)
            .join('');
        }
      }

      fetch("{{ route('informesat1.buscarDiagnosticos') }}")
        .then(res => res.json())
        .then(data => poblarDatalists(data))
        .catch(err => console.warn('No se pudo precargar catálogo de diagnósticos:', err));

      // Función para buscar y autocompletar diagnóstico según el código ingresado
      function buscarDiagPorCodigo(rawCod) {
        if (!rawCod) return;
        const codVal = String(rawCod).trim();
        if (codVal === '') return;

        // 1) Buscar coincidencia exacta en el catálogo precargado
        const found = catalogoDiags.find(d => 
          String(d.codigo || '').trim().toUpperCase() === codVal.toUpperCase()
        );

        if (found && found.patologia) {
          $('#edit_diagnostico').val(found.patologia);
          return;
        }

        // 2) Si no se encontró en memoria, consultar al servidor
        fetch(`{{ route('informesat1.buscarDiagnosticos') }}?q=${encodeURIComponent(codVal)}`)
          .then(res => res.json())
          .then(data => {
            if (data && data.length > 0) {
              const exact = data.find(d => String(d.codigo || '').trim().toUpperCase() === codVal.toUpperCase()) || data[0];
              if (exact && exact.patologia) {
                $('#edit_diagnostico').val(exact.patologia);
                if (!catalogoDiags.some(cd => cd.codigo === exact.codigo)) {
                  catalogoDiags.push(exact);
                  poblarDatalists(catalogoDiags);
                }
              }
            }
          })
          .catch(err => console.warn('Error buscando código en catálogo:', err));
      }

      // Al escribir o cambiar el CÓDIGO CIE-10 -> autocompletar DIAGNÓSTICO
      $('#edit_cod').on('input change blur', function () {
        buscarDiagPorCodigo($(this).val());
      });

      // Al escribir o cambiar el DIAGNÓSTICO -> autocompletar CÓDIGO si coincide
      $('#edit_diagnostico').on('input change blur', function () {
        const val = $(this).val().trim().toUpperCase();
        if (!val) return;
        const found = catalogoDiags.find(d => (d.patologia || '').toUpperCase() === val);
        if (found && found.codigo) {
          $('#edit_cod').val(found.codigo);
        }
      });

      // Initialize Excel Table Engine for Informes AT1 with Actions Column
      window.informesExcel = new SingExcelTable('excelInformesAt1Table', {
        dataScriptId: 'informesDataJson',
        storagePrefix: 'sing_informesat1',
        exportSheetName: 'Informes AT1',
        exportFileName: 'informes_at1_diagnosticos',
        chunkSize: 200,
        actionsRenderer: function (item, visualRowIndex) {
          const regId = item.cells[28] || '';
          const diagNumStr = item.cells[17] || 'D1';
          const diagNum = diagNumStr.replace(/[^0-9]/g, '') || '1';
          const cod = item.cells[18] || '';
          const diag = item.cells[19] || '';
          const cond = item.cells[20] || '';
          const pac = item.cells[7] || '';
          const exp = item.cells[6] || '';
          const fecha = item.cells[4] || '';
          const medico = item.cells[2] || '';

          const esc = (s) => String(s).replace(/"/g, '&quot;');

          return `
            <div class="d-inline-flex align-items-center gap-1">
              <button type="button" class="btn btn-sm btn-subtle text-primary p-1 btn-edit-diag" 
                title="Editar Diagnóstico"
                data-reg-id="${esc(regId)}"
                data-diag-num="${esc(diagNum)}"
                data-cod="${esc(cod)}"
                data-diag="${esc(diag)}"
                data-cond="${esc(cond)}"
                data-paciente="${esc(pac)}"
                data-exp="${esc(exp)}"
                data-fecha="${esc(fecha)}"
                data-medico="${esc(medico)}"
                style="height: 24px; width: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px;">
                <i class="bi bi-pencil-square" style="font-size: 0.85rem;"></i>
              </button>
              <button type="button" class="btn btn-sm btn-subtle text-danger p-1 btn-delete-diag" 
                title="Eliminar Diagnóstico"
                data-reg-id="${esc(regId)}"
                data-diag-num="${esc(diagNum)}"
                data-cod="${esc(cod)}"
                data-diag="${esc(diag)}"
                data-paciente="${esc(pac)}"
                data-exp="${esc(exp)}"
                style="height: 24px; width: 24px; display: inline-flex; align-items: center; justify-content: center; border-radius: 4px;">
                <i class="bi bi-trash3" style="font-size: 0.85rem;"></i>
              </button>
            </div>
          `;
        }
      });

      // Delegación de Evento: Clic en Editar Diagnóstico
      $(document).on('click', '.btn-edit-diag', function (e) {
        e.preventDefault();
        const btn = $(this);
        const regId = btn.data('reg-id');
        const diagNum = btn.data('diag-num');
        const cod = btn.data('cod');
        const diag = btn.data('diag');
        const cond = btn.data('cond');
        const pac = btn.data('paciente');
        const exp = btn.data('exp');
        const fecha = btn.data('fecha');
        const medico = btn.data('medico');

        $('#edit_registro_id').val(regId);
        $('#edit_diag_index').val(diagNum);
        $('#edit_diag_label').val('D' + diagNum + ' (Diag. ' + diagNum + ')');
        $('#edit_cod').val(cod);
        $('#edit_diagnostico').val(diag);
        $('#edit_cond').val(cond ? cond.toUpperCase() : '');

        $('#edit_paciente_nombre').text(pac || 'Sin Nombre');
        $('#edit_paciente_exp').text((exp ? 'Exp: ' + exp : 'Sin Exp.') + (fecha ? ' | ' + fecha : ''));
        $('#edit_paciente_medico').text(medico || 'No especificado');

        // Si tiene código pero no diagnóstico, buscarlo automáticamente
        if (cod && (!diag || String(diag).trim() === '')) {
          buscarDiagPorCodigo(cod);
        }

        abrirModalEditar();
      });

      // Enviar formulario de Edición de Diagnóstico
      $('#formEditarDiagnostico').on('submit', function (e) {
        e.preventDefault();
        const btnSubmit = $('#btnGuardarEditDiag');
        const originalHtml = btnSubmit.html();
        btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

        const regId = $('#edit_registro_id').val();
        const diagNum = $('#edit_diag_index').val();
        const cod = $('#edit_cod').val();
        const diag = $('#edit_diagnostico').val();
        const cond = $('#edit_cond').val();

        fetch("{{ route('informesat1.updateDiagnostico') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            registro_id: regId,
            diag_index: diagNum,
            cod: cod,
            diagnostico: diag,
            cond: cond
          })
        })
          .then(res => res.json())
          .then(data => {
            btnSubmit.prop('disabled', false).html(originalHtml);
            if (data.success) {
              cerrarModalEditar();

              // Actualizar datos en memoria en SingExcelTable sin recargar la página
              window.informesExcel.updateRecord(
                item => String(item.cells[28]) === String(regId) && String(item.cells[17]) === ('D' + diagNum),
                item => {
                  item.cells[18] = cod;
                  item.cells[19] = diag;
                  item.cells[20] = cond;
                }
              );

              Swal.fire({
                icon: 'success',
                title: '¡Diagnóstico Actualizado!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
              });
            } else {
              Swal.fire('Error', data.message || 'No se pudo actualizar el diagnóstico.', 'error');
            }
          })
          .catch(err => {
            btnSubmit.prop('disabled', false).html(originalHtml);
            Swal.fire('Error', 'Ocurrió un error en la solicitud de red.', 'error');
            console.error(err);
          });
      });

      // Delegación de Evento: Clic en Eliminar Diagnóstico
      $(document).on('click', '.btn-delete-diag', function (e) {
        e.preventDefault();
        const btn = $(this);
        const regId = btn.data('reg-id');
        const diagNum = btn.data('diag-num');
        const diag = btn.data('diag');
        const cod = btn.data('cod');
        const pac = btn.data('paciente') || 'el paciente';
        const exp = btn.data('exp') || 'S/N';

        // 1. Consultar cuántos diagnósticos tiene el registro
        fetch("{{ route('informesat1.deleteDiagnostico') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            registro_id: regId,
            diag_index: diagNum,
            check_only: 1
          })
        })
          .then(res => res.json())
          .then(resData => {
            if (!resData.success) {
              Swal.fire('Error', resData.message || 'No se pudo verificar el registro.', 'error');
              return;
            }

            const isOnly = resData.is_only_diagnosis;
            const totalDiags = resData.total_diagnosticos;

            if (isOnly) {
              // Caso especial: Es el único diagnóstico de este paciente
              Swal.fire({
                title: 'Único Diagnóstico del Registro',
                html: `El paciente <b>${pac}</b> (Exp: ${exp}) solo cuenta con este diagnóstico (<b>${diag || 'D' + diagNum}</b>).<br><br>¿Deseas eliminar solo el diagnóstico o eliminar el registro completo del paciente?`,
                icon: 'warning',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: '<i class="bi bi-trash"></i> Eliminar Registro Completo',
                denyButtonText: '<i class="bi bi-eraser"></i> Eliminar Solo Diagnóstico',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ef4444',
                denyButtonColor: '#f59e0b',
                cancelButtonColor: '#64748b'
              }).then((result) => {
                if (result.isConfirmed) {
                  // Eliminar registro completo
                  ejecutarEliminacion(regId, diagNum, true);
                } else if (result.isDenied) {
                  // Eliminar solo diagnóstico
                  ejecutarEliminacion(regId, diagNum, false);
                }
              });
            } else {
              // Caso normal: Tiene más diagnósticos
              Swal.fire({
                title: '¿Eliminar este Diagnóstico?',
                html: `Se eliminará el diagnóstico <b>${diag || 'D' + diagNum}</b> (${cod || 'Sin código'}) del paciente <b>${pac}</b>.<br><br>El registro del paciente y sus otros <b>${totalDiags - 1}</b> diagnóstico(s) permanecerán guardados.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar diagnóstico',
                cancelButtonText: 'Cancelar'
              }).then((result) => {
                if (result.isConfirmed) {
                  ejecutarEliminacion(regId, diagNum, false);
                }
              });
            }
          })
          .catch(err => {
            Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
            console.error(err);
          });
      });

      // Función auxiliar para ejecutar la eliminación física
      function ejecutarEliminacion(regId, diagNum, eliminarCompleto) {
        Swal.fire({
          title: 'Procesando...',
          text: 'Eliminando de la base de datos...',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
        });

        fetch("{{ route('informesat1.deleteDiagnostico') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            registro_id: regId,
            diag_index: diagNum,
            eliminar_registro_completo: eliminarCompleto ? 1 : 0
          })
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              if (eliminarCompleto) {
                // Eliminar todas las filas asociadas a este registro global
                window.informesExcel.deleteRecord(item => String(item.cells[28]) === String(regId));
              } else {
                // Eliminar únicamente la fila de este diagnóstico
                window.informesExcel.deleteRecord(
                  item => String(item.cells[28]) === String(regId) && String(item.cells[17]) === ('D' + diagNum)
                );
              }

              Swal.fire({
                icon: 'success',
                title: '¡Eliminado!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
              });
            } else {
              Swal.fire('Error', data.message || 'No se pudo eliminar.', 'error');
            }
          })
          .catch(err => {
            Swal.fire('Error', 'Error en el servidor al procesar la eliminación.', 'error');
            console.error(err);
          });
      }
    });
  </script>
@endpush