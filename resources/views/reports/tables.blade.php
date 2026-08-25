@extends('layouts.app')

@section('title', 'Registros AT1 & Tabla Excel')

@push('styles')
<style>
  /* Full Viewport App Content for Excel Table Mode (Always visible footer and scrollbars) */
  .app-content {
    padding: 0.75rem 1rem !important;
    height: calc(100vh - var(--navbar-height)) !important;
    max-height: calc(100vh - var(--navbar-height)) !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
  }

  .sing-card-excel-fullscreen {
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
    margin-bottom: 0 !important;
    height: 100% !important;
    max-height: 100% !important;
    overflow: hidden !important;
    border-radius: var(--radius-lg);
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
  }

  .excel-table-scroll {
    flex: 1 !important;
    height: 100% !important;
    max-height: none !important;
    min-height: 0 !important;
    overflow-x: auto !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    position: relative !important;
    border-bottom: 1px solid var(--border-color);
  }

  .table-excel-footer {
    flex-shrink: 0 !important;
    position: sticky !important;
    bottom: 0 !important;
    z-index: 20 !important;
  }

  /* Auto-expanding dynamic Multi-Selects (Años y Meses) in both Light & Dark mode */
  .select2-container--default {
    width: auto !important;
    max-width: 340px !important;
  }

  .select2-container--default .select2-selection--multiple {
    display: inline-flex !important;
    align-items: center !important;
    min-height: 32px !important;
    height: 32px !important;
    max-height: 32px !important;
    width: auto !important;
    min-width: 75px !important;
    max-width: 340px !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    padding: 0 5px !important;
    box-sizing: border-box !important;
    transition: width 0.2s ease;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__rendered {
    display: inline-flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    padding: 0 !important;
    margin: 0 !important;
    width: auto !important;
    gap: 4px !important;
    scrollbar-width: none !important;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__rendered::-webkit-scrollbar {
    display: none !important;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice {
    display: inline-flex !important;
    align-items: center !important;
    flex-shrink: 0 !important;
    margin: 0 !important;
    padding: 1px 6px !important;
    font-size: 0.78rem !important;
    line-height: 1.35 !important;
    border-radius: 4px !important;
    white-space: nowrap !important;
    gap: 3px !important;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    position: static !important;
    float: none !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    background: transparent !important;
    font-size: 0.85rem !important;
    line-height: 1 !important;
    cursor: pointer !important;
    display: inline-flex !important;
    align-items: center;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__clear {
    position: static !important;
    float: none !important;
    margin-left: 4px !important;
    margin-right: 2px !important;
    order: 99 !important;
    font-size: 0.95rem !important;
    line-height: 1 !important;
    cursor: pointer !important;
  }

  .select2-container--default .select2-selection--multiple .select2-search--inline {
    display: inline-flex !important;
    align-items: center !important;
    margin: 0 !important;
    padding: 0 !important;
    flex-shrink: 0 !important;
  }

  .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
    margin: 0 !important;
    height: 22px !important;
    min-width: 25px !important;
    font-size: 0.78rem !important;
  }

  /* Dropdown fitted to text content (no excess width) */
  .select2-dropdown {
    width: max-content !important;
    min-width: 100% !important;
    max-width: 220px !important;
    border-radius: var(--radius-sm, 4px) !important;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25) !important;
  }

  /* Dark Mode Palette for Select2 (Identical clean layout to Light Mode) */
  [data-theme="dark"] .select2-container--default .select2-selection--multiple {
    background-color: var(--input-bg, #111827) !important;
    border: 1px solid var(--input-border, #374151) !important;
  }

  [data-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #1f2937 !important;
    border: 1px solid #4b5563 !important;
    color: #f9fafb !important;
  }

  [data-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #9ca3af !important;
  }

  [data-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #ef4444 !important;
  }

  [data-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__clear {
    color: #9ca3af !important;
  }

  [data-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__clear:hover {
    color: #ef4444 !important;
  }

  [data-theme="dark"] .select2-container--default .select2-search--inline .select2-search__field {
    color: var(--input-text, #f9fafb) !important;
    background: transparent !important;
  }

  [data-theme="dark"] .select2-dropdown {
    background-color: var(--bg-surface-elevated, #1f2937) !important;
    border: 1px solid var(--border-color, #374151) !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.45) !important;
  }

  [data-theme="dark"] .select2-container--default .select2-search--dropdown {
    background-color: #111827 !important;
    padding: 4px !important;
  }

  [data-theme="dark"] .select2-container--default .select2-search--dropdown .select2-search__field {
    background-color: #1f2937 !important;
    border: 1px solid #374151 !important;
    color: #f9fafb !important;
  }

  [data-theme="dark"] .select2-container--default .select2-results__option {
    color: var(--text-primary, #f9fafb) !important;
    background-color: transparent !important;
    padding: 5px 10px !important;
  }

  [data-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: var(--color-primary, #3b82f6) !important;
    color: #ffffff !important;
  }

  /* ==========================================================================
     Top Table Toolbar Colored Buttons (Adapted to Light & Dark Theme)
     ========================================================================== */
  .btn-toolbar-consultar {
    background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
    border: 1px solid #1d4ed8 !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25) !important;
    transition: all 0.2s ease !important;
    border-radius: var(--radius-sm, 5px) !important;
  }
  .btn-toolbar-consultar:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
    border-color: #1e40af !important;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35) !important;
    transform: translateY(-1px);
    color: #ffffff !important;
  }
  [data-theme="dark"] .btn-toolbar-consultar {
    background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
    border: 1px solid #60a5fa !important;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.4) !important;
  }
  [data-theme="dark"] .btn-toolbar-consultar:hover {
    border-color: #93c5fd !important;
    box-shadow: 0 4px 14px rgba(96, 165, 250, 0.45) !important;
  }

  /* Reset Period Button (Cyan / Slate Soft) */
  .btn-toolbar-reset {
    background: #f1f5f9 !important;
    border: 1px solid #cbd5e1 !important;
    color: #475569 !important;
    border-radius: var(--radius-sm, 5px) !important;
    transition: all 0.2s ease !important;
  }
  .btn-toolbar-reset:hover {
    background: #e0f2fe !important;
    border-color: #0284c7 !important;
    color: #0284c7 !important;
    transform: translateY(-1px) rotate(-15deg);
  }
  [data-theme="dark"] .btn-toolbar-reset {
    background: #1e293b !important;
    border: 1px solid #334155 !important;
    color: #94a3b8 !important;
  }
  [data-theme="dark"] .btn-toolbar-reset:hover {
    background: #0f172a !important;
    border-color: #38bdf8 !important;
    color: #38bdf8 !important;
    box-shadow: 0 0 10px rgba(56, 189, 248, 0.25) !important;
  }

  /* Clear Filters Button (Warm Amber / Orange) */
  .btn-toolbar-clear {
    background: #fff7ed !important;
    border: 1px solid #fdba74 !important;
    color: #c2410c !important;
    font-weight: 600 !important;
    border-radius: var(--radius-sm, 5px) !important;
    transition: all 0.2s ease !important;
  }
  .btn-toolbar-clear:hover {
    background: #ffedd5 !important;
    border-color: #ea580c !important;
    color: #9a3412 !important;
    transform: translateY(-1px);
  }
  [data-theme="dark"] .btn-toolbar-clear {
    background: rgba(234, 88, 12, 0.15) !important;
    border: 1px solid rgba(251, 146, 60, 0.45) !important;
    color: #fb923c !important;
  }
  [data-theme="dark"] .btn-toolbar-clear:hover {
    background: rgba(234, 88, 12, 0.28) !important;
    border-color: #fb923c !important;
    color: #fdba74 !important;
    box-shadow: 0 0 10px rgba(251, 146, 60, 0.25) !important;
  }

  /* Add Record Button (Fresh Emerald Green) */
  .btn-toolbar-add {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    border: 1px solid #047857 !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25) !important;
    border-radius: var(--radius-sm, 5px) !important;
    transition: all 0.2s ease !important;
  }
  .btn-toolbar-add:hover {
    background: linear-gradient(135deg, #059669, #047857) !important;
    border-color: #065f46 !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35) !important;
    transform: translateY(-1px);
    color: #ffffff !important;
  }
  [data-theme="dark"] .btn-toolbar-add {
    background: linear-gradient(135deg, #059669, #047857) !important;
    border: 1px solid #34d399 !important;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35) !important;
  }
  [data-theme="dark"] .btn-toolbar-add:hover {
    border-color: #6ee7b7 !important;
    box-shadow: 0 4px 14px rgba(52, 211, 153, 0.45) !important;
  }

  /* CSV Export Button (Teal / Excel Green Soft) */
  .btn-toolbar-csv {
    background: #f0fdf4 !important;
    border: 1px solid #86efac !important;
    color: #15803d !important;
    font-weight: 600 !important;
    border-radius: var(--radius-sm, 5px) !important;
    transition: all 0.2s ease !important;
  }
  .btn-toolbar-csv:hover {
    background: #dcfce7 !important;
    border-color: #22c55e !important;
    color: #166534 !important;
    transform: translateY(-1px);
  }
  [data-theme="dark"] .btn-toolbar-csv {
    background: rgba(34, 197, 94, 0.12) !important;
    border: 1px solid rgba(74, 222, 128, 0.4) !important;
    color: #4ade80 !important;
  }
  [data-theme="dark"] .btn-toolbar-csv:hover {
    background: rgba(34, 197, 94, 0.22) !important;
    border-color: #86efac !important;
    color: #86efac !important;
    box-shadow: 0 0 10px rgba(74, 222, 128, 0.25) !important;
  }

  /* Fullscreen Button */
  .btn-toolbar-fullscreen {
    background: #f8fafc !important;
    border: 1px solid #cbd5e1 !important;
    color: #64748b !important;
    border-radius: var(--radius-sm, 5px) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s ease !important;
  }
  .btn-toolbar-fullscreen:hover {
    background: #f1f5f9 !important;
    border-color: #6366f1 !important;
    color: #6366f1 !important;
    transform: translateY(-1px);
  }
  [data-theme="dark"] .btn-toolbar-fullscreen {
    background: #1e293b !important;
    border: 1px solid #475569 !important;
    color: #94a3b8 !important;
  }
  [data-theme="dark"] .btn-toolbar-fullscreen:hover {
    background: #0f172a !important;
    border-color: #818cf8 !important;
    color: #a5b4fc !important;
    box-shadow: 0 0 10px rgba(129, 140, 248, 0.25) !important;
  }
</style>
@endpush

@section('content')
<!-- =========================================================================
     TABLA EXCEL INTERACTIVA A PANTALLA COMPLETA CON FILTROS INTEGRADAS EN HEADER
     ========================================================================= -->
<div class="sing-card-excel-fullscreen">
  <!-- Card Header con Título, Búsqueda Rápida, Filtros de Período (Años/Meses) y Botones -->
  <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.6rem; padding: 0.6rem 1.1rem; border-bottom: 1px solid var(--border-color);">
    <!-- Título de la Tabla -->
    <div style="display: flex; align-items: center; gap: 0.65rem;">
      <div style="width: 32px; height: 32px; border-radius: var(--radius-sm); background: linear-gradient(135deg, var(--color-primary), #6366f1); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1rem; box-shadow: 0 2px 8px rgba(77, 124, 254, 0.35); flex-shrink: 0;">
        <i class="bi bi-table"></i>
      </div>
      <div>
        <h2 class="card-title" style="font-size: 1rem; margin-bottom: 0; font-weight: 700; color: var(--text-primary); line-height: 1.2;">
          Registros de Atención AT1
        </h2>
      </div>
    </div>

    <!-- Todos los Filtros Integrados Arriba (Búsqueda + Años + Meses + Botones) -->
    <div style="display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap;">
      <!-- Búsqueda Rápida Integrada Arriba -->
      <div style="position: relative; width: 190px;">
        <i class="bi bi-search" style="position: absolute; left: 0.65rem; top: 0.5rem; color: var(--text-muted); font-size: 0.78rem;"></i>
        <input type="text" id="excelGlobalSearch" class="form-control form-control-sm" style="padding-left: 1.85rem; height: 32px; font-size: 0.8rem;" placeholder="Búsqueda rápida...">
      </div>

      <!-- Select2 Años (Autoexpandible) -->
      <div style="display: inline-flex; width: auto; max-width: 260px;">
        <select id="selectYear" name="years[]" class="form-control select2-filter" multiple="multiple">
          <option value="2026" selected>2026</option>
          <option value="2025">2025</option>
          <option value="2024">2024</option>
          <option value="2023">2023</option>
          <option value="2022">2022</option>
        </select>
      </div>

      <!-- Select2 Meses (Autoexpandible) -->
      <div style="display: inline-flex; width: auto; max-width: 290px;">
        <select id="selectMonth" name="months[]" class="form-control select2-filter" multiple="multiple">
          <option value="01">01 - Ene</option>
          <option value="02">02 - Feb</option>
          <option value="03">03 - Mar</option>
          <option value="04">04 - Abr</option>
          <option value="05">05 - May</option>
          <option value="06">06 - Jun</option>
          <option value="07">07 - Jul</option>
          <option value="08" selected>08 - Ago</option>
          <option value="09">09 - Sep</option>
          <option value="10">10 - Oct</option>
          <option value="11">11 - Nov</option>
          <option value="12">12 - Dic</option>
        </select>
      </div>

      <!-- Botones Consultar / Reset Período -->
      <button type="button" id="btnApplyFilter" class="btn btn-toolbar-consultar btn-sm" style="height: 32px; padding: 0 0.8rem; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem;">
        <i class="bi bi-search"></i> Consultar
      </button>
      <button type="button" id="btnResetFilter" class="btn btn-toolbar-reset btn-sm btn-icon" style="height: 32px; width: 32px;" title="Restablecer período">
        <i class="bi bi-arrow-counterclockwise"></i>
      </button>

      <button class="btn btn-toolbar-clear btn-sm" id="btnResetAllExcelFilters" title="Limpiar todos los filtros de columna" style="display: none; height: 32px; font-size: 0.78rem; padding: 0 0.65rem;">
        <i class="bi bi-funnel-fill"></i> Limpiar
      </button>

      <div style="height: 20px; width: 1px; background-color: var(--border-color); margin: 0 0.15rem;"></div>

      <!-- Botones de Acción -->
      <button class="btn btn-toolbar-add btn-sm" style="height: 32px; padding: 0 0.8rem; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem;" onclick="SingApp.toast({title: 'Nuevo Registro', message: 'Abriendo formulario de creación...', type: 'success'})">
        <i class="bi bi-plus-lg"></i> Agregar
      </button>
      <button class="btn btn-toolbar-csv btn-sm" id="btnExportExcelCSV" style="height: 32px; padding: 0 0.75rem; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem;" title="Exportar a CSV">
        <i class="bi bi-file-earmark-excel"></i> CSV
      </button>
      <button class="btn-toolbar-fullscreen" data-action="fullscreen" title="Pantalla Completa" style="height: 32px; width: 32px; cursor: pointer;"><i class="bi bi-fullscreen"></i></button>
    </div>
  </div>

  <!-- Contenedor con Scroll de la Tabla Excel (Horizontal & Vertical siempre visibles) -->
  <div class="excel-table-scroll">
    <table class="sing-table-excel" id="excelAt1Table">
      <thead>
        <tr>
          <!-- Col 0: Checkbox Selector -->
          <th style="width: 42px; text-align: center;">
            <input type="checkbox" id="excelSelectAllRows" title="Seleccionar todas las filas">
          </th>
          <!-- Col 1: Folio / ID -->
          <th data-col="1" data-title="ID Registro" data-type="text">
            <div class="excel-th-content">
              <span class="excel-th-title">ID Registro</span>
              <button class="excel-filter-btn" title="Filtrar por ID"><i class="bi bi-funnel"></i></button>
            </div>
          </th>
          <!-- Col 2: Fecha (Filtro Jerárquico Tipo Excel: Año > Mes > Día) -->
          <th data-col="2" data-title="Fecha Atención" data-type="date">
            <div class="excel-th-content">
              <span class="excel-th-title">Fecha Atención</span>
              <button class="excel-filter-btn" title="Filtrar por Fecha"><i class="bi bi-funnel"></i></button>
            </div>
          </th>
          <!-- Col 3: Paciente -->
          <th data-col="3" data-title="Paciente / Usuario" data-type="text">
            <div class="excel-th-content">
              <span class="excel-th-title">Paciente / Usuario</span>
              <button class="excel-filter-btn" title="Filtrar por Paciente"><i class="bi bi-funnel"></i></button>
            </div>
          </th>
          <!-- Col 4: Edad -->
          <th data-col="4" data-title="Edad" data-type="text">
            <div class="excel-th-content">
              <span class="excel-th-title">Edad</span>
              <button class="excel-filter-btn" title="Filtrar por Edad"><i class="bi bi-funnel"></i></button>
            </div>
          </th>
          <!-- Col 5: Sexo -->
          <th data-col="5" data-title="Sexo" data-type="text">
            <div class="excel-th-content">
              <span class="excel-th-title">Sexo</span>
              <button class="excel-filter-btn" title="Filtrar por Sexo"><i class="bi bi-funnel"></i></button>
            </div>
          </th>
          <!-- Col 6: Médico Tratante -->
          <th data-col="6" data-title="Médico Tratante" data-type="text">
            <div class="excel-th-content">
              <span class="excel-th-title">Médico Tratante</span>
              <button class="excel-filter-btn" title="Filtrar por Médico"><i class="bi bi-funnel"></i></button>
            </div>
          </th>
          <!-- Col 7: Diagnóstico -->
          <th data-col="7" data-title="Diagnóstico CIE-10" data-type="text">
            <div class="excel-th-content">
              <span class="excel-th-title">Diagnóstico CIE-10</span>
              <button class="excel-filter-btn" title="Filtrar por Diagnóstico"><i class="bi bi-funnel"></i></button>
            </div>
          </th>
          <!-- Col 8: Tipo Atención -->
          <th data-col="8" data-title="Tipo Atención" data-type="text">
            <div class="excel-th-content">
              <span class="excel-th-title">Tipo Atención</span>
              <button class="excel-filter-btn" title="Filtrar por Tipo"><i class="bi bi-funnel"></i></button>
            </div>
          </th>
          <!-- Col 9: Estado SVS -->
          <th data-col="9" data-title="Estado SVS" data-type="text">
            <div class="excel-th-content">
              <span class="excel-th-title">Estado SVS</span>
              <button class="excel-filter-btn" title="Filtrar por Estado"><i class="bi bi-funnel"></i></button>
            </div>
          </th>
          <!-- Col 10: Acciones -->
          <th style="width: 80px; text-align: center;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <!-- Fila 1 (2026 - Enero) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1001</code></td>
          <td>2026-01-10</td>
          <td><strong>Carlos Sandoval Ríos</strong></td>
          <td>35 años</td>
          <td><span class="badge badge-soft-primary">Masculino</span></td>
          <td>Dr. Carlos Mendoza</td>
          <td><code>J00</code> Rinofaringitis Aguda</td>
          <td>Consulta Externa</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 2 (2026 - Enero) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1002</code></td>
          <td>2026-01-18</td>
          <td><strong>Natalia Fuentes Bravo</strong></td>
          <td>28 años</td>
          <td><span class="badge badge-soft-info">Femenino</span></td>
          <td>Dra. Elena Ramos</td>
          <td><code>K29</code> Gastritis Aguda</td>
          <td>Urgencia</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 3 (2026 - Enero) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1003</code></td>
          <td>2026-01-25</td>
          <td><strong>Jorge Valenzuela Soto</strong></td>
          <td>64 años</td>
          <td><span class="badge badge-soft-primary">Masculino</span></td>
          <td>Dr. Andrés Ortiz</td>
          <td><code>I10</code> Hipertensión Esencial</td>
          <td>Control Crónico</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 4 (2026 - Febrero) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1004</code></td>
          <td>2026-02-12</td>
          <td><strong>Diego Pizarro Vargas</strong></td>
          <td>54 años</td>
          <td><span class="badge badge-soft-primary">Masculino</span></td>
          <td>Dr. Andrés Ortiz</td>
          <td><code>I10</code> Hipertensión Esencial</td>
          <td>Control Crónico</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 5 (2026 - Marzo) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1005</code></td>
          <td>2026-03-08</td>
          <td><strong>Camila Silva Oyarzún</strong></td>
          <td>41 años</td>
          <td><span class="badge badge-soft-info">Femenino</span></td>
          <td>Dra. Patricia Vidal</td>
          <td><code>J45</code> Asma Bronquial</td>
          <td>Control Crónico</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 6 (2026 - Abril) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1006</code></td>
          <td>2026-04-14</td>
          <td><strong>Rodrigo Araya Muñoz</strong></td>
          <td>50 años</td>
          <td><span class="badge badge-soft-primary">Masculino</span></td>
          <td>Dr. Carlos Mendoza</td>
          <td><code>E11</code> Diabetes Mellitus Tipo 2</td>
          <td>Control Crónico</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 7 (2026 - Mayo) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1007</code></td>
          <td>2026-05-20</td>
          <td><strong>Francisca Pinto Lara</strong></td>
          <td>22 años</td>
          <td><span class="badge badge-soft-info">Femenino</span></td>
          <td>Dra. Elena Ramos</td>
          <td><code>N39</code> Infección del Tracto Urinario</td>
          <td>Urgencia</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 8 (2026 - Junio) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1008</code></td>
          <td>2026-06-11</td>
          <td><strong>Manuel Castro Reyes</strong></td>
          <td>37 años</td>
          <td><span class="badge badge-soft-primary">Masculino</span></td>
          <td>Dr. Andrés Ortiz</td>
          <td><code>M54</code> Dorsalgia / Lumbalgia</td>
          <td>Consulta Externa</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 9 (2026 - Julio) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1009</code></td>
          <td>2026-07-15</td>
          <td><strong>Sofía Navarro Palma</strong></td>
          <td>29 años</td>
          <td><span class="badge badge-soft-info">Femenino</span></td>
          <td>Dra. Elena Ramos</td>
          <td><code>K21</code> Reflujo Gastroesofágico</td>
          <td>Consulta Externa</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 10 (2026 - Agosto) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1010</code></td>
          <td>2026-08-01</td>
          <td><strong>Adriana Vega Solís</strong></td>
          <td>34 años</td>
          <td><span class="badge badge-soft-info">Femenino</span></td>
          <td>Dr. Carlos Mendoza</td>
          <td><code>J00</code> Rinofaringitis Aguda</td>
          <td>Consulta Externa</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 11 (2026 - Agosto) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1011</code></td>
          <td>2026-08-02</td>
          <td><strong>Gabriel Santos Ruiz</strong></td>
          <td>45 años</td>
          <td><span class="badge badge-soft-primary">Masculino</span></td>
          <td>Dra. Elena Ramos</td>
          <td><code>I10</code> Hipertensión Esencial</td>
          <td>Control Crónico</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 12 (2026 - Agosto) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1012</code></td>
          <td>2026-08-02</td>
          <td><strong>Beatriz Morales Peña</strong></td>
          <td>28 años</td>
          <td><span class="badge badge-soft-info">Femenino</span></td>
          <td>Dr. Carlos Mendoza</td>
          <td><code>E11</code> Diabetes Mellitus Tipo 2</td>
          <td>Control Crónico</td>
          <td><span class="badge badge-soft-warning">Pendiente</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 13 (2026 - Agosto) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1013</code></td>
          <td>2026-08-03</td>
          <td><strong>Fernando Ruiz Lara</strong></td>
          <td>62 años</td>
          <td><span class="badge badge-soft-primary">Masculino</span></td>
          <td>Dr. Andrés Ortiz</td>
          <td><code>M54</code> Dorsalgia / Lumbalgia</td>
          <td>Urgencia</td>
          <td><span class="badge badge-soft-danger">En Observación</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 14 (2026 - Septiembre) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1014</code></td>
          <td>2026-09-04</td>
          <td><strong>Tomás Loyola Espina</strong></td>
          <td>48 años</td>
          <td><span class="badge badge-soft-primary">Masculino</span></td>
          <td>Dr. Carlos Mendoza</td>
          <td><code>I10</code> Hipertensión Esencial</td>
          <td>Control Crónico</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 15 (2026 - Octubre) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1015</code></td>
          <td>2026-10-19</td>
          <td><strong>Valeria Godoy Sepúlveda</strong></td>
          <td>33 años</td>
          <td><span class="badge badge-soft-info">Femenino</span></td>
          <td>Dra. Elena Ramos</td>
          <td><code>K58</code> Síndrome Intestino Irritable</td>
          <td>Consulta Externa</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 16 (2026 - Noviembre) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1016</code></td>
          <td>2026-11-22</td>
          <td><strong>Esteban Parra Cárdenas</strong></td>
          <td>60 años</td>
          <td><span class="badge badge-soft-primary">Masculino</span></td>
          <td>Dr. Andrés Ortiz</td>
          <td><code>E11</code> Diabetes Mellitus Tipo 2</td>
          <td>Control Crónico</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 17 (2026 - Diciembre) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-1017</code></td>
          <td>2026-12-05</td>
          <td><strong>Constanza Rivas Toro</strong></td>
          <td>26 años</td>
          <td><span class="badge badge-soft-info">Femenino</span></td>
          <td>Dra. Patricia Vidal</td>
          <td><code>J06</code> Infección Respiratoria Aguda</td>
          <td>Urgencia</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 18 (2025 - Enero) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-0910</code></td>
          <td>2025-01-14</td>
          <td><strong>Daniela Cáceres Luna</strong></td>
          <td>24 años</td>
          <td><span class="badge badge-soft-info">Femenino</span></td>
          <td>Dra. Elena Ramos</td>
          <td><code>N39</code> Infección del Tracto Urinario</td>
          <td>Urgencia</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 19 (2025 - Agosto) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-0985</code></td>
          <td>2025-08-10</td>
          <td><strong>Claudio Bravo Sepúlveda</strong></td>
          <td>38 años</td>
          <td><span class="badge badge-soft-primary">Masculino</span></td>
          <td>Dr. Carlos Mendoza</td>
          <td><code>J06</code> Infección Respiratoria Aguda</td>
          <td>Consulta Externa</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 20 (2024 - Agosto) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-0810</code></td>
          <td>2024-08-18</td>
          <td><strong>Gonzalo Toledo Vera</strong></td>
          <td>49 años</td>
          <td><span class="badge badge-soft-primary">Masculino</span></td>
          <td>Dr. Andrés Ortiz</td>
          <td><code>M54</code> Dorsalgia / Lumbalgia</td>
          <td>Consulta Externa</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
        <!-- Fila 21 (2023 - Agosto) -->
        <tr>
          <td style="text-align: center;"><input type="checkbox" class="excel-row-checkbox"></td>
          <td><code>#AT1-0620</code></td>
          <td>2023-08-22</td>
          <td><strong>Raúl Espinoza Garrido</strong></td>
          <td>58 años</td>
          <td><span class="badge badge-soft-primary">Masculino</span></td>
          <td>Dr. Carlos Mendoza</td>
          <td><code>I10</code> Hipertensión Esencial</td>
          <td>Control Crónico</td>
          <td><span class="badge badge-soft-success">Validado</span></td>
          <td style="text-align: center;">
            <button class="btn btn-subtle btn-sm btn-icon" title="Ver detalle"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Footer Dinámico Tipo Excel (Status Bar Pinned - Always Visible) -->
  <div class="table-excel-footer">
    <div class="table-excel-footer-left">
      <span class="excel-status-badge">
        <i class="bi bi-database text-primary"></i> <span id="excelStatTotal">18</span> Registros Totales
      </span>
      <span class="excel-status-badge">
        <i class="bi bi-filter text-info"></i> <span id="excelStatFiltered">Mostrando 10 de 18</span>
      </span>
      <span class="excel-status-badge">
        <i class="bi bi-check2-square text-success"></i> <span id="excelStatSelected">0</span> Seleccionados
      </span>
    </div>
    <div class="table-excel-footer-right">
      <span id="excelActiveFiltersText" style="font-size: 0.78rem; color: var(--text-muted);">Filtro activo: 2026 | Agosto</span>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  $(document).ready(function() {
    // 1. Initialize Top Period Select2
    $('#selectYear').select2({
      placeholder: 'Años...',
      closeOnSelect: false,
      allowClear: true,
      dropdownAutoWidth: false,
      width: 'style'
    });

    $('#selectMonth').select2({
      placeholder: 'Meses...',
      closeOnSelect: false,
      allowClear: true,
      dropdownAutoWidth: false,
      width: 'style'
    });

    const MONTH_NAMES = {
      '01': 'Enero', '02': 'Febrero', '03': 'Marzo', '04': 'Abril',
      '05': 'Mayo', '06': 'Junio', '07': 'Julio', '08': 'Agosto',
      '09': 'Septiembre', '10': 'Octubre', '11': 'Noviembre', '12': 'Diciembre'
    };

    // 2. EXCEL TABLE ENGINE (Filtros en Cascada, Select2 Período Integrado, Desglose Jerárquico de Fechas, Multi-selección, Cell Focus y Footer)
    class SingExcelTable {
      constructor(tableId) {
        this.table = document.getElementById(tableId);
        if (!this.table) return;
        this.tbody = this.table.querySelector('tbody');
        this.activeFilters = {}; // colIndex -> Set of allowed values
        this.sortState = {};     // colIndex -> 'asc' | 'desc'
        this.globalSearchQuery = '';
        this.currentOpenCol = null;
        this.selectedYears = $('#selectYear').val() || [];
        this.selectedMonths = $('#selectMonth').val() || [];

        this.initRows();
        this.initEvents();
        this.applyFilters();
      }

      initRows() {
        this.allRows = Array.from(this.tbody.querySelectorAll('tr:not(.excel-no-records-row)')).map(tr => {
          const cells = Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim());
          return { element: tr, cells: cells };
        });
      }

      initEvents() {
        const self = this;

        // Sync Top Select2 Year & Month Filters with Table Data
        const updatePeriodFilter = () => {
          self.selectedYears = ($('#selectYear').val() || []).map(String);
          self.selectedMonths = ($('#selectMonth').val() || []).map(String);
          self.applyFilters();
        };

        $('#selectYear, #selectMonth').on('change select2:select select2:unselect select2:clear', updatePeriodFilter);

        $('#btnApplyFilter').on('click', function() {
          updatePeriodFilter();
          SingApp.toast({
            title: 'Tabla AT1 Filtrada',
            message: `Años: [${self.selectedYears.length ? self.selectedYears.join(', ') : 'Todos'}] | Meses: [${self.selectedMonths.length ? self.selectedMonths.map(m => MONTH_NAMES[m] || m).join(', ') : 'Todos'}]`,
            type: 'primary'
          });
        });

        $('#btnResetFilter').on('click', function() {
          $('#selectYear').val(['2026']).trigger('change');
          $('#selectMonth').val(['08']).trigger('change');
          SingApp.toast({
            title: 'Filtros Restablecidos',
            message: 'Período actual restaurado (Agosto 2026)',
            type: 'info'
          });
        });

        // Excel Cell Click Focus Border Indicator
        this.tbody.addEventListener('click', (e) => {
          const td = e.target.closest('td');
          if (!td) return;
          // Don't focus if clicking checkbox column
          if (td.querySelector('.excel-row-checkbox') || e.target.tagName === 'BUTTON' || e.target.tagName === 'I') return;

          self.table.querySelectorAll('td.excel-cell-active').forEach(cell => {
            cell.classList.remove('excel-cell-active');
          });

          td.classList.add('excel-cell-active');
        });

        // Column Filter Trigger Buttons
        this.table.querySelectorAll('th[data-col]').forEach(th => {
          const colIndex = parseInt(th.getAttribute('data-col'), 10);
          const filterBtn = th.querySelector('.excel-filter-btn');

          if (filterBtn) {
            filterBtn.addEventListener('click', (e) => {
              e.stopPropagation();
              self.openFilterPopover(th, colIndex);
            });
          }

          // Sort on Title Click
          const titleSpan = th.querySelector('.excel-th-title');
          if (titleSpan) {
            titleSpan.addEventListener('click', () => {
              const currentDir = self.sortState[colIndex] === 'asc' ? 'desc' : 'asc';
              self.sortTable(colIndex, currentDir);
            });
          }
        });

        // Global Search Input (Integrated in Card Header)
        const globalSearch = document.getElementById('excelGlobalSearch');
        if (globalSearch) {
          globalSearch.addEventListener('input', (e) => {
            self.globalSearchQuery = e.target.value.toLowerCase().trim();
            self.applyFilters();
          });
        }

        // Row Selection (Select All & Individual)
        const selectAllCheckbox = document.getElementById('excelSelectAllRows');
        if (selectAllCheckbox) {
          selectAllCheckbox.addEventListener('change', (e) => {
            const checked = e.target.checked;
            self.allRows.forEach(row => {
              if (row.element.style.display !== 'none') {
                const cb = row.element.querySelector('.excel-row-checkbox');
                if (cb) {
                  cb.checked = checked;
                  row.element.classList.toggle('row-selected', checked);
                }
              }
            });
            self.updateFooter();
          });
        }

        this.tbody.addEventListener('change', (e) => {
          if (e.target.classList.contains('excel-row-checkbox')) {
            const tr = e.target.closest('tr');
            if (tr) {
              tr.classList.toggle('row-selected', e.target.checked);
            }
            self.updateFooter();
          }
        });

        // Reset All Filters Button
        const resetAllBtn = document.getElementById('btnResetAllExcelFilters');
        if (resetAllBtn) {
          resetAllBtn.addEventListener('click', () => {
            self.activeFilters = {};
            self.globalSearchQuery = '';
            if (globalSearch) globalSearch.value = '';
            self.table.querySelectorAll('.excel-filter-btn').forEach(btn => btn.classList.remove('has-filter'));
            self.applyFilters();
            SingApp.toast({ title: 'Filtros Limpiados', message: 'Se han restablecido todos los filtros de la tabla', type: 'info' });
          });
        }

        // Export to CSV
        const exportBtn = document.getElementById('btnExportExcelCSV');
        if (exportBtn) {
          exportBtn.addEventListener('click', () => {
            self.exportCSV();
          });
        }
      }

      // Compute distinct available values in column taking into account ALL OTHER active filters (CASCADE FILTER)
      getDistinctValuesForColumn(targetCol) {
        const valuesMap = new Map();

        this.allRows.forEach(row => {
          let passesOthers = true;
          const dateStr = row.cells[2] || '';
          if (dateStr) {
            const parts = dateStr.split('-');
            if (parts.length >= 2) {
              const rowYear = parts[0];
              const rowMonth = parts[1];
              if (this.selectedYears && this.selectedYears.length > 0 && !this.selectedYears.includes(rowYear)) {
                passesOthers = false;
              }
              if (passesOthers && this.selectedMonths && this.selectedMonths.length > 0 && !this.selectedMonths.includes(rowMonth)) {
                passesOthers = false;
              }
            }
          }

          if (passesOthers) {
            for (const [colIdxStr, allowedSet] of Object.entries(this.activeFilters)) {
              const cIdx = parseInt(colIdxStr, 10);
              if (cIdx !== targetCol) {
                const cellVal = row.cells[cIdx] || '';
                if (!allowedSet.has(cellVal)) {
                  passesOthers = false;
                  break;
                }
              }
            }
          }

          if (passesOthers && this.globalSearchQuery) {
            const rowText = row.cells.join(' ').toLowerCase();
            if (!rowText.includes(this.globalSearchQuery)) {
              passesOthers = false;
            }
          }

          if (passesOthers) {
            const val = row.cells[targetCol] || '(En blanco)';
            valuesMap.set(val, (valuesMap.get(val) || 0) + 1);
          }
        });

        return Array.from(valuesMap.entries()).sort((a, b) => a[0].localeCompare(b[0]));
      }

      // Build Excel Hierarchical Date Tree (Año > Mes > Solo número de Día)
      buildDateTreeHtml(distinctDates, currentActiveSet) {
        const tree = {};

        distinctDates.forEach(([dateStr, count]) => {
          const match = dateStr.match(/^(\d{4})-(\d{2})-(\d{2})$/);
          if (match) {
            const [, year, month, day] = match;
            if (!tree[year]) tree[year] = { count: 0, months: {} };
            if (!tree[year].months[month]) tree[year].months[month] = { count: 0, days: {} };

            tree[year].count += count;
            tree[year].months[month].count += count;
            tree[year].months[month].days[day] = { fullDate: dateStr, count: count };
          } else {
            const year = 'Otros';
            if (!tree[year]) tree[year] = { count: 0, months: { '00': { count: 0, days: {} } } };
            tree[year].count += count;
            tree[year].months['00'].count += count;
            tree[year].months['00'].days[dateStr] = { fullDate: dateStr, count: count };
          }
        });

        let treeHtml = '<div class="excel-date-tree">';
        Object.keys(tree).sort().reverse().forEach(year => {
          const yearData = tree[year];
          let monthsHtml = '';

          Object.keys(yearData.months).sort().forEach(month => {
            const monthData = yearData.months[month];
            const monthLabel = MONTH_NAMES[month] || month;
            let daysHtml = '';

            Object.keys(monthData.days).sort().forEach(day => {
              const dayData = monthData.days[day];
              const isChecked = !currentActiveSet || currentActiveSet.has(dayData.fullDate);

              daysHtml += `
                <label class="excel-tree-row excel-popover-list-item">
                  <input type="checkbox" class="excel-val-cb excel-cb-day" data-date="${dayData.fullDate}" value="${dayData.fullDate}" ${isChecked ? 'checked' : ''}>
                  <span>${parseInt(day, 10) || day}</span>
                  <span class="item-count">${dayData.count}</span>
                </label>
              `;
            });

            monthsHtml += `
              <div class="excel-tree-node excel-node-month" data-month="${month}">
                <div class="excel-tree-row excel-popover-list-item">
                  <button type="button" class="excel-tree-toggle expanded"><i class="bi bi-chevron-right"></i></button>
                  <input type="checkbox" class="excel-cb-month" data-year="${year}" data-month="${month}">
                  <span style="font-weight:600;">${monthLabel}</span>
                  <span class="item-count">${monthData.count}</span>
                </div>
                <div class="excel-tree-children">
                  ${daysHtml}
                </div>
              </div>
            `;
          });

          treeHtml += `
            <div class="excel-tree-node excel-node-year" data-year="${year}">
              <div class="excel-tree-row excel-popover-list-item">
                <button type="button" class="excel-tree-toggle expanded"><i class="bi bi-chevron-right"></i></button>
                <input type="checkbox" class="excel-cb-year" data-year="${year}">
                <span style="font-weight:700;">${year}</span>
                <span class="item-count">${yearData.count}</span>
              </div>
              <div class="excel-tree-children">
                ${monthsHtml}
              </div>
            </div>
          `;
        });
        treeHtml += '</div>';

        return treeHtml;
      }

      openFilterPopover(th, colIndex) {
        this.closeFilterPopover();
        this.currentOpenCol = colIndex;

        const colTitle = th.getAttribute('data-title') || 'Columna';
        const isDateCol = th.getAttribute('data-type') === 'date';
        const distinctValues = this.getDistinctValuesForColumn(colIndex);
        const currentActiveSet = this.activeFilters[colIndex];

        // Create Backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'excel-popover-backdrop';
        backdrop.addEventListener('click', () => this.closeFilterPopover());

        // Create Popover Element
        const popover = document.createElement('div');
        popover.className = 'excel-filter-popover';

        // Calculate Position near Header
        const rect = th.getBoundingClientRect();
        popover.style.top = (rect.bottom + window.scrollY + 4) + 'px';
        const leftPos = Math.min(rect.left + window.scrollX, window.innerWidth - 290);
        popover.style.left = Math.max(10, leftPos) + 'px';

        // Build Items HTML (Date Hierarchy vs Standard Flat List)
        let listItemsHtml = '';
        if (isDateCol) {
          listItemsHtml = this.buildDateTreeHtml(distinctValues, currentActiveSet);
        } else {
          distinctValues.forEach(([val, count]) => {
            const isChecked = !currentActiveSet || currentActiveSet.has(val);
            listItemsHtml += `
              <label class="excel-popover-list-item">
                <input type="checkbox" class="excel-val-cb" value="${val.replace(/"/g, '&quot;')}" ${isChecked ? 'checked' : ''}>
                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${val}</span>
                <span class="item-count">${count}</span>
              </label>
            `;
          });
        }

        popover.innerHTML = `
          <div class="excel-popover-header">
            <span><i class="bi bi-funnel text-primary"></i> Filtrar: ${colTitle}</span>
            <button type="button" class="btn-close-popover" style="background:transparent; border:none; color:var(--text-muted); cursor:pointer; font-size:0.9rem;">&times;</button>
          </div>
          <div class="excel-popover-actions">
            <button type="button" class="excel-popover-action-item" id="popoverSortAsc">
              <i class="bi bi-sort-alpha-down text-primary"></i> ${isDateCol ? 'Ordenar de Más Antiguo a Más Reciente' : 'Ordenar de A a Z / Menor a Mayor'}
            </button>
            <button type="button" class="excel-popover-action-item" id="popoverSortDesc">
              <i class="bi bi-sort-alpha-down-alt text-primary"></i> ${isDateCol ? 'Ordenar de Más Reciente a Más Antiguo' : 'Ordenar de Z a A / Mayor a Menor'}
            </button>
            ${currentActiveSet ? `
            <button type="button" class="excel-popover-action-item text-danger" id="popoverClearColFilter">
              <i class="bi bi-x-circle text-danger"></i> Borrar filtro de ${colTitle}
            </button>` : ''}
          </div>
          <div class="excel-popover-search">
            <input type="text" id="popoverSearchInput" placeholder="Buscar en ${colTitle}...">
          </div>
          <div class="excel-popover-list">
            <label class="excel-popover-list-item" style="font-weight: 700; border-bottom: 1px dashed var(--border-color); padding-bottom: 0.35rem;">
              <input type="checkbox" id="popoverSelectAllVals" ${!currentActiveSet || currentActiveSet.size === distinctValues.length ? 'checked' : ''}>
              <span>(Seleccionar Todo)</span>
            </label>
            <div id="popoverItemsContainer">
              ${listItemsHtml}
            </div>
          </div>
          <div class="excel-popover-footer">
            <button type="button" class="btn btn-subtle btn-sm" id="popoverBtnCancel" style="padding: 0.25rem 0.65rem; font-size: 0.8rem;">Cancelar</button>
            <button type="button" class="btn btn-primary btn-sm" id="popoverBtnApply" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">Aplicar</button>
          </div>
        `;

        document.body.appendChild(backdrop);
        document.body.appendChild(popover);

        this.currentBackdrop = backdrop;
        this.currentPopover = popover;

        // Popover Actions
        popover.querySelector('.btn-close-popover').addEventListener('click', () => this.closeFilterPopover());
        popover.querySelector('#popoverBtnCancel').addEventListener('click', () => this.closeFilterPopover());

        // Sort Asc/Desc
        popover.querySelector('#popoverSortAsc').addEventListener('click', () => {
          this.sortTable(colIndex, 'asc');
          this.closeFilterPopover();
        });
        popover.querySelector('#popoverSortDesc').addEventListener('click', () => {
          this.sortTable(colIndex, 'desc');
          this.closeFilterPopover();
        });

        // Clear filter
        const clearBtn = popover.querySelector('#popoverClearColFilter');
        if (clearBtn) {
          clearBtn.addEventListener('click', () => {
            delete this.activeFilters[colIndex];
            th.querySelector('.excel-filter-btn')?.classList.remove('has-filter');
            this.applyFilters();
            this.closeFilterPopover();
          });
        }

        // Search Input & Filtering inside Popover (Live search, Enter key support, multi-select sync)
        const searchInput = popover.querySelector('#popoverSearchInput');
        const itemsContainer = popover.querySelector('#popoverItemsContainer');
        const selectAllCb = popover.querySelector('#popoverSelectAllVals');
        const selectAllSpan = selectAllCb.parentElement.querySelector('span');

        const handleSearch = () => {
          const q = searchInput.value.toLowerCase().trim();
          let visibleCount = 0;

          if (isDateCol) {
            itemsContainer.querySelectorAll('.excel-tree-row').forEach(row => {
              const text = row.innerText.toLowerCase();
              const isMatch = !q || text.includes(q);
              row.style.display = isMatch ? '' : 'none';
              const cb = row.querySelector('.excel-val-cb');
              if (cb) {
                if (q) cb.checked = isMatch;
                if (isMatch) visibleCount++;
              }
            });
            if (typeof syncDateTreeState === 'function') syncDateTreeState();
          } else {
            itemsContainer.querySelectorAll('.excel-popover-list-item').forEach(item => {
              const cb = item.querySelector('.excel-val-cb');
              if (!cb) return;
              const text = item.innerText.toLowerCase();
              const isMatch = !q || text.includes(q);
              item.style.display = isMatch ? '' : 'none';
              if (q) {
                cb.checked = isMatch;
              }
              if (isMatch) visibleCount++;
            });
          }

          if (q) {
            if (selectAllSpan) selectAllSpan.innerText = `(Seleccionar ${visibleCount} resultado${visibleCount === 1 ? '' : 's'})`;
            if (selectAllCb) selectAllCb.checked = visibleCount > 0;
          } else {
            if (selectAllSpan) selectAllSpan.innerText = '(Seleccionar Todo)';
          }
        };

        searchInput.addEventListener('input', handleSearch);

        // Enter key in search box triggers Apply immediately
        searchInput.addEventListener('keydown', (e) => {
          if (e.key === 'Enter') {
            e.preventDefault();
            doApplyFilter();
          }
        });

        // Auto-focus search input on popover open
        setTimeout(() => searchInput.focus(), 60);

        // If Date Column: Bind Tree Toggles and Hierarchy Sync
        let syncDateTreeState = () => {};
        if (isDateCol) {
          // Tree Collapse/Expand Chevrons
          itemsContainer.querySelectorAll('.excel-tree-toggle').forEach(toggleBtn => {
            toggleBtn.addEventListener('click', (e) => {
              e.preventDefault();
              e.stopPropagation();
              toggleBtn.classList.toggle('expanded');
              const node = toggleBtn.closest('.excel-tree-node');
              const children = node.querySelector(':scope > .excel-tree-children');
              if (children) children.classList.toggle('collapsed');
            });
          });

          // Sync parent states
          syncDateTreeState = () => {
            itemsContainer.querySelectorAll('.excel-node-month').forEach(monthNode => {
              const dayCbs = Array.from(monthNode.querySelectorAll('.excel-cb-day')).filter(cb => {
                const row = cb.closest('.excel-tree-row');
                return !row || row.style.display !== 'none';
              });
              const checkedDays = dayCbs.filter(cb => cb.checked).length;
              const monthCb = monthNode.querySelector('.excel-cb-month');
              if (monthCb) {
                monthCb.checked = dayCbs.length > 0 && checkedDays === dayCbs.length;
                monthCb.indeterminate = checkedDays > 0 && checkedDays < dayCbs.length;
              }
            });

            itemsContainer.querySelectorAll('.excel-node-year').forEach(yearNode => {
              const dayCbs = Array.from(yearNode.querySelectorAll('.excel-cb-day')).filter(cb => {
                const row = cb.closest('.excel-tree-row');
                return !row || row.style.display !== 'none';
              });
              const checkedDays = dayCbs.filter(cb => cb.checked).length;
              const yearCb = yearNode.querySelector('.excel-cb-year');
              if (yearCb) {
                yearCb.checked = dayCbs.length > 0 && checkedDays === dayCbs.length;
                yearCb.indeterminate = checkedDays > 0 && checkedDays < dayCbs.length;
              }
            });

            const allDayCbs = Array.from(itemsContainer.querySelectorAll('.excel-cb-day')).filter(cb => {
              const row = cb.closest('.excel-tree-row');
              return !row || row.style.display !== 'none';
            });
            const totalChecked = allDayCbs.filter(cb => cb.checked).length;
            if (selectAllCb) {
              selectAllCb.checked = allDayCbs.length > 0 && totalChecked === allDayCbs.length;
              selectAllCb.indeterminate = totalChecked > 0 && totalChecked < allDayCbs.length;
            }
          };

          // Year CB Change
          itemsContainer.querySelectorAll('.excel-cb-year').forEach(yearCb => {
            yearCb.addEventListener('change', (e) => {
              const node = yearCb.closest('.excel-node-year');
              node.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                const row = cb.closest('.excel-tree-row');
                if (!row || row.style.display !== 'none') cb.checked = e.target.checked;
              });
              syncDateTreeState();
            });
          });

          // Month CB Change
          itemsContainer.querySelectorAll('.excel-cb-month').forEach(monthCb => {
            monthCb.addEventListener('change', (e) => {
              const node = monthCb.closest('.excel-node-month');
              node.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                const row = cb.closest('.excel-tree-row');
                if (!row || row.style.display !== 'none') cb.checked = e.target.checked;
              });
              syncDateTreeState();
            });
          });

          // Day CB Change
          itemsContainer.querySelectorAll('.excel-cb-day').forEach(dayCb => {
            dayCb.addEventListener('change', () => syncDateTreeState());
          });

          syncDateTreeState();
        }

        // Select All inside Popover
        selectAllCb.addEventListener('change', (e) => {
          const isChecked = e.target.checked;
          itemsContainer.querySelectorAll('.excel-val-cb').forEach(cb => {
            const row = cb.closest('.excel-popover-list-item') || cb.closest('.excel-tree-row');
            if (!row || row.style.display !== 'none') {
              cb.checked = isChecked;
            }
          });
          if (isDateCol) syncDateTreeState();
        });

        // Function to Apply Filter
        const doApplyFilter = () => {
          const q = searchInput.value.toLowerCase().trim();
          const selectedVals = new Set();
          let totalOptionsInPopover = 0;

          itemsContainer.querySelectorAll('.excel-val-cb').forEach(cb => {
            totalOptionsInPopover++;
            const itemContainer = cb.closest('.excel-popover-list-item') || cb.closest('.excel-tree-row');
            const isVisible = !itemContainer || itemContainer.style.display !== 'none';
            if (cb.checked && (isVisible || !q)) {
              selectedVals.add(cb.value);
            }
          });

          const filterBtn = th.querySelector('.excel-filter-btn');

          if (!q && (selectedVals.size === totalOptionsInPopover)) {
            // All items selected and no search -> remove column filter
            delete this.activeFilters[colIndex];
            filterBtn?.classList.remove('has-filter');
          } else if (selectedVals.size === 0) {
            // Unchecked everything -> matches 0 rows
            this.activeFilters[colIndex] = new Set(['__NONE_MATCHING__']);
            filterBtn?.classList.add('has-filter');
          } else {
            this.activeFilters[colIndex] = selectedVals;
            filterBtn?.classList.add('has-filter');
          }

          this.applyFilters();
          this.closeFilterPopover();
        };

        // Apply Filter Button
        popover.querySelector('#popoverBtnApply').addEventListener('click', doApplyFilter);
      }

      closeFilterPopover() {
        if (this.currentPopover) {
          this.currentPopover.remove();
          this.currentPopover = null;
        }
        if (this.currentBackdrop) {
          this.currentBackdrop.remove();
          this.currentBackdrop = null;
        }
        this.currentOpenCol = null;
      }

      applyFilters() {
        let visibleCount = 0;
        const activeFilterCols = Object.keys(this.activeFilters);
        const hasFilters = activeFilterCols.length > 0 || this.globalSearchQuery.length > 0;

        const resetAllBtn = document.getElementById('btnResetAllExcelFilters');
        if (resetAllBtn) resetAllBtn.style.display = hasFilters ? 'inline-flex' : 'none';

        this.allRows.forEach(row => {
          let visible = true;
          const dateStr = (row.cells[2] || '').trim();

          // 1. Check Top Period Filter (Años y Meses)
          if (dateStr) {
            const parts = dateStr.split('-');
            if (parts.length >= 2) {
              const rowYear = parts[0].trim();
              const rowMonth = parts[1].trim();
              if (this.selectedYears && this.selectedYears.length > 0 && !this.selectedYears.includes(rowYear)) {
                visible = false;
              }
              if (visible && this.selectedMonths && this.selectedMonths.length > 0 && !this.selectedMonths.includes(rowMonth)) {
                visible = false;
              }
            }
          }

          // 2. Check Column Cascade Filters
          if (visible) {
            for (const [colIdxStr, allowedSet] of Object.entries(this.activeFilters)) {
              const colIndex = parseInt(colIdxStr, 10);
              const cellVal = row.cells[colIndex] || '';
              if (!allowedSet.has(cellVal)) {
                visible = false;
                break;
              }
            }
          }

          // 3. Check Global Search
          if (visible && this.globalSearchQuery) {
            const rowText = row.cells.join(' ').toLowerCase();
            if (!rowText.includes(this.globalSearchQuery)) {
              visible = false;
            }
          }

          row.element.style.display = visible ? '' : 'none';
          if (visible) visibleCount++;
        });

        // Empty state row handling
        let noRecordsRow = this.tbody.querySelector('.excel-no-records-row');
        if (visibleCount === 0) {
          if (!noRecordsRow) {
            noRecordsRow = document.createElement('tr');
            noRecordsRow.className = 'excel-no-records-row';
            noRecordsRow.innerHTML = `
              <td colspan="11" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
                <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.6;"></i>
                <strong>No se encontraron registros para el período o filtros seleccionados</strong>
                <div style="font-size: 0.8rem; margin-top: 0.25rem;">Prueba seleccionando otro mes o año en la cabecera.</div>
              </td>
            `;
            this.tbody.appendChild(noRecordsRow);
          } else {
            noRecordsRow.style.display = '';
          }
        } else if (noRecordsRow) {
          noRecordsRow.style.display = 'none';
        }

        this.updateFooter(visibleCount);
      }

      sortTable(colIndex, direction) {
        this.sortState[colIndex] = direction;

        this.allRows.sort((a, b) => {
          const valA = a.cells[colIndex] || '';
          const valB = b.cells[colIndex] || '';
          const numA = parseFloat(valA.replace(/[^0-9.-]+/g, ''));
          const numB = parseFloat(valB.replace(/[^0-9.-]+/g, ''));

          let res = 0;
          if (!isNaN(numA) && !isNaN(numB)) {
            res = numA - numB;
          } else {
            res = valA.localeCompare(valB, undefined, { numeric: true, sensitivity: 'base' });
          }

          return direction === 'asc' ? res : -res;
        });

        this.allRows.forEach(row => this.tbody.appendChild(row.element));
        this.applyFilters();
      }

      updateFooter(customVisibleCount) {
        const total = this.allRows.length;
        const visible = customVisibleCount !== undefined ? customVisibleCount : this.allRows.filter(r => r.element.style.display !== 'none').length;
        const selected = this.table.querySelectorAll('tbody tr.row-selected').length;

        const totalEl = document.getElementById('excelStatTotal');
        const filteredEl = document.getElementById('excelStatFiltered');
        const selectedEl = document.getElementById('excelStatSelected');
        const activeTextEl = document.getElementById('excelActiveFiltersText');

        if (totalEl) totalEl.innerText = total;
        if (filteredEl) filteredEl.innerText = `Mostrando ${visible} de ${total}`;
        if (selectedEl) selectedEl.innerText = selected;

        if (activeTextEl) {
          const filterCount = Object.keys(this.activeFilters).length;
          const yearTxt = this.selectedYears && this.selectedYears.length ? this.selectedYears.join(', ') : 'Todos';
          const monthTxt = this.selectedMonths && this.selectedMonths.length ? this.selectedMonths.map(m => MONTH_NAMES[m] || m).join(', ') : 'Todos';

          if (filterCount > 0) {
            activeTextEl.innerHTML = `<span class="badge badge-soft-primary"><i class="bi bi-funnel-fill"></i> ${filterCount} filtro(s) | ${yearTxt} - ${monthTxt}</span>`;
          } else if (this.globalSearchQuery) {
            activeTextEl.innerHTML = `<span class="badge badge-soft-info"><i class="bi bi-search"></i> Búsqueda activa | ${yearTxt} - ${monthTxt}</span>`;
          } else {
            activeTextEl.innerText = `Período: [${yearTxt}] - [${monthTxt}]`;
          }
        }
      }

      exportCSV() {
        const headers = [];
        this.table.querySelectorAll('thead th[data-title]').forEach(th => {
          headers.push(`"${th.getAttribute('data-title')}"`);
        });

        const rowsData = [];
        this.allRows.forEach(row => {
          if (row.element.style.display !== 'none') {
            const rowValues = [];
            this.table.querySelectorAll('thead th[data-col]').forEach(th => {
              const colIdx = parseInt(th.getAttribute('data-col'), 10);
              rowValues.push(`"${(row.cells[colIdx] || '').replace(/"/g, '""')}"`);
            });
            rowsData.push(rowValues.join(','));
          }
        });

        const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + [headers.join(','), ...rowsData].join('\n');
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', `Registros_AT1_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();

        SingApp.toast({ title: 'Exportación Exitosa', message: `Descargando ${rowsData.length} registros en formato CSV`, type: 'success' });
      }
    }

    // Initialize Excel Table Instance
    window.excelAt1 = new SingExcelTable('excelAt1Table');
  });
</script>
@endpush
