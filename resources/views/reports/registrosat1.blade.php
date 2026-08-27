@extends('layouts.app')

@section('title', 'Registros AT1 & Tabla Excel')

@push('styles')
<style>
  /* ==========================================================================
     FULLSCREEN EXCEL TABLE LAYOUT & RESPONSIVE CONTAINER
     ========================================================================== */
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

  .sing-card-excel-fullscreen {
    flex: 1 1 auto !important;
    display: flex !important;
    flex-direction: column !important;
    margin-bottom: 0 !important;
    height: 100% !important;
    max-height: 100% !important;
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    overflow: hidden !important;
    border-radius: var(--radius-md, 6px);
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
  }

  .sing-card-excel-fullscreen.is-fullscreen,
  .sing-card-excel-fullscreen:fullscreen {
    position: fixed !important;
    inset: 0 !important;
    z-index: 9999 !important;
    width: 100vw !important;
    height: 100vh !important;
    max-width: 100vw !important;
    max-height: 100vh !important;
    border-radius: 0 !important;
    border: none !important;
    box-shadow: none !important;
    background: var(--bg-surface) !important;
  }

  .excel-table-scroll {
    flex: 1 1 auto !important;
    height: 100% !important;
    max-height: none !important;
    min-height: 0 !important;
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    overflow-x: auto !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch;
    position: relative !important;
    border-bottom: 1px solid var(--border-color);
    background-color: var(--bg-surface);
    transform: translateZ(0);
  }

  /* Custom Modern Scrollbars for Table */
  .excel-table-scroll::-webkit-scrollbar {
    width: 7px;
    height: 7px;
  }
  .excel-table-scroll::-webkit-scrollbar-track {
    background: var(--bg-body, #0b1120);
  }
  .excel-table-scroll::-webkit-scrollbar-thumb {
    background: var(--border-color, #1e293b);
    border-radius: 3px;
  }
  .excel-table-scroll::-webkit-scrollbar-thumb:hover {
    background: var(--color-primary, #4d7cfe);
  }

  /* ==========================================================================
     COMPACT EXCEL TABLE (PLAIN TEXT, ROW INDICATOR & CRISP GRID)
     ========================================================================== */
  .sing-table-excel {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.8rem;
    font-family: var(--font-family-base, 'Inter', system-ui, -apple-system, sans-serif);
    text-align: left;
    background-color: var(--bg-surface);
    line-height: 1.25;
  }

  .sing-table-excel thead th {
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important;
    background-color: var(--table-header-bg) !important;
    color: var(--text-primary) !important;
    font-weight: 700 !important;
    font-size: 0.74rem !important;
    letter-spacing: 0.02em !important;
    padding: 0.35rem 0.5rem !important;
    border-bottom: 2px solid var(--border-color) !important;
    border-right: 1px solid var(--border-color) !important;
    white-space: nowrap !important;
    user-select: none !important;
    text-transform: uppercase;
  }

  /* Sticky Row Number / Indicator Column */
  .sing-table-excel thead th.th-row-num {
    position: sticky !important;
    left: 0 !important;
    top: 0 !important;
    z-index: 20 !important;
    width: 44px !important;
    min-width: 44px !important;
    max-width: 44px !important;
    text-align: center !important;
    padding: 0.35rem 0.25rem !important;
    background-color: var(--table-header-bg) !important;
    border-right: 1px solid var(--border-color) !important;
  }

  .sing-table-excel tbody tr {
    content-visibility: auto;
    contain-intrinsic-size: 0 28px;
  }

  .sing-table-excel tbody td {
    padding: 0.22rem 0.48rem !important;
    border-bottom: 1px solid var(--border-color) !important;
    border-right: 1px solid var(--border-color) !important;
    color: var(--text-primary) !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    background-color: transparent !important;
    font-size: 0.8rem !important;
  }

  .sing-table-excel tbody td.excel-row-num {
    position: sticky !important;
    left: 0 !important;
    z-index: 5 !important;
    width: 44px !important;
    min-width: 44px !important;
    max-width: 44px !important;
    text-align: center !important;
    padding: 0.22rem 0.25rem !important;
    font-size: 0.76rem !important;
    font-weight: 500 !important;
    color: var(--text-muted) !important;
    background-color: var(--table-header-bg) !important;
    border-right: 1px solid var(--border-color) !important;
    user-select: none !important;
  }

  /* Shortened Width for Diagnostic Columns (1 to 7) */
  .sing-table-excel thead th.col-diag,
  .sing-table-excel tbody td.col-diag {
    width: 160px !important;
    min-width: 160px !important;
    max-width: 160px !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
  }

  /* Compact Column Widths */
  .sing-table-excel thead th.col-cod,
  .sing-table-excel tbody td.col-cod {
    width: 60px !important;
    min-width: 60px !important;
    max-width: 60px !important;
    text-align: center !important;
  }

  .sing-table-excel thead th.col-cond,
  .sing-table-excel tbody td.col-cond {
    width: 55px !important;
    min-width: 55px !important;
    max-width: 55px !important;
    text-align: center !important;
  }

  .sing-table-excel thead th.col-medico,
  .sing-table-excel tbody td.col-medico {
    width: 175px !important;
    min-width: 175px !important;
    max-width: 175px !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
  }

  .sing-table-excel thead th.col-prof,
  .sing-table-excel tbody td.col-prof {
    width: 130px !important;
    min-width: 130px !important;
    max-width: 130px !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
  }

  .sing-table-excel thead th.col-paciente,
  .sing-table-excel tbody td.col-paciente {
    width: 175px !important;
    min-width: 175px !important;
    max-width: 175px !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
  }

  .sing-table-excel tbody tr:hover td {
    background-color: var(--bg-surface-hover) !important;
  }

  .sing-table-excel tbody tr:hover td.excel-row-num {
    background-color: var(--bg-surface-hover) !important;
    color: var(--color-primary) !important;
    font-weight: 700 !important;
  }

  .sing-table-excel tbody tr.row-selected td {
    background-color: rgba(var(--color-primary-rgb, 77, 124, 254), 0.16) !important;
  }

  /* Excel Cell Focus Outline Indicator */
  .sing-table-excel tbody td.excel-cell-active {
    outline: 2px solid var(--color-primary, #4d7cfe) !important;
    outline-offset: -2px !important;
    background-color: rgba(var(--color-primary-rgb, 77, 124, 254), 0.18) !important;
    position: relative;
    z-index: 6;
  }

  /* Table Header Elements */
  .excel-th-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.35rem;
  }

  .excel-th-title {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    cursor: pointer;
    color: var(--text-primary);
    font-weight: 700;
  }

  .excel-filter-btn {
    width: 18px;
    height: 18px;
    border-radius: 3px;
    border: 1px solid transparent;
    background: transparent;
    color: var(--text-muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.65rem;
    padding: 0;
    transition: background-color var(--transition-fast), color var(--transition-fast);
  }

  .excel-filter-btn:hover {
    background-color: var(--bg-surface-hover);
    color: var(--color-primary);
    border-color: var(--border-color);
  }

  .excel-filter-btn.has-filter {
    background-color: #2563eb !important;
    color: #ffffff !important;
    border-color: #1d4ed8 !important;
    box-shadow: 0 1px 4px rgba(37, 99, 235, 0.4);
  }

  /* ==========================================================================
     EXCEL FILTER POPOVER DIALOG (FULL DUAL-THEME LIGHT & DARK STYLING)
     ========================================================================== */
  .excel-popover-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 10080 !important;
    background: transparent;
  }

  .excel-filter-popover {
    position: fixed !important;
    z-index: 10090 !important;
    width: 285px;
    background-color: var(--bg-surface-elevated, #1a233a) !important;
    border: 1px solid var(--border-color, #334155) !important;
    border-radius: var(--radius-md, 8px);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.55) !important;
    display: flex;
    flex-direction: column;
    animation: popoverFadeIn 0.15s ease-out;
    font-size: 0.82rem;
    overflow: hidden;
    color: var(--text-primary) !important;
  }

  @keyframes popoverFadeIn {
    from { opacity: 0; transform: translateY(-6px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
  }

  .excel-popover-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.55rem 0.75rem;
    background-color: var(--bg-surface) !important;
    border-bottom: 1px solid var(--border-color);
    font-weight: 700;
    font-size: 0.8rem;
    color: var(--text-primary) !important;
  }

  .excel-popover-actions {
    padding: 0.3rem 0;
    background-color: var(--bg-surface-elevated, #1a233a) !important;
    border-bottom: 1px solid var(--border-color);
  }

  .excel-popover-action-item {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    width: 100%;
    padding: 0.4rem 0.75rem;
    border: none;
    background: transparent;
    color: var(--text-primary) !important;
    font-size: 0.8rem;
    text-align: left;
    cursor: pointer;
    transition: background-color var(--transition-fast);
  }

  .excel-popover-action-item:hover {
    background-color: var(--bg-surface-hover) !important;
    color: var(--color-primary) !important;
  }

  .excel-popover-search {
    padding: 0.45rem 0.75rem;
    border-bottom: 1px solid var(--border-color);
    background-color: var(--bg-surface) !important;
  }

  .excel-popover-search input {
    width: 100%;
    padding: 0.3rem 0.55rem;
    font-size: 0.78rem;
    border-radius: var(--radius-xs, 4px);
    border: 1px solid var(--input-border);
    background-color: var(--input-bg) !important;
    color: var(--input-text) !important;
    outline: none;
  }

  .excel-popover-search input:focus {
    border-color: var(--color-primary);
  }

  .excel-popover-add-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.55rem;
    margin-top: 0.45rem;
    background: var(--bg-surface-hover);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xs, 4px);
    cursor: pointer;
    user-select: none;
    transition: all var(--transition-fast);
  }

  .excel-popover-add-wrap:hover {
    border-color: var(--color-primary);
    background: var(--bg-surface-elevated);
  }

  .excel-popover-add-wrap input[type="checkbox"] {
    width: 14px;
    height: 14px;
    margin: 0;
    flex-shrink: 0;
    accent-color: var(--color-primary);
    cursor: pointer;
  }

  .excel-popover-add-wrap span {
    font-size: 0.74rem;
    font-weight: 500;
    color: var(--text-secondary);
    line-height: 1.25;
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .excel-popover-add-wrap input[type="checkbox"]:checked ~ span {
    color: var(--text-primary);
    font-weight: 600;
  }

  .excel-popover-list {
    max-height: 185px;
    overflow-y: auto;
    padding: 0.35rem 0.55rem;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    background-color: var(--bg-surface-elevated, #1a233a) !important;
  }

  .excel-popover-list-item {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.25rem 0.4rem;
    border-radius: var(--radius-xs, 4px);
    cursor: pointer;
    font-size: 0.78rem;
    user-select: none;
    color: var(--text-primary) !important;
    transition: background-color var(--transition-fast);
  }

  .excel-popover-list-item:hover {
    background-color: var(--bg-surface-hover) !important;
  }

  .excel-popover-list-item input[type="checkbox"] {
    cursor: pointer;
    accent-color: var(--color-primary);
  }

  .excel-popover-list-item .item-count {
    margin-left: auto;
    font-size: 0.72rem;
    color: var(--text-muted);
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    padding: 0.05rem 0.35rem;
    border-radius: 10px;
  }

  .excel-popover-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.45rem;
    padding: 0.45rem 0.75rem;
    background-color: var(--bg-surface) !important;
    border-top: 1px solid var(--border-color);
  }

  /* Date Tree Popover Styles */
  .excel-date-tree {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
  }

  .excel-tree-node {
    display: flex;
    flex-direction: column;
  }

  .excel-tree-row {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.35rem;
    border-radius: 4px;
    cursor: pointer;
  }

  .excel-tree-row:hover {
    background-color: var(--bg-surface-hover) !important;
  }

  .excel-tree-toggle {
    width: 15px;
    height: 15px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--text-muted);
    font-size: 0.65rem;
    border: none;
    background: transparent;
    padding: 0;
  }

  .excel-tree-toggle.expanded i {
    transform: rotate(90deg);
  }

  .excel-tree-toggle i {
    display: inline-block;
    transition: transform var(--transition-fast);
  }

  .excel-tree-children {
    display: flex;
    flex-direction: column;
    margin-left: 1rem;
    border-left: 1px dashed var(--border-color);
    padding-left: 0.3rem;
  }

  .excel-tree-children.collapsed {
    display: none;
  }

  /* ==========================================================================
     STATUS FOOTER BAR PINNED (ALWAYS VISIBLE)
     ========================================================================== */
  .table-excel-footer {
    flex-shrink: 0 !important;
    position: sticky !important;
    bottom: 0 !important;
    z-index: 20 !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.6rem;
    padding: 0.45rem 1rem;
    background-color: var(--table-header-bg) !important;
    border-top: 1px solid var(--border-color) !important;
    font-size: 0.78rem;
    color: var(--text-secondary);
  }

  .table-excel-footer-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .table-excel-footer-right {
    display: flex;
    align-items: center;
    gap: 0.6rem;
  }

  .excel-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.15rem 0.5rem;
    border-radius: var(--radius-full);
    font-size: 0.74rem;
    font-weight: 600;
    background-color: var(--bg-surface);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
  }

  /* ==========================================================================
     TOOLBAR CONTROLS & SELECT2 (SPACIOUS, MODERN DUAL-THEME STYLING & ZERO FOUC)
     ========================================================================== */
  /* Anti-FOUC: Hide raw native select elements before Select2 JS initialization */
  select.select2-filter:not(.select2-hidden-accessible) {
    opacity: 0 !important;
    position: absolute !important;
    pointer-events: none !important;
    z-index: -1 !important;
    width: 0 !important;
    height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
  }

  .select2-container--default {
    width: auto !important;
    max-width: 320px !important;
  }

  .select2-container--default .select2-selection--multiple {
    display: inline-flex !important;
    align-items: center !important;
    min-height: 32px !important;
    height: 32px !important;
    max-height: 32px !important;
    width: auto !important;
    min-width: 80px !important;
    padding: 2px 8px !important;
    box-sizing: border-box !important;
    background-color: var(--input-bg) !important;
    border: 1px solid var(--input-border) !important;
    border-radius: var(--radius-xs, 4px) !important;
    transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
  }

  .select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: var(--color-primary) !important;
    box-shadow: 0 0 0 2px rgba(var(--color-primary-rgb, 77, 124, 254), 0.2) !important;
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
    gap: 5px !important;
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
    padding: 2px 8px !important;
    font-size: 0.78rem !important;
    font-weight: 600 !important;
    line-height: 1.25 !important;
    border-radius: 4px !important;
    white-space: nowrap !important;
    gap: 5px !important;
    background-color: var(--bg-surface-hover) !important;
    border: 1px solid var(--border-color) !important;
    color: var(--text-primary) !important;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    position: static !important;
    float: none !important;
    margin: 0 !important;
    padding: 0 !important;
    color: var(--text-muted) !important;
    font-weight: 700 !important;
    font-size: 0.85rem !important;
    line-height: 1 !important;
    border: none !important;
    background: transparent !important;
    cursor: pointer !important;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: var(--color-danger, #ef4444) !important;
  }

  .btn-toolbar-consultar {
    background: linear-gradient(135deg, var(--color-primary, #4d7cfe), #3b82f6) !important;
    border: 1px solid #2563eb !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    border-radius: var(--radius-xs, 4px) !important;
    box-shadow: 0 2px 6px rgba(77, 124, 254, 0.25) !important;
  }
  .btn-toolbar-consultar:hover {
    background: linear-gradient(135deg, #2563eb, #1e40af) !important;
    color: #ffffff !important;
  }

  .btn-toolbar-reset {
    background: var(--bg-surface-hover) !important;
    border: 1px solid var(--border-color) !important;
    color: var(--text-secondary) !important;
    border-radius: var(--radius-xs, 4px) !important;
  }
  .btn-toolbar-reset:hover {
    color: var(--color-primary) !important;
  }

  .btn-toolbar-add {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    border: 1px solid #047857 !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    border-radius: var(--radius-xs, 4px) !important;
  }

  .btn-toolbar-xlsx {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    border: 1px solid #047857 !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    border-radius: var(--radius-xs, 4px) !important;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25) !important;
    transition: all var(--transition-fast);
  }
  .btn-toolbar-xlsx:hover {
    background: linear-gradient(135deg, #059669, #047857) !important;
    color: #ffffff !important;
  }
</style>
@endpush

@section('content')
<!-- =========================================================================
     TABLA EXCEL INTERACTIVA A PANTALLA COMPLETA CON FILTROS EN HEADER
     ========================================================================= -->
<div class="sing-card-excel-fullscreen">
  <!-- Card Header con Título, Búsqueda Rápida, Filtros de Período y Acciones -->
  <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; padding: 0.45rem 0.85rem; border-bottom: 1px solid var(--border-color);">
    <!-- Título de la Tabla -->
    <div style="display: flex; align-items: center; gap: 0.55rem;">
      <div style="width: 28px; height: 28px; border-radius: var(--radius-xs, 4px); background: linear-gradient(135deg, var(--color-primary), #6366f1); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; box-shadow: 0 2px 6px rgba(77, 124, 254, 0.35); flex-shrink: 0;">
        <i class="bi bi-table"></i>
      </div>
      <div>
        <h2 class="card-title" style="font-size: 0.92rem; margin-bottom: 0; font-weight: 700; color: var(--text-primary); line-height: 1.2;">
          Registros Globales AT1
        </h2>
        <span style="font-size: 0.72rem; color: var(--text-muted);">
          Total en BD: <strong>{{ number_format($stats['total_bd'] ?? count($registros)) }}</strong>
        </span>
      </div>
    </div>

    <!-- Filtros de Período y Acciones Integradas Arriba -->
    <form id="filterPeriodForm" method="GET" action="{{ route('registrosat1') }}" style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap; margin: 0;">
      <!-- Búsqueda Rápida Integrada Arriba -->
      <div style="position: relative; width: 205px;">
        <i class="bi bi-search" style="position: absolute; left: 0.65rem; top: 0.52rem; color: var(--text-muted); font-size: 0.78rem;"></i>
        <input type="text" id="excelGlobalSearch" class="form-control form-control-sm" style="padding-left: 1.85rem; height: 32px; font-size: 0.82rem; border-radius: var(--radius-xs, 4px);" placeholder="Búsqueda rápida...">
      </div>

      <!-- Select2 Años -->
      <div style="display: inline-flex; width: auto; min-width: 110px; max-width: 260px;">
        <select id="selectYear" name="years[]" class="form-control select2-filter" multiple="multiple">
          @foreach($anos as $ano)
            <option value="{{ $ano }}" {{ in_array((string)$ano, $selectedYears ?? []) ? 'selected' : '' }}>{{ $ano }}</option>
          @endforeach
        </select>
      </div>

      <!-- Select2 Meses -->
      <div style="display: inline-flex; width: auto; min-width: 140px; max-width: 320px;">
        <select id="selectMonth" name="months[]" class="form-control select2-filter" multiple="multiple">
          @foreach($mesesDisponibles as $m)
            <option value="{{ $m }}" {{ in_array((string)$m, $selectedMonths ?? []) ? 'selected' : '' }}>{{ $m }}</option>
          @endforeach
        </select>
      </div>

      <!-- Botones Consultar / Reset Período -->
      <button type="submit" id="btnApplyFilter" class="btn btn-toolbar-consultar btn-sm" style="height: 32px; padding: 0 0.8rem; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem;">
        <i class="bi bi-search"></i> Consultar
      </button>
      <a href="{{ route('registrosat1') }}" id="btnResetFilter" class="btn btn-toolbar-reset btn-sm btn-icon" style="height: 32px; width: 32px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;" title="Restablecer período">
        <i class="bi bi-arrow-counterclockwise"></i>
      </a>

      <button type="button" class="btn btn-toolbar-clear btn-sm" id="btnResetAllExcelFilters" title="Limpiar todos los filtros de columna" style="display: none; height: 32px; font-size: 0.78rem; padding: 0 0.65rem;">
        <i class="bi bi-funnel-fill"></i> Limpiar
      </button>

      <div style="height: 20px; width: 1px; background-color: var(--border-color); margin: 0 0.15rem;"></div>

      <!-- Botones de Acción -->
      <button type="button" class="btn btn-toolbar-add btn-sm" style="height: 32px; padding: 0 0.8rem; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem;" onclick="SingApp.toast({title: 'Nuevo Registro', message: 'Abriendo formulario...', type: 'success'})">
        <i class="bi bi-plus-lg"></i> Agregar
      </button>
      <button type="button" class="btn btn-toolbar-xlsx btn-sm" id="btnExportExcelXLSX" style="height: 32px; padding: 0 0.75rem; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem;" title="Exportar a Excel (.xlsx)">
        <i class="bi bi-file-earmark-spreadsheet-fill"></i> XLSX
      </button>
      <button type="button" class="btn btn-toolbar-reset btn-sm btn-icon" data-action="fullscreen" title="Pantalla Completa" style="height: 32px; width: 32px;"><i class="bi bi-fullscreen"></i></button>
    </form>
  </div>

  <!-- Contenedor con Scroll de la Tabla Excel -->
  <div class="excel-table-scroll">
    <table class="sing-table-excel" id="excelAt1Table">
      <thead>
        <tr>
          <!-- Columna Indicadora de Fila (Excel Row Index) -->
          <th class="th-row-num"></th>

          <!-- Columnas en el orden exacto solicitado -->
          <th data-col="1" data-title="Nº" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">Nº</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="2" data-title="CM" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">CM</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="3" data-title="MEDICO" data-type="text" class="col-medico">
            <div class="excel-th-content"><span class="excel-th-title">MEDICO</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="4" data-title="PROF" data-type="text" class="col-prof">
            <div class="excel-th-content"><span class="excel-th-title">PROF</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="5" data-title="FECHA" data-type="date">
            <div class="excel-th-content"><span class="excel-th-title">FECHA</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="6" data-title="SE" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">SE</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="7" data-title="EXP" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">EXP</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="8" data-title="PACIENTE" data-type="text" class="col-paciente">
            <div class="excel-th-content"><span class="excel-th-title">PACIENTE</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="9" data-title="IDENTIDAD" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">IDENTIDAD</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="10" data-title="TELÉFONO" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">TELÉFONO</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="11" data-title="F. NACIMIENTO" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">F. NACIMIENTO</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="12" data-title="ETNIA" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">ETNIA</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="13" data-title="SEXO" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">SEXO</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="14" data-title="EDAD" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">EDAD</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="15" data-title="TIPO" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">TIPO</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="16" data-title="RANGO" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">RANGO</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="17" data-title="CONDICIÓN" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">CONDICIÓN</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="18" data-title="COD. COL" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">COD. COL</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="19" data-title="COLONIA" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">COLONIA</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="20" data-title="COD 1" data-type="text" class="col-cod">
            <div class="excel-th-content"><span class="excel-th-title">COD 1</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="21" data-title="DIAGNÓSTICO 1" data-type="text" class="col-diag">
            <div class="excel-th-content"><span class="excel-th-title">DIAGNÓSTICO 1</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="22" data-title="COND 1" data-type="text" class="col-cond">
            <div class="excel-th-content"><span class="excel-th-title">COND 1</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="23" data-title="DG" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">DG</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="24" data-title="COD 2" data-type="text" class="col-cod">
            <div class="excel-th-content"><span class="excel-th-title">COD 2</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="25" data-title="DIAGNÓSTICO 2" data-type="text" class="col-diag">
            <div class="excel-th-content"><span class="excel-th-title">DIAGNÓSTICO 2</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="26" data-title="COND 2" data-type="text" class="col-cond">
            <div class="excel-th-content"><span class="excel-th-title">COND 2</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="27" data-title="COD 3" data-type="text" class="col-cod">
            <div class="excel-th-content"><span class="excel-th-title">COD 3</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="28" data-title="DIAGNÓSTICO 3" data-type="text" class="col-diag">
            <div class="excel-th-content"><span class="excel-th-title">DIAGNÓSTICO 3</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="29" data-title="COND 3" data-type="text" class="col-cond">
            <div class="excel-th-content"><span class="excel-th-title">COND 3</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="30" data-title="COD 4" data-type="text" class="col-cod">
            <div class="excel-th-content"><span class="excel-th-title">COD 4</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="31" data-title="DIAGNÓSTICO 4" data-type="text" class="col-diag">
            <div class="excel-th-content"><span class="excel-th-title">DIAGNÓSTICO 4</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="32" data-title="COND 4" data-type="text" class="col-cond">
            <div class="excel-th-content"><span class="excel-th-title">COND 4</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="33" data-title="COD 5" data-type="text" class="col-cod">
            <div class="excel-th-content"><span class="excel-th-title">COD 5</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="34" data-title="DIAGNÓSTICO 5" data-type="text" class="col-diag">
            <div class="excel-th-content"><span class="excel-th-title">DIAGNÓSTICO 5</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="35" data-title="COND 5" data-type="text" class="col-cond">
            <div class="excel-th-content"><span class="excel-th-title">COND 5</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="36" data-title="COD 6" data-type="text" class="col-cod">
            <div class="excel-th-content"><span class="excel-th-title">COD 6</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="37" data-title="DIAGNÓSTICO 6" data-type="text" class="col-diag">
            <div class="excel-th-content"><span class="excel-th-title">DIAGNÓSTICO 6</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="38" data-title="COND 6" data-type="text" class="col-cond">
            <div class="excel-th-content"><span class="excel-th-title">COND 6</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="39" data-title="COD 7" data-type="text" class="col-cod">
            <div class="excel-th-content"><span class="excel-th-title">COD 7</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="40" data-title="DIAGNÓSTICO 7" data-type="text" class="col-diag">
            <div class="excel-th-content"><span class="excel-th-title">DIAGNÓSTICO 7</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="41" data-title="COND 7" data-type="text" class="col-cond">
            <div class="excel-th-content"><span class="excel-th-title">COND 7</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="42" data-title="REFERIDO A" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">REFERIDO A</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="43" data-title="REFERIDO DE" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">REFERIDO DE</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="44" data-title="PG / EMB" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">PG / EMB</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="45" data-title="JORNADA" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">JORNADA</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="46" data-title="SM" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">SM</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="47" data-title="AÑO" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">AÑO</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="48" data-title="MES" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">MES</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
          <th data-col="49" data-title="ID" data-type="text">
            <div class="excel-th-content"><span class="excel-th-title">ID</span><button class="excel-filter-btn" title="Filtrar"><i class="bi bi-caret-down-fill"></i></button></div>
          </th>
        </tr>
      </thead>
      <tbody id="excelTableBody">
        <!-- Renderizado Progresivo en Chunks de 200 registros via JavaScript -->
      </tbody>
    </table>
  </div>

  <!-- Footer Dinámico Tipo Excel (Status Bar Pinned - Always Visible) -->
  <div class="table-excel-footer">
    <div class="table-excel-footer-left">
      <span class="excel-status-badge">
        <i class="bi bi-database text-primary"></i> <span id="excelStatTotal">{{ count($registros) }}</span> Registros Totales
      </span>
      <span class="excel-status-badge">
        <i class="bi bi-filter text-info"></i> <span id="excelStatFiltered">Mostrando 0 de {{ count($registros) }}</span>
      </span>
      <span class="excel-status-badge" id="excelProgressiveBadge" style="display: none; border-color: rgba(77, 124, 254, 0.4); background: rgba(77, 124, 254, 0.1);">
        <i class="bi bi-arrow-down-short text-primary" style="font-size: 1rem;"></i> <span id="excelProgressiveText">Scroll para +200</span>
      </span>
      <span class="excel-status-badge" id="excelActiveFiltersBadge" style="display: none;">
        <i class="bi bi-funnel-fill text-warning"></i> <span id="excelActiveFiltersCount">0</span> Filtros de Columna
      </span>
    </div>
    <div class="table-excel-footer-right">
      <span id="excelActiveFiltersText" style="font-size: 0.76rem; color: var(--text-muted);">Período: [{{ $anoActual ?: 'Todos' }}] - [{{ $mesSeleccionado ?: 'Todos' }}]</span>
    </div>
  </div>
</div>

<!-- Dataset serializado en JSON para carga instantánea en memoria sin saturar el DOM -->
<script id="registrosDataJson" type="application/json">@json($registros)</script>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
  // Safe Storage wrapper for LocalStorage (prevents tracking prevention exception)
  const SafeStorage = {
    get(key, fallback = null) {
      try {
        const v = window.localStorage ? localStorage.getItem(key) : null;
        return v ? JSON.parse(v) : fallback;
      } catch (e) {
        return fallback;
      }
    },
    set(key, value) {
      try {
        if (window.localStorage) localStorage.setItem(key, JSON.stringify(value));
      } catch (e) {}
    },
    remove(key) {
      try {
        if (window.localStorage) localStorage.removeItem(key);
      } catch (e) {}
    }
  };

  function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  $(document).ready(function() {
    // 1. Check & Restore Saved Period Filter if landing on /registrosat1 without query parameters
    const hasQueryParams = window.location.search && window.location.search.length > 1;
    if (!hasQueryParams) {
      const savedPeriod = SafeStorage.get('sing_registrosat1_period');
      if (savedPeriod && (savedPeriod.years?.length || savedPeriod.months?.length)) {
        const currentYear = $('#selectYear').val() || [];
        const currentMonth = $('#selectMonth').val() || [];
        const sameYears = JSON.stringify(currentYear.sort()) === JSON.stringify((savedPeriod.years || []).sort());
        const sameMonths = JSON.stringify(currentMonth.sort()) === JSON.stringify((savedPeriod.months || []).sort());
        
        if (!sameYears || !sameMonths) {
          if (savedPeriod.years) $('#selectYear').val(savedPeriod.years);
          if (savedPeriod.months) $('#selectMonth').val(savedPeriod.months);
          $('#filterPeriodForm').submit();
          return;
        }
      }
    }

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

    // Save period to localStorage on form submit or change
    $('#filterPeriodForm').on('submit', function() {
      SafeStorage.set('sing_registrosat1_period', {
        years: $('#selectYear').val() || [],
        months: $('#selectMonth').val() || []
      });
    });

    $('#btnResetFilter').on('click', function() {
      SafeStorage.remove('sing_registrosat1_period');
      SafeStorage.remove('sing_registrosat1_col_filters');
      SafeStorage.remove('sing_registrosat1_search');
    });

    const MONTH_NAMES = {
      '01': 'Enero', '02': 'Febrero', '03': 'Marzo', '04': 'Abril',
      '05': 'Mayo', '06': 'Junio', '07': 'Julio', '08': 'Agosto',
      '09': 'Septiembre', '10': 'Octubre', '11': 'Noviembre', '12': 'Diciembre'
    };

    // 2. EXCEL TABLE ENGINE (Progressive Infinite Scroll in Chunks of 200, In-Memory Lightning Filtering & Sorting)
    class SingExcelTable {
      constructor(tableId) {
        this.table = document.getElementById(tableId);
        if (!this.table) return;
        this.tbody = document.getElementById('excelTableBody') || this.table.querySelector('tbody');
        this.scrollContainer = document.querySelector('.excel-table-scroll');
        
        this.CHUNK_SIZE = 200; // Carga progresiva de 200 en 200
        this.renderedCount = 0;
        this.isLoadingChunk = false;

        this.activeFilters = {}; // colIndex -> Set of allowed values
        this.sortState = {};     // colIndex -> 'asc' | 'desc'
        this.currentSortCol = null;
        this.currentSortDir = null;
        this.globalSearchQuery = '';
        this.currentOpenCol = null;

        // Cargar registros desde JSON
        this.loadRawData();

        // Restore saved column filters and search query from LocalStorage
        this.restoreSavedFilters();

        this.initEvents();
        this.applyFilters();
      }

      loadRawData() {
        const jsonEl = document.getElementById('registrosDataJson');
        let rawArray = [];
        try {
          rawArray = jsonEl ? JSON.parse(jsonEl.textContent || '[]') : [];
        } catch (e) {
          rawArray = [];
        }

        this.allRecords = rawArray.map((r, index) => {
          const sexo = r.sexo ? (r.sexo.charAt(0).toUpperCase() || r.sexo) : '';
          const cells = [
            String(index + 1), // 0: Índice
            String(r.numero ?? ''), // 1: Nº
            String(r.cm ?? ''), // 2: CM
            String(r.medico ?? ''), // 3: MEDICO
            String(r.prof ?? ''), // 4: PROF
            String(r.fecha ?? ''), // 5: FECHA
            String(r.se ?? ''), // 6: SE
            String(r.exp ?? ''), // 7: EXP
            String(r.nombre_paciente ?? ''), // 8: PACIENTE
            String(r.identidad ?? ''), // 9: IDENTIDAD
            String(r.telefono ?? ''), // 10: TELÉFONO
            String(r.fecha_nacimiento ?? ''), // 11: F. NACIMIENTO
            String(r.etnia ?? ''), // 12: ETNIA
            String(sexo ?? ''), // 13: SEXO
            String(r.edad ?? ''), // 14: EDAD
            String(r.tipo ?? ''), // 15: TIPO
            String(r.rango ?? ''), // 16: RANGO
            String(r.cond ?? ''), // 17: CONDICIÓN
            String(r.cod_col ?? ''), // 18: COD. COL
            String(r.colonia ?? ''), // 19: COLONIA
            String(r.cod_1 ?? r.cod ?? ''), // 20: COD 1
            String(r.diagnostico_1 ?? r.diagnostico ?? ''), // 21: DIAGNÓSTICO 1
            String(r.cond_1 ?? ''), // 22: COND 1
            String(r.sg ?? r.dg ?? ''), // 23: DG
            String(r.cod_2 ?? ''), // 24: COD 2
            String(r.diagnostico_2 ?? ''), // 25: DIAGNÓSTICO 2
            String(r.cond_2 ?? ''), // 26: COND 2
            String(r.cod_3 ?? ''), // 27: COD 3
            String(r.diagnostico_3 ?? ''), // 28: DIAGNÓSTICO 3
            String(r.cond_3 ?? ''), // 29: COND 3
            String(r.cod_4 ?? ''), // 30: COD 4
            String(r.diagnostico_4 ?? ''), // 31: DIAGNÓSTICO 4
            String(r.cond_4 ?? ''), // 32: COND 4
            String(r.cod_5 ?? ''), // 33: COD 5
            String(r.diagnostico_5 ?? ''), // 34: DIAGNÓSTICO 5
            String(r.cond_5 ?? ''), // 35: COND 5
            String(r.cod_6 ?? ''), // 36: COD 6
            String(r.diagnostico_6 ?? ''), // 37: DIAGNÓSTICO 6
            String(r.cond_6 ?? ''), // 38: COND 6
            String(r.cod_7 ?? ''), // 39: COD 7
            String(r.diagnostico_7 ?? ''), // 40: DIAGNÓSTICO 7
            String(r.cond_7 ?? ''), // 41: COND 7
            String(r.referido_a ?? ''), // 42: REFERIDO A
            String(r.referido_de ?? ''), // 43: REFERIDO DE
            String(r.pg_emb ?? ''), // 44: PG / EMB
            String(r.jornada ?? ''), // 45: JORNADA
            String(r.sm ?? ''), // 46: SM
            String(r.ano ?? ''), // 47: AÑO
            String(r.mes ?? ''), // 48: MES
            String(r.id ?? '') // 49: ID
          ];

          return {
            raw: r,
            cells: cells,
            searchText: cells.slice(1).join(' ').toLowerCase(),
            originalIndex: index + 1
          };
        });

        this.filteredRecords = [...this.allRecords];
      }

      restoreSavedFilters() {
        // Restore Global Search
        const savedSearch = SafeStorage.get('sing_registrosat1_search', '');
        if (savedSearch) {
          this.globalSearchQuery = savedSearch;
          const searchInput = document.getElementById('excelGlobalSearch');
          if (searchInput) searchInput.value = savedSearch;
        }

        // Restore Column Filters
        const savedColFilters = SafeStorage.get('sing_registrosat1_col_filters', {});
        for (const [colIdx, valArray] of Object.entries(savedColFilters)) {
          if (Array.isArray(valArray) && valArray.length > 0) {
            this.activeFilters[colIdx] = new Set(valArray);
            const th = this.table.querySelector(`th[data-col="${colIdx}"]`);
            if (th) th.querySelector('.excel-filter-btn')?.classList.add('has-filter');
          }
        }
      }

      saveFiltersToStorage() {
        const serialized = {};
        for (const [colIdx, set] of Object.entries(this.activeFilters)) {
          serialized[colIdx] = Array.from(set);
        }
        SafeStorage.set('sing_registrosat1_col_filters', serialized);
        SafeStorage.set('sing_registrosat1_search', this.globalSearchQuery);
      }

      buildRowHtml(item, displayIndex) {
        const c = item.cells;
        return `<tr>` +
          `<td class="excel-row-num">${displayIndex}</td>` +
          `<td>${escHtml(c[1])}</td>` +
          `<td>${escHtml(c[2])}</td>` +
          `<td class="col-medico" title="${escHtml(c[3])}">${escHtml(c[3])}</td>` +
          `<td class="col-prof" title="${escHtml(c[4])}">${escHtml(c[4])}</td>` +
          `<td>${escHtml(c[5])}</td>` +
          `<td>${escHtml(c[6])}</td>` +
          `<td>${escHtml(c[7])}</td>` +
          `<td class="col-paciente" title="${escHtml(c[8])}">${escHtml(c[8])}</td>` +
          `<td>${escHtml(c[9])}</td>` +
          `<td>${escHtml(c[10])}</td>` +
          `<td>${escHtml(c[11])}</td>` +
          `<td>${escHtml(c[12])}</td>` +
          `<td>${escHtml(c[13])}</td>` +
          `<td>${escHtml(c[14])}</td>` +
          `<td>${escHtml(c[15])}</td>` +
          `<td>${escHtml(c[16])}</td>` +
          `<td class="col-cond">${escHtml(c[17])}</td>` +
          `<td>${escHtml(c[18])}</td>` +
          `<td>${escHtml(c[19])}</td>` +
          `<td class="col-cod">${escHtml(c[20])}</td>` +
          `<td class="col-diag" title="${escHtml(c[21])}">${escHtml(c[21])}</td>` +
          `<td class="col-cond">${escHtml(c[22])}</td>` +
          `<td>${escHtml(c[23])}</td>` +
          `<td class="col-cod">${escHtml(c[24])}</td>` +
          `<td class="col-diag" title="${escHtml(c[25])}">${escHtml(c[25])}</td>` +
          `<td class="col-cond">${escHtml(c[26])}</td>` +
          `<td class="col-cod">${escHtml(c[27])}</td>` +
          `<td class="col-diag" title="${escHtml(c[28])}">${escHtml(c[28])}</td>` +
          `<td class="col-cond">${escHtml(c[29])}</td>` +
          `<td class="col-cod">${escHtml(c[30])}</td>` +
          `<td class="col-diag" title="${escHtml(c[31])}">${escHtml(c[31])}</td>` +
          `<td class="col-cond">${escHtml(c[32])}</td>` +
          `<td class="col-cod">${escHtml(c[33])}</td>` +
          `<td class="col-diag" title="${escHtml(c[34])}">${escHtml(c[34])}</td>` +
          `<td class="col-cond">${escHtml(c[35])}</td>` +
          `<td class="col-cod">${escHtml(c[36])}</td>` +
          `<td class="col-diag" title="${escHtml(c[37])}">${escHtml(c[37])}</td>` +
          `<td class="col-cond">${escHtml(c[38])}</td>` +
          `<td class="col-cod">${escHtml(c[39])}</td>` +
          `<td class="col-diag" title="${escHtml(c[40])}">${escHtml(c[40])}</td>` +
          `<td class="col-cond">${escHtml(c[41])}</td>` +
          `<td>${escHtml(c[42])}</td>` +
          `<td>${escHtml(c[43])}</td>` +
          `<td>${escHtml(c[44])}</td>` +
          `<td>${escHtml(c[45])}</td>` +
          `<td>${escHtml(c[46])}</td>` +
          `<td>${escHtml(c[47])}</td>` +
          `<td>${escHtml(c[48])}</td>` +
          `<td>${escHtml(c[49])}</td>` +
        `</tr>`;
      }

      renderNextChunk() {
        if (this.renderedCount >= this.filteredRecords.length || this.isLoadingChunk) return;
        this.isLoadingChunk = true;

        const start = this.renderedCount;
        const count = Math.min(this.CHUNK_SIZE, this.filteredRecords.length - start);
        const end = start + count;

        let chunkHtml = '';
        for (let i = start; i < end; i++) {
          chunkHtml += this.buildRowHtml(this.filteredRecords[i], i + 1);
        }

        this.tbody.insertAdjacentHTML('beforeend', chunkHtml);
        this.renderedCount = end;
        this.isLoadingChunk = false;
        this.updateFooter();
      }

      initEvents() {
        const self = this;

        // Infinite Scrolling Progresivo al desplazarse
        if (this.scrollContainer) {
          this.scrollContainer.addEventListener('scroll', () => {
            if (self.renderedCount >= self.filteredRecords.length) return;
            const distanceToBottom = self.scrollContainer.scrollHeight - self.scrollContainer.scrollTop - self.scrollContainer.clientHeight;
            if (distanceToBottom < 350) {
              self.renderNextChunk();
            }
          }, { passive: true });
        }

        // Excel Cell Click Focus Border Indicator
        this.tbody.addEventListener('click', (e) => {
          const td = e.target.closest('td');
          if (!td) return;
          if (e.target.tagName === 'BUTTON' || e.target.tagName === 'I') return;

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

        // Global Search Input
        const globalSearch = document.getElementById('excelGlobalSearch');
        if (globalSearch) {
          let searchTimer = null;
          globalSearch.addEventListener('input', (e) => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
              self.globalSearchQuery = e.target.value.toLowerCase().trim();
              self.applyFilters();
            }, 100);
          });
        }

        // Clear All Filters Button in Header
        const resetAllBtn = document.getElementById('btnResetAllExcelFilters');
        if (resetAllBtn) {
          resetAllBtn.addEventListener('click', () => {
            self.activeFilters = {};
            self.globalSearchQuery = '';
            if (globalSearch) globalSearch.value = '';
            self.table.querySelectorAll('.excel-filter-btn.has-filter').forEach(btn => {
              btn.classList.remove('has-filter');
            });
            SafeStorage.remove('sing_registrosat1_col_filters');
            SafeStorage.remove('sing_registrosat1_search');
            self.applyFilters();
            SingApp.toast({ title: 'Filtros Limpiados', message: 'Se eliminaron todos los filtros de columna y búsquedas guardadas.', type: 'info' });
          });
        }

        // XLSX Export Button
        const exportXlsxBtn = document.getElementById('btnExportExcelXLSX');
        if (exportXlsxBtn) {
          exportXlsxBtn.addEventListener('click', () => self.exportXLSX());
        }
      }

      getDistinctValuesForColumn(colIndex) {
        const counts = {};
        const query = this.globalSearchQuery;
        const activeColEntries = Object.entries(this.activeFilters).filter(([cStr]) => parseInt(cStr, 10) !== colIndex);

        this.allRecords.forEach(item => {
          if (query && !item.searchText.includes(query)) return;

          for (const [cStr, allowedSet] of activeColEntries) {
            const c = parseInt(cStr, 10);
            const val = item.cells[c] || '';
            if (!allowedSet.has(val)) return;
          }

          const val = (item.cells[colIndex] || '').trim() || '(Vacío)';
          counts[val] = (counts[val] || 0) + 1;
        });

        return Object.entries(counts).sort((a, b) => {
          const numA = parseFloat(a[0]);
          const numB = parseFloat(b[0]);
          if (!isNaN(numA) && !isNaN(numB)) return numA - numB;
          return a[0].localeCompare(b[0], undefined, { numeric: true, sensitivity: 'base' });
        });
      }

      buildDateTreeHtml(distinctValues, currentActiveSet) {
        const tree = {};

        distinctValues.forEach(([dateStr, count]) => {
          const match = dateStr.match(/^(\d{4})-(\d{2})-(\d{2})/) || dateStr.match(/^(\d{2})-(\d{2})-(\d{4})/);
          if (match) {
            let year, month, day;
            if (dateStr.indexOf('-') === 4) {
              [, year, month, day] = match;
            } else {
              [, day, month, year] = match;
            }
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
        const activeTheme = document.documentElement.getAttribute('data-theme') || 'dark';

        // Create Backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'excel-popover-backdrop';
        backdrop.addEventListener('click', () => this.closeFilterPopover());

        // Create Popover Element
        const popover = document.createElement('div');
        popover.className = 'excel-filter-popover';
        popover.setAttribute('data-theme', activeTheme);

        // Calculate Position near Header using viewport coordinates (position: fixed)
        const rect = th.getBoundingClientRect();
        popover.style.position = 'fixed';
        popover.style.top = (rect.bottom + 2) + 'px';
        const leftPos = Math.min(rect.left, window.innerWidth - 290);
        popover.style.left = Math.max(10, leftPos) + 'px';

        // Build Items HTML
        let listItemsHtml = '';
        if (isDateCol) {
          listItemsHtml = this.buildDateTreeHtml(distinctValues, currentActiveSet);
        } else {
          distinctValues.forEach(([val, count]) => {
            const isChecked = !currentActiveSet || currentActiveSet.has(val);
            listItemsHtml += `
              <label class="excel-popover-list-item">
                <input type="checkbox" class="excel-val-cb" value="${val.replace(/"/g, '&quot;')}" ${isChecked ? 'checked' : ''}>
                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${escHtml(val)}</span>
                <span class="item-count">${count}</span>
              </label>
            `;
          });
        }

        popover.innerHTML = `
          <div class="excel-popover-header">
            <span><i class="bi bi-funnel text-primary"></i> Filtrar: ${escHtml(colTitle)}</span>
            <button type="button" class="btn-close-popover" style="background:transparent; border:none; color:var(--text-muted); cursor:pointer; font-size:1.1rem; line-height:1;">&times;</button>
          </div>
          <div class="excel-popover-actions">
            <button type="button" class="excel-popover-action-item" id="popoverSortAsc">
              <i class="bi bi-sort-alpha-down text-primary"></i> ${isDateCol ? 'Ordenar Antiguo a Reciente' : 'Ordenar A a Z / Menor a Mayor'}
            </button>
            <button type="button" class="excel-popover-action-item" id="popoverSortDesc">
              <i class="bi bi-sort-alpha-down-alt text-primary"></i> ${isDateCol ? 'Ordenar Reciente a Antiguo' : 'Ordenar Z a A / Mayor a Menor'}
            </button>
            ${currentActiveSet ? `
            <button type="button" class="excel-popover-action-item text-danger" id="popoverClearColFilter">
              <i class="bi bi-x-circle text-danger"></i> Borrar filtro de ${escHtml(colTitle)}
            </button>` : ''}
          </div>
          <div class="excel-popover-search">
            <input type="text" id="popoverSearchInput" placeholder="Buscar en ${escHtml(colTitle)}...">
            <label id="popoverAddToFilterWrap" class="excel-popover-add-wrap" style="display: ${currentActiveSet ? 'flex' : 'none'};">
              <input type="checkbox" id="popoverAddToFilterCb" ${currentActiveSet ? 'checked' : ''}>
              <span>Agregar selección al filtro actual</span>
            </label>
          </div>
          <div class="excel-popover-list">
            <label class="excel-popover-list-item" style="font-weight: 700; border-bottom: 1px dashed var(--border-color); padding-bottom: 0.3rem;">
              <input type="checkbox" id="popoverSelectAllVals" ${!currentActiveSet || currentActiveSet.size === distinctValues.length ? 'checked' : ''}>
              <span>(Seleccionar Todo)</span>
            </label>
            <div id="popoverItemsContainer">
              ${listItemsHtml}
            </div>
          </div>
          <div class="excel-popover-footer">
            <button type="button" class="btn btn-subtle btn-sm" id="popoverBtnCancel" style="padding: 0.2rem 0.6rem; font-size: 0.78rem;">Cancelar</button>
            <button type="button" class="btn btn-primary btn-sm" id="popoverBtnApply" style="padding: 0.2rem 0.7rem; font-size: 0.78rem;">Aplicar</button>
          </div>
        `;

        const activeContainer = this.table.closest('.sing-card-excel-fullscreen') || document.body;
        activeContainer.appendChild(backdrop);
        activeContainer.appendChild(popover);

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

        // Live Search within Popover Values
        const searchInput = popover.querySelector('#popoverSearchInput');
        const selectAllCb = popover.querySelector('#popoverSelectAllVals');
        const addToFilterWrap = popover.querySelector('#popoverAddToFilterWrap');

        setTimeout(() => {
          if (searchInput) {
            searchInput.focus();
            searchInput.select();
          }
        }, 50);

        searchInput.addEventListener('input', (e) => {
          const q = e.target.value.toLowerCase().trim();
          let matchingCount = 0;

          if (addToFilterWrap) {
            addToFilterWrap.style.display = currentActiveSet ? 'flex' : 'none';
          }

          popover.querySelectorAll('.excel-popover-list-item').forEach(item => {
            if (item.querySelector('#popoverSelectAllVals')) return;
            const text = item.innerText.toLowerCase();
            const matches = text.includes(q);
            item.style.display = matches ? 'flex' : 'none';
            if (matches) {
              matchingCount++;
              if (q.length > 0) {
                const cb = item.querySelector('.excel-val-cb');
                if (cb) cb.checked = true;
              }
            }
          });

          if (selectAllCb) {
            selectAllCb.checked = matchingCount > 0;
            const span = selectAllCb.nextElementSibling;
            if (span) {
              span.textContent = q.length > 0 ? `(Seleccionar ${matchingCount} coincidencias)` : '(Seleccionar Todo)';
            }
          }
        });

        // (Select All) Checkbox
        selectAllCb.addEventListener('change', (e) => {
          const isChecked = e.target.checked;
          popover.querySelectorAll('.excel-val-cb').forEach(cb => {
            const item = cb.closest('.excel-popover-list-item');
            if (item && item.style.display !== 'none') {
              cb.checked = isChecked;
            }
          });
        });

        // Date Tree Toggles
        popover.querySelectorAll('.excel-tree-toggle').forEach(toggleBtn => {
          toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleBtn.classList.toggle('expanded');
            const node = toggleBtn.closest('.excel-tree-node');
            const children = node.querySelector('.excel-tree-children');
            if (children) children.classList.toggle('collapsed');
          });
        });

        // Date Hierarchy Checkbox Bubbling
        popover.querySelectorAll('.excel-cb-year').forEach(yearCb => {
          yearCb.addEventListener('change', (e) => {
            const node = yearCb.closest('.excel-tree-node');
            node.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = e.target.checked);
          });
        });

        popover.querySelectorAll('.excel-cb-month').forEach(monthCb => {
          monthCb.addEventListener('change', (e) => {
            const node = monthCb.closest('.excel-tree-node');
            node.querySelectorAll('.excel-cb-day').forEach(cb => cb.checked = e.target.checked);
          });
        });

        // Unified Apply Filter Function (Supports Excel-style additive filtering)
        const applyFilterAction = () => {
          const isSearching = searchInput && searchInput.value.trim().length > 0;
          const addToFilterCb = popover.querySelector('#popoverAddToFilterCb');
          const isAddMode = addToFilterCb && addToFilterCb.checked && currentActiveSet;

          let selectedVals = new Set();

          if (isSearching) {
            popover.querySelectorAll('.excel-popover-list-item').forEach(item => {
              if (item.style.display !== 'none') {
                const cb = item.querySelector('.excel-val-cb:checked');
                if (cb) selectedVals.add(cb.value);
              }
            });

            // Merge with existing filter set if 'Agregar la selección actual al filtro' is enabled
            if (isAddMode) {
              selectedVals = new Set([...currentActiveSet, ...selectedVals]);
            }
          } else {
            popover.querySelectorAll('.excel-popover-list-item').forEach(item => {
              const cb = item.querySelector('.excel-val-cb:checked');
              if (cb) selectedVals.add(cb.value);
            });
          }

          if (selectedVals.size >= distinctValues.length || selectedVals.size === 0) {
            delete this.activeFilters[colIndex];
            th.querySelector('.excel-filter-btn')?.classList.remove('has-filter');
          } else {
            this.activeFilters[colIndex] = selectedVals;
            th.querySelector('.excel-filter-btn')?.classList.add('has-filter');
          }

          this.saveFiltersToStorage();
          this.applyFilters();
          this.closeFilterPopover();
        };

        popover.querySelector('#popoverBtnApply').addEventListener('click', applyFilterAction);

        searchInput.addEventListener('keydown', (e) => {
          if (e.key === 'Enter') {
            e.preventDefault();
            applyFilterAction();
          } else if (e.key === 'Escape') {
            e.preventDefault();
            this.closeFilterPopover();
          }
        });

        popover.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') {
            e.preventDefault();
            this.closeFilterPopover();
          }
        });
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

      sortTable(colIndex, direction) {
        this.sortState = { [colIndex]: direction };
        this.currentSortCol = colIndex;
        this.currentSortDir = direction;

        this.sortFilteredRecords(colIndex, direction, true);
      }

      sortFilteredRecords(colIndex, direction, rerender = true) {
        this.filteredRecords.sort((a, b) => {
          let valA = (a.cells[colIndex] || '').trim();
          let valB = (b.cells[colIndex] || '').trim();

          const numA = parseFloat(valA);
          const numB = parseFloat(valB);

          if (!isNaN(numA) && !isNaN(numB)) {
            return direction === 'asc' ? numA - numB : numB - numA;
          }

          return direction === 'asc' 
            ? valA.localeCompare(valB, undefined, { numeric: true, sensitivity: 'base' })
            : valB.localeCompare(valA, undefined, { numeric: true, sensitivity: 'base' });
        });

        if (rerender) {
          this.tbody.innerHTML = '';
          this.renderedCount = 0;
          if (this.scrollContainer) this.scrollContainer.scrollTop = 0;
          this.renderNextChunk();
        }
      }

      applyFilters() {
        const activeColEntries = Object.entries(this.activeFilters);
        const hasColFilters = activeColEntries.length > 0;
        const query = this.globalSearchQuery;

        this.filteredRecords = this.allRecords.filter(item => {
          // 1. Global Search
          if (query && !item.searchText.includes(query)) {
            return false;
          }

          // 2. Column Specific Filters
          if (hasColFilters) {
            for (const [colIdxStr, allowedSet] of activeColEntries) {
              const colIndex = parseInt(colIdxStr, 10);
              const cellVal = item.cells[colIndex] || '';
              if (!allowedSet.has(cellVal)) {
                return false;
              }
            }
          }

          return true;
        });

        // Re-apply sort if active
        if (this.currentSortCol !== null && this.currentSortDir) {
          this.sortFilteredRecords(this.currentSortCol, this.currentSortDir, false);
        }

        // Reset DOM and render first 200 items
        this.tbody.innerHTML = '';
        this.renderedCount = 0;
        if (this.scrollContainer) this.scrollContainer.scrollTop = 0;

        if (this.filteredRecords.length === 0) {
          this.tbody.innerHTML = `
            <tr class="excel-no-records-row">
              <td colspan="50" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
                <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.45rem; opacity: 0.6;"></i>
                <strong>No se encontraron registros que coincidan con los filtros</strong>
                <div style="font-size: 0.78rem; margin-top: 0.25rem;">Prueba ajustando los filtros de columna o limpiando la búsqueda.</div>
              </td>
            </tr>
          `;
          this.updateFooter();
        } else {
          this.renderNextChunk();
        }

        this.saveFiltersToStorage();
      }

      updateFooter() {
        const totalCount = this.allRecords.length;
        const filteredCount = this.filteredRecords.length;
        const rendered = this.renderedCount;

        const totalStatEl = document.getElementById('excelStatTotal');
        const filteredStatEl = document.getElementById('excelStatFiltered');
        const progressiveBadge = document.getElementById('excelProgressiveBadge');
        const progressiveText = document.getElementById('excelProgressiveText');
        const activeFiltersBadge = document.getElementById('excelActiveFiltersBadge');
        const activeFiltersCount = document.getElementById('excelActiveFiltersCount');
        const resetAllBtn = document.getElementById('btnResetAllExcelFilters');

        if (totalStatEl) totalStatEl.textContent = totalCount;
        
        if (filteredStatEl) {
          if (rendered < filteredCount) {
            filteredStatEl.textContent = `Mostrando ${rendered} de ${filteredCount}`;
            if (progressiveBadge) progressiveBadge.style.display = 'inline-flex';
            if (progressiveText) progressiveText.textContent = `Scroll para cargar +${Math.min(this.CHUNK_SIZE, filteredCount - rendered)}`;
          } else {
            filteredStatEl.textContent = `Mostrando todos (${filteredCount} de ${totalCount})`;
            if (progressiveBadge) progressiveBadge.style.display = 'none';
          }
        }

        const filterCount = Object.keys(this.activeFilters).length + (this.globalSearchQuery ? 1 : 0);
        if (filterCount > 0) {
          if (activeFiltersBadge) activeFiltersBadge.style.display = 'inline-flex';
          if (activeFiltersCount) activeFiltersCount.textContent = filterCount;
          if (resetAllBtn) resetAllBtn.style.display = 'inline-flex';
        } else {
          if (activeFiltersBadge) activeFiltersBadge.style.display = 'none';
          if (resetAllBtn) resetAllBtn.style.display = 'none';
        }
      }

      exportXLSX() {
        if (this.filteredRecords.length === 0) {
          SingApp.toast({ title: 'Exportación', message: 'No hay filas coincidentes para exportar.', type: 'warning' });
          return;
        }

        if (typeof XLSX === 'undefined') {
          SingApp.toast({ title: 'Cargando', message: 'Cargando módulo de exportación Excel...', type: 'info' });
          return;
        }

        // Get headers (excluding row number column)
        const headers = Array.from(this.table.querySelectorAll('thead th[data-title]')).map(th => th.getAttribute('data-title') || '');

        const aoa = [headers];

        this.filteredRecords.forEach(item => {
          // Exclude column 0 (row number indicator)
          const rowData = item.cells.slice(1).map(val => {
            const trimmed = String(val).trim();
            const num = Number(trimmed);
            // Numeric detection (exclude ID/phone strings starting with 0)
            if (trimmed !== '' && !isNaN(num) && !trimmed.startsWith('0') && trimmed.length < 10) {
              return num;
            }
            return trimmed;
          });
          aoa.push(rowData);
        });

        // Create worksheet
        const ws = XLSX.utils.aoa_to_sheet(aoa);

        // Auto calculate column widths
        const colWidths = headers.map((h, colIdx) => {
          let maxLen = h.length;
          const sampleLimit = Math.min(aoa.length, 120);
          for (let r = 0; r < sampleLimit; r++) {
            const cellVal = aoa[r][colIdx] !== undefined ? String(aoa[r][colIdx]) : '';
            if (cellVal.length > maxLen) {
              maxLen = Math.min(cellVal.length, 45);
            }
          }
          return { wch: Math.max(maxLen + 3, 7) };
        });
        ws['!cols'] = colWidths;

        // Create workbook and write file
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Registros AT1");

        const fileName = `registros_at1_${new Date().toISOString().slice(0, 10)}.xlsx`;
        XLSX.writeFile(wb, fileName);

        SingApp.toast({
          title: 'Exportación Exitosa',
          message: `Se descargó la tabla Excel con ${this.filteredRecords.length} registros (${fileName}).`,
          type: 'success'
        });
      }
    }

    // Initialize Excel Table Engine
    window.excelAt1 = new SingExcelTable('excelAt1Table');
  });
</script>
@endpush
