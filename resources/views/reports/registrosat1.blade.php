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

  /* Actions Column & Sticky Cell */
  .sing-table-excel thead th.th-actions,
  .sing-table-excel tbody td.excel-actions-cell {
    position: sticky !important;
    right: 0 !important;
    z-index: 15 !important;
    background-color: var(--table-header-bg) !important;
    border-left: 1px solid var(--border-color) !important;
    text-align: center !important;
    padding: 0.2rem 0.35rem !important;
    width: 96px !important;
    min-width: 96px !important;
    max-width: 96px !important;
  }
  .sing-table-excel tbody td.excel-actions-cell {
    background-color: var(--bg-surface) !important;
  }
  .sing-table-excel tbody tr:hover td.excel-actions-cell {
    background-color: var(--bg-surface-hover) !important;
  }
  .sing-table-excel tbody tr.row-selected td.excel-actions-cell {
    background-color: rgba(var(--color-primary-rgb, 77, 124, 254), 0.16) !important;
  }

  .btn-action-icon {
    width: 24px;
    height: 24px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    border: none;
    background: transparent;
    transition: all 0.15s ease;
    cursor: pointer;
  }
  .btn-action-icon:hover {
    transform: scale(1.15);
  }
  .btn-action-icon.text-info:hover {
    background-color: rgba(14, 165, 233, 0.18);
    color: #38bdf8 !important;
  }
  .btn-action-icon.text-primary:hover {
    background-color: rgba(77, 124, 254, 0.18);
    color: #60a5fa !important;
  }
  .btn-action-icon.text-danger:hover {
    background-color: rgba(239, 68, 68, 0.18);
    color: #f87171 !important;
  }

  /* Multiselect Checkbox styling */
  .row-select-checkbox, .select-all-checkbox {
    width: 14px;
    height: 14px;
    cursor: pointer;
    margin: 0;
    accent-color: var(--color-primary, #3b82f6);
  }
  .excel-row-num-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
  }
  .multiselect-active .row-select-checkbox,
  .multiselect-active .select-all-checkbox {
    display: inline-block !important;
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

      <!-- Botones de Acción y Multiselección -->
      <button type="button" class="btn btn-sm btn-subtle text-danger" id="btnToggleMultiSelect" style="height: 32px; padding: 0 0.75rem; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid var(--border-color);" title="Activar/Desactivar selección múltiple para eliminar filas">
        <i class="bi bi-check2-square"></i> <span id="btnToggleMultiSelectText">Multiselección</span>
      </button>

      <button type="button" class="btn btn-danger btn-sm" id="btnDeleteSelected" style="display: none; height: 32px; padding: 0 0.85rem; font-size: 0.8rem; font-weight: 700; align-items: center; gap: 0.35rem; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);">
        <i class="bi bi-trash3-fill"></i> Eliminar (<span id="selectedCountBadge">0</span>)
      </button>

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
          <!-- Columna Indicadora de Fila (Excel Row Index) & Multiselección -->
          <th class="th-row-num text-center" style="position: sticky; left: 0; z-index: 20; width: 48px; min-width: 48px;">
            <input type="checkbox" id="selectAllRows" class="select-all-checkbox form-check-input" title="Seleccionar todos los visibles" style="display: none; cursor: pointer; margin: 0 auto;">
            <span class="row-num-header-label">#</span>
          </th>

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

          <!-- Columna Fija de Acciones (CRUD) -->
          <th class="th-actions text-center" style="position: sticky; right: 0; z-index: 20; min-width: 96px; width: 96px; background-color: var(--table-header-bg); border-left: 1px solid var(--border-color); color: var(--text-primary); font-weight: 700; font-size: 0.74rem;">ACCIONES</th>
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

<!-- =========================================================================
     MODAL 1: VER DETALLE DE ATENCIÓN MÉDICA (CRUD DETALLE)
     ========================================================================= -->
<div class="modal fade" id="modalVerRegistro" tabindex="-1" aria-labelledby="modalVerRegistroLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 780px;">
    <div class="modal-content" style="background: var(--bg-surface, #1e293b); color: var(--text-primary, #f8fafc); border: 1px solid var(--border-color, #334155); border-radius: var(--radius-md, 8px); box-shadow: 0 16px 40px rgba(0,0,0,0.5);">
      <div class="modal-header" style="border-bottom: 1px solid var(--border-color, #334155); padding: 0.85rem 1.25rem;">
        <div class="d-flex align-items-center gap-2">
          <div style="width: 32px; height: 32px; border-radius: 6px; background: rgba(14, 165, 233, 0.15); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 1.05rem;">
            <i class="bi bi-person-lines-fill"></i>
          </div>
          <div>
            <h5 class="modal-title font-weight-bold mb-0" id="modalVerRegistroLabel" style="font-size: 0.95rem; color: var(--text-primary);">Detalle de Atención Médica AT-1</h5>
            <small id="verModalSubTitle" style="color: var(--text-muted); font-size: 0.74rem;">Información clínica y registro de consulta</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body p-3" style="max-height: 75vh; overflow-y: auto;">
        <!-- Header resumen -->
        <div class="p-3 mb-3" style="background: var(--bg-surface-hover, #0f172a); border-radius: 6px; border: 1px solid var(--border-color, #334155);">
          <div class="row g-2">
            <div class="col-md-6">
              <span style="font-size: 0.73rem; color: var(--text-muted); display: block;">Paciente</span>
              <strong id="ver_paciente_nombre" style="font-size: 0.95rem; color: var(--text-primary);">--</strong>
            </div>
            <div class="col-md-3 col-6">
              <span style="font-size: 0.73rem; color: var(--text-muted); display: block;">Expediente</span>
              <span id="ver_exp" style="font-family: monospace; font-weight: 700; color: var(--color-primary, #38bdf8);">--</span>
            </div>
            <div class="col-md-3 col-6">
              <span style="font-size: 0.73rem; color: var(--text-muted); display: block;">Fecha Atención</span>
              <span id="ver_fecha" style="font-weight: 600; color: var(--text-primary);">--</span>
            </div>
          </div>
        </div>

        <!-- Grid de Datos del Paciente y Consulta -->
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div class="p-2.5 h-100" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.8rem; padding: 0.75rem;">
              <h6 class="font-weight-bold mb-2 pb-1 border-bottom" style="font-size: 0.8rem; color: #38bdf8;"><i class="bi bi-person mr-1"></i> Datos Personales</h6>
              <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Identidad:</span> <strong id="ver_identidad">--</strong></div>
              <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Teléfono:</span> <span id="ver_telefono">--</span></div>
              <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">F. Nacimiento:</span> <span id="ver_fecha_nacimiento">--</span></div>
              <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Sexo / Edad:</span> <span id="ver_sexo_edad">--</span></div>
              <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Etnia:</span> <span id="ver_etnia">--</span></div>
              <div class="d-flex justify-content-between py-1"><span class="text-muted">Colonia / Procedencia:</span> <span id="ver_colonia">--</span></div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-2.5 h-100" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.8rem; padding: 0.75rem;">
              <h6 class="font-weight-bold mb-2 pb-1 border-bottom" style="font-size: 0.8rem; color: #38bdf8;"><i class="bi bi-hospital mr-1"></i> Datos de Consulta</h6>
              <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Médico Tratante:</span> <strong id="ver_medico">--</strong></div>
              <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Profesión:</span> <span id="ver_prof">--</span></div>
              <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Jornada:</span> <span id="ver_jornada" class="badge badge-subtle-primary">--</span></div>
              <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Semana Epidemiológica (SE):</span> <span id="ver_se">--</span></div>
              <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Condición General:</span> <span id="ver_cond">--</span></div>
              <div class="d-flex justify-content-between py-1"><span class="text-muted">Referencia (A / De):</span> <span id="ver_referencia">--</span></div>
            </div>
          </div>
        </div>

        <!-- Diagnósticos -->
        <div class="p-2.5" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 6px; padding: 0.75rem;">
          <h6 class="font-weight-bold mb-2 pb-1 border-bottom" style="font-size: 0.8rem; color: #38bdf8;"><i class="bi bi-clipboard2-pulse mr-1"></i> Diagnósticos Registrados</h6>
          <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0" style="font-size: 0.78rem;">
              <thead style="background: var(--bg-surface-hover); color: var(--text-muted);">
                <tr>
                  <th class="text-center" style="width: 45px;">N°</th>
                  <th style="width: 90px;">Código</th>
                  <th>Diagnóstico / Patología</th>
                  <th class="text-center" style="width: 75px;">Condición</th>
                </tr>
              </thead>
              <tbody id="verDiagnosticosTableBody">
                <!-- Llenado dinámico -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="border-top: 1px solid var(--border-color, #334155); padding: 0.65rem 1.25rem;">
        <button type="button" class="btn btn-sm btn-subtle" data-dismiss="modal" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-sm btn-primary" id="btnEditarDesdeVer" style="display: inline-flex; align-items: center; gap: 0.35rem;">
          <i class="bi bi-pencil-square"></i> Editar este Registro
        </button>
      </div>
    </div>
  </div>
</div>

<!-- =========================================================================
     MODAL 2: EDITAR REGISTRO GLOBAL COMPLETO Y PACIENTE (CRUD POPUP)
     ========================================================================= -->
<div class="modal fade" id="modalEditarRegistroCompleto" tabindex="-1" aria-labelledby="modalEditarRegistroCompletoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 960px;">
    <div class="modal-content" style="background: var(--bg-surface, #1e293b); color: var(--text-primary, #f8fafc); border: 1px solid var(--border-color, #334155); border-radius: var(--radius-md, 8px); box-shadow: 0 16px 45px rgba(0,0,0,0.55);">
      <div class="modal-header" style="border-bottom: 1px solid var(--border-color, #334155); padding: 0.85rem 1.25rem;">
        <div class="d-flex align-items-center gap-2">
          <div style="width: 32px; height: 32px; border-radius: 6px; background: rgba(77, 124, 254, 0.15); color: var(--color-primary, #38bdf8); display: flex; align-items: center; justify-content: center; font-size: 1.05rem;">
            <i class="bi bi-pencil-square"></i>
          </div>
          <div>
            <h5 class="modal-title font-weight-bold mb-0" id="modalEditarRegistroCompletoLabel" style="font-size: 0.95rem; color: var(--text-primary);">Editar Registro Médico & Paciente AT-1</h5>
            <small style="color: var(--text-muted); font-size: 0.74rem;">Modificar datos demográficos, atención médica y diagnósticos</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <form id="formEditarRegistroCompleto">
        @csrf
        <input type="hidden" id="edit_reg_id" name="id">

        <div class="modal-body p-3" style="max-height: 75vh; overflow-y: auto;">
          <!-- Sección 1: Datos del Paciente -->
          <div class="card mb-3" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 6px;">
            <div class="card-header py-2 px-3" style="background: var(--bg-surface-hover); border-bottom: 1px solid var(--border-color);">
              <span class="font-weight-bold" style="font-size: 0.82rem; color: #38bdf8;"><i class="bi bi-person-vcard mr-1"></i> 1. Información del Paciente</span>
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <div class="col-md-3 col-6">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Expediente</label>
                  <input type="text" id="edit_reg_exp" name="exp" class="form-control form-control-sm" placeholder="Ej: 1234 / CHTA50">
                </div>
                <div class="col-md-3 col-6">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Identidad (DNI)</label>
                  <input type="text" id="edit_reg_identidad" name="identidad" class="form-control form-control-sm" placeholder="0801-1990-12345">
                </div>
                <div class="col-md-6 col-12">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Nombre Completo del Paciente</label>
                  <input type="text" id="edit_reg_nombre_paciente" name="nombre_paciente" class="form-control form-control-sm" placeholder="Nombre y Apellidos">
                </div>

                <div class="col-md-2 col-4">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Sexo</label>
                  <select id="edit_reg_sexo" name="sexo" class="form-select form-select-sm">
                    <option value="H">H - Hombre</option>
                    <option value="M">M - Mujer</option>
                  </select>
                </div>
                <div class="col-md-2 col-4">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Edad</label>
                  <input type="number" id="edit_reg_edad" name="edad" min="0" max="150" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 col-4">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Tipo Edad</label>
                  <select id="edit_reg_tipo" name="tipo" class="form-select form-select-sm">
                    <option value="A">A - Años</option>
                    <option value="M">M - Meses</option>
                    <option value="D">D - Días</option>
                  </select>
                </div>
                <div class="col-md-3 col-6">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">F. Nacimiento</label>
                  <input type="date" id="edit_reg_fecha_nacimiento" name="fecha_nacimiento" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 col-6">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Teléfono</label>
                  <input type="text" id="edit_reg_telefono" name="telefono" class="form-control form-control-sm" placeholder="00000000">
                </div>

                <div class="col-md-3 col-6">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Etnia</label>
                  <input type="text" id="edit_reg_etnia" name="etnia" class="form-control form-control-sm" placeholder="MESTIZO, etc.">
                </div>
                <div class="col-md-3 col-6">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Condición General</label>
                  <select id="edit_reg_cond" name="cond" class="form-select form-select-sm">
                    <option value="N">N - Nuevo</option>
                    <option value="S">S - Subsiguiente</option>
                  </select>
                </div>
                <div class="col-md-2 col-4">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Cód. Col</label>
                  <input type="text" id="edit_reg_cod_col" name="cod_col" class="form-control form-control-sm">
                </div>
                <div class="col-md-4 col-8">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Colonia / Procedencia</label>
                  <input type="text" id="edit_reg_colonia" name="colonia" class="form-control form-control-sm">
                </div>
              </div>
            </div>
          </div>

          <!-- Sección 2: Datos de la Atención / Consulta -->
          <div class="card mb-3" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 6px;">
            <div class="card-header py-2 px-3" style="background: var(--bg-surface-hover); border-bottom: 1px solid var(--border-color);">
              <span class="font-weight-bold" style="font-size: 0.82rem; color: #38bdf8;"><i class="bi bi-hospital mr-1"></i> 2. Datos de la Consulta Médica</span>
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <div class="col-md-3 col-6">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Fecha Consulta</label>
                  <input type="date" id="edit_reg_fecha" name="fecha" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2 col-6">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Sem. Epi (SE)</label>
                  <input type="number" id="edit_reg_se" name="se" min="1" max="53" class="form-control form-control-sm">
                </div>
                <div class="col-md-4 col-7">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Médico Responsable</label>
                  <input type="text" id="edit_reg_medico" name="medico" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3 col-5">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Profesión</label>
                  <input type="text" id="edit_reg_prof" name="prof" class="form-control form-control-sm">
                </div>

                <div class="col-md-3 col-6">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Jornada</label>
                  <select id="edit_reg_jornada" name="jornada" class="form-select form-select-sm">
                    <option value="MATUTINA">MATUTINA</option>
                    <option value="VESPERTINA">VESPERTINA</option>
                    <option value="NOCTURNA">NOCTURNA</option>
                  </select>
                </div>
                <div class="col-md-3 col-6">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Referido A</label>
                  <input type="text" id="edit_reg_referido_a" name="referido_a" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 col-6">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">Referido DE</label>
                  <input type="text" id="edit_reg_referido_de" name="referido_de" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 col-6">
                  <label class="form-label font-weight-bold mb-1" style="font-size: 0.75rem;">PG / EMB</label>
                  <input type="text" id="edit_reg_pg_emb" name="pg_emb" class="form-control form-control-sm">
                </div>
              </div>
            </div>
          </div>

          <!-- Sección 3: Diagnósticos 1 al 7 -->
          <div class="card mb-1" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 6px;">
            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" style="background: var(--bg-surface-hover); border-bottom: 1px solid var(--border-color);">
              <span class="font-weight-bold" style="font-size: 0.82rem; color: #38bdf8;"><i class="bi bi-clipboard2-pulse mr-1"></i> 3. Diagnósticos y Patologías (D1 a D7)</span>
              <small class="text-muted" style="font-size: 0.7rem;">Autocompletado instantáneo por código CIE o nombre</small>
            </div>
            <div class="card-body p-3">
              <div class="d-flex flex-column gap-2" id="editDiagsRowsContainer">
                @for($i = 1; $i <= 7; $i++)
                  <div class="row g-2 align-items-center p-1.5 rounded" style="background: var(--bg-surface-hover); border: 1px solid var(--border-color);">
                    <div class="col-auto">
                      <span class="badge badge-subtle-primary font-weight-bold" style="width: 28px; text-align: center;">D{{ $i }}</span>
                    </div>
                    <div class="col-md-2 col-4">
                      <input type="text" id="edit_reg_cod_{{ $i }}" name="cod_{{ $i }}" class="form-control form-control-sm edit-diag-code-input" data-index="{{ $i }}" list="codigosGlobalDatalist" placeholder="CIE-10 (ej: 102)" autocomplete="off" style="font-weight: 700;">
                    </div>
                    <div class="col-md-6 col-8">
                      <input type="text" id="edit_reg_diagnostico_{{ $i }}" name="diagnostico_{{ $i }}" class="form-control form-control-sm edit-diag-name-input" data-index="{{ $i }}" list="diagnosticosGlobalDatalist" placeholder="Escriba o busque diagnóstico..." autocomplete="off">
                    </div>
                    <div class="col-md-3 col-12">
                      <select id="edit_reg_cond_{{ $i }}" name="cond_{{ $i }}" class="form-select form-select-sm">
                        <option value="">(Ninguna)</option>
                        <option value="N">N - Nuevo</option>
                        <option value="S">S - Subsiguiente</option>
                      </select>
                    </div>
                  </div>
                @endfor
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid var(--border-color, #334155); padding: 0.65rem 1.25rem;">
          <button type="button" class="btn btn-sm btn-subtle" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" id="btnGuardarEditFull" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 0.35rem;">
            <i class="bi bi-check-lg"></i> Guardar Cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<datalist id="codigosGlobalDatalist"></datalist>
<datalist id="diagnosticosGlobalDatalist"></datalist>

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

    // Catálogo precargado de diagnósticos para autocompletado instantáneo
    let catalogoDiagsGlobal = [];
    fetch("{{ route('informesat1.buscarDiagnosticos') }}")
      .then(res => res.json())
      .then(data => {
        catalogoDiagsGlobal = data || [];
        const codDl = document.getElementById('codigosGlobalDatalist');
        const diagDl = document.getElementById('diagnosticosGlobalDatalist');

        if (diagDl && catalogoDiagsGlobal.length > 0) {
          diagDl.innerHTML = catalogoDiagsGlobal.map(d => 
            `<option value="${d.patologia}">${d.codigo ? d.codigo + ' - ' : ''}${d.patologia}</option>`
          ).join('');
        }

        if (codDl && catalogoDiagsGlobal.length > 0) {
          codDl.innerHTML = catalogoDiagsGlobal
            .filter(d => d.codigo && String(d.codigo).trim() !== '')
            .map(d => `<option value="${d.codigo}">${d.codigo} - ${d.patologia}</option>`)
            .join('');
        }
      })
      .catch(err => console.warn('Error precargando diagnósticos globales:', err));

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

        // Selección Múltiple
        this.selectedRecordIds = new Set();
        this.isMultiSelectActive = false;

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
          return this.formatRecordItem(r, index + 1);
        });

        this.filteredRecords = [...this.allRecords];
      }

      formatRecordItem(r, displayIndex) {
        const sexo = r.sexo ? (r.sexo.charAt(0).toUpperCase() || r.sexo) : '';
        const cells = [
          String(displayIndex), // 0: Índice
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
          originalIndex: displayIndex
        };
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
        const regId = String(c[49]);
        const isChecked = this.selectedRecordIds.has(regId) ? 'checked' : '';
        const chkDisplay = this.isMultiSelectActive ? 'inline-block' : 'none';
        const rowSelectedClass = this.selectedRecordIds.has(regId) ? ' row-selected' : '';

        return `<tr class="${rowSelectedClass}" data-reg-id="${regId}">` +
          `<td class="excel-row-num text-center">` +
            `<div class="excel-row-num-wrapper">` +
              `<input type="checkbox" class="row-select-checkbox form-check-input" data-id="${regId}" ${isChecked} style="display: ${chkDisplay};">` +
              `<span class="row-num-val">${displayIndex}</span>` +
            `</div>` +
          `</td>` +
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
          `<td class="excel-actions-cell text-center">` +
            `<div class="d-inline-flex align-items-center justify-content-center gap-1">` +
              `<button type="button" class="btn-action-icon text-info btn-view-reg" title="Ver Detalle Médico" data-id="${regId}"><i class="bi bi-eye"></i></button>` +
              `<button type="button" class="btn-action-icon text-primary btn-edit-reg" title="Editar Registro Completo" data-id="${regId}"><i class="bi bi-pencil-square"></i></button>` +
              `<button type="button" class="btn-action-icon text-danger btn-delete-reg" title="Eliminar Registro" data-id="${regId}"><i class="bi bi-trash3"></i></button>` +
            `</div>` +
          `</td>` +
        `</tr>`;
      }

      toggleMultiSelect(forceState) {
        if (typeof forceState === 'boolean') {
          this.isMultiSelectActive = forceState;
        } else {
          this.isMultiSelectActive = !this.isMultiSelectActive;
        }

        const isAct = this.isMultiSelectActive;
        $('#selectAllRows').css('display', isAct ? 'inline-block' : 'none');
        $('.row-select-checkbox').css('display', isAct ? 'inline-block' : 'none');
        $('#excelAt1Table').toggleClass('multiselect-active', isAct);

        if (isAct) {
          $('#btnToggleMultiSelect').addClass('btn-danger text-white').removeClass('btn-subtle text-danger');
          $('#btnToggleMultiSelectText').text('Cancelar Selección');
          this.updateSelectedCount();
        } else {
          $('#btnToggleMultiSelect').removeClass('btn-danger text-white').addClass('btn-subtle text-danger');
          $('#btnToggleMultiSelectText').text('Multiselección');
          this.selectedRecordIds.clear();
          $('#selectAllRows').prop('checked', false);
          $('.row-select-checkbox').prop('checked', false);
          $('.sing-table-excel tbody tr').removeClass('row-selected');
          $('#btnDeleteSelected').hide();
        }
      }

      updateSelectedCount() {
        const count = this.selectedRecordIds.size;
        $('#selectedCountBadge').text(count);
        if (count > 0 && this.isMultiSelectActive) {
          $('#btnDeleteSelected').css('display', 'inline-flex');
        } else {
          $('#btnDeleteSelected').hide();
        }
      }

      updateRecordInStore(updatedRaw) {
        if (!updatedRaw || !updatedRaw.id) return;
        const idStr = String(updatedRaw.id);
        const idx = this.allRecords.findIndex(r => String(r.cells[49]) === idStr);
        if (idx !== -1) {
          const formatted = this.formatRecordItem(updatedRaw, this.allRecords[idx].originalIndex);
          this.allRecords[idx] = formatted;

          // Re-apply filters to refresh view
          this.applyFilters();
        }
      }

      removeRecordFromStore(id) {
        const idStr = String(id);
        this.allRecords = this.allRecords.filter(r => String(r.cells[49]) !== idStr);
        this.selectedRecordIds.delete(idStr);
        this.applyFilters();
        this.updateSelectedCount();
      }

      removeMultipleRecordsFromStore(idsArray) {
        const idsSet = new Set(idsArray.map(id => String(id)));
        this.allRecords = this.allRecords.filter(r => !idsSet.has(String(r.cells[49])));
        idsArray.forEach(id => this.selectedRecordIds.delete(String(id)));
        this.applyFilters();
        this.updateSelectedCount();
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
          if (e.target.tagName === 'BUTTON' || e.target.tagName === 'I' || e.target.tagName === 'INPUT') return;

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

        // -------------------------------------------------------------
        // MULTISELECCIÓN & ACCIONES CRUD (VER, EDITAR, ELIMINAR)
        // -------------------------------------------------------------
        $('#btnToggleMultiSelect').on('click', function(e) {
          e.preventDefault();
          self.toggleMultiSelect();
        });

        // Checkbox maestro: Seleccionar todos los visibles
        $('#selectAllRows').on('change', function() {
          const checked = $(this).is(':checked');
          self.filteredRecords.forEach(r => {
            const id = String(r.cells[49]);
            if (id) {
              if (checked) self.selectedRecordIds.add(id);
              else self.selectedRecordIds.delete(id);
            }
          });
          self.table.querySelectorAll('.row-select-checkbox').forEach(cb => {
            cb.checked = checked;
            const tr = cb.closest('tr');
            if (tr) tr.classList.toggle('row-selected', checked);
          });
          self.updateSelectedCount();
        });

        // Checkbox individual de cada fila
        $(document).on('change', '.row-select-checkbox', function() {
          const id = String($(this).data('id'));
          const checked = $(this).is(':checked');
          if (checked) {
            self.selectedRecordIds.add(id);
          } else {
            self.selectedRecordIds.delete(id);
          }
          $(this).closest('tr').toggleClass('row-selected', checked);
          self.updateSelectedCount();
        });

        // Botón Eliminar Seleccionados (Lote)
        $('#btnDeleteSelected').on('click', function(e) {
          e.preventDefault();
          const ids = Array.from(self.selectedRecordIds);
          if (ids.length === 0) return;

          Swal.fire({
            title: `¿Eliminar ${ids.length} registros seleccionados?`,
            text: "Esta acción eliminará de forma permanente los registros y actualizará automáticamente todos los informes del sistema.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: `<i class="bi bi-trash3-fill"></i> Sí, eliminar ${ids.length} registros`,
            cancelButtonText: 'Cancelar',
            background: 'var(--bg-surface, #1e293b)',
            color: 'var(--text-primary, #f8fafc)'
          }).then((result) => {
            if (result.isConfirmed) {
              Swal.fire({
                title: 'Eliminando registros...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                background: 'var(--bg-surface, #1e293b)',
                color: 'var(--text-primary, #f8fafc)'
              });

              fetch("{{ route('registrosat1.deleteMultiple') }}", {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                  'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
              })
              .then(res => res.json())
              .then(data => {
                if (data.success) {
                  self.removeMultipleRecordsFromStore(ids);
                  Swal.fire({
                    title: '¡Eliminados!',
                    text: data.message,
                    icon: 'success',
                    timer: 2200,
                    showConfirmButton: false,
                    background: 'var(--bg-surface, #1e293b)',
                    color: 'var(--text-primary, #f8fafc)'
                  });
                } else {
                  Swal.fire({ title: 'Error', text: data.message, icon: 'error', background: 'var(--bg-surface, #1e293b)', color: 'var(--text-primary, #f8fafc)' });
                }
              })
              .catch(err => {
                Swal.fire({ title: 'Error', text: 'Error en la petición: ' + err, icon: 'error' });
              });
            }
          });
        });

        // Helper para abrir/cerrar modales
        function abrirModalId(modalId) {
          const $m = $(modalId);
          if (typeof $m.modal === 'function') {
            $m.modal('show');
          } else if (window.bootstrap && typeof bootstrap.Modal === 'function') {
            try {
              const inst = bootstrap.Modal.getInstance ? bootstrap.Modal.getInstance($m[0]) : null;
              (inst || new bootstrap.Modal($m[0])).show();
            } catch(e) {
              $m.addClass('show').css('display', 'block');
              $('body').addClass('modal-open');
            }
          } else {
            $m.addClass('show').css('display', 'block');
            $('body').addClass('modal-open');
          }
        }

        function cerrarModalId(modalId) {
          const $m = $(modalId);
          if (typeof $m.modal === 'function') {
            $m.modal('hide');
          } else if (window.bootstrap && typeof bootstrap.Modal === 'function') {
            try {
              const inst = bootstrap.Modal.getInstance ? bootstrap.Modal.getInstance($m[0]) : null;
              if (inst) inst.hide();
            } catch(e) {}
          }
          $m.removeClass('show').css('display', 'none');
          $('body').removeClass('modal-open');
          $('.modal-backdrop').remove();
        }

        // Eventos cerrar modales
        $(document).on('click', '.modal [data-dismiss="modal"], .modal [data-bs-dismiss="modal"]', function(e) {
          e.preventDefault();
          const targetModal = $(this).closest('.modal');
          if (targetModal.length) cerrarModalId('#' + targetModal.attr('id'));
        });

        // 1. BOTÓN VER DETALLE MÉDICO
        let activeViewRecordId = null;
        $(document).on('click', '.btn-view-reg', function(e) {
          e.preventDefault();
          const id = $(this).data('id');
          activeViewRecordId = id;

          fetch(`{{ url('/registrosat1/registro') }}/${id}`)
            .then(res => res.json())
            .then(resData => {
              if (!resData.success || !resData.data) {
                SingApp.toast({ title: 'Error', message: 'No se pudo cargar el registro.', type: 'danger' });
                return;
              }
              const d = resData.data;
              $('#ver_paciente_nombre').text(d.nombre_paciente || 'Sin nombre registrado');
              $('#ver_exp').text(d.exp || 'Sin Exp.');
              $('#ver_fecha').text(d.fecha || '-');
              $('#ver_identidad').text(d.identidad || '-');
              $('#ver_telefono').text(d.telefono || '-');
              $('#ver_fecha_nacimiento').text(d.fecha_nacimiento || '-');
              $('#ver_sexo_edad').text(`${d.sexo || '-'} • ${d.edad || '-'} ${d.tipo || ''}`);
              $('#ver_etnia').text(d.etnia || '-');
              $('#ver_colonia').text(`${d.colonia || '-'} ${d.cod_col ? '(Cód: ' + d.cod_col + ')' : ''}`);

              $('#ver_medico').text(d.medico || 'No especificado');
              $('#ver_prof').text(d.prof || '-');
              $('#ver_jornada').text(d.jornada || '-');
              $('#ver_se').text(d.se || '-');
              $('#ver_cond').text(d.cond === 'N' ? 'N - Nuevo' : (d.cond === 'S' ? 'S - Subsiguiente' : (d.cond || '-')));
              $('#ver_referencia').text(`${d.referido_a ? 'A: ' + d.referido_a : ''} ${d.referido_de ? 'De: ' + d.referido_de : '-'}`);

              let diagsHtml = '';
              let hasDiags = false;
              for (let i = 1; i <= 7; i++) {
                const diag = d['diagnostico_' + i] || (i === 1 ? d.diagnostico : '');
                const cod = d['cod_' + i] || (i === 1 ? d.cod : '');
                const cond = d['cond_' + i] || (i === 1 ? d.cond : '');

                if (diag || cod) {
                  hasDiags = true;
                  const condBadge = cond === 'N' ? '<span class="badge badge-subtle-primary font-weight-bold">N</span>' : (cond === 'S' ? '<span class="badge badge-subtle-secondary font-weight-bold">S</span>' : '-');
                  diagsHtml += `
                    <tr>
                      <td class="text-center font-weight-bold text-primary">D${i}</td>
                      <td style="font-family: monospace; font-weight: 700; color: #38bdf8;">${cod || '-'}</td>
                      <td class="font-weight-600">${diag || '-'}</td>
                      <td class="text-center">${condBadge}</td>
                    </tr>
                  `;
                }
              }
              if (!hasDiags) {
                diagsHtml = '<tr><td colspan="4" class="text-center py-2 text-muted">Sin diagnósticos registrados.</td></tr>';
              }
              $('#verDiagnosticosTableBody').html(diagsHtml);

              abrirModalId('#modalVerRegistro');
            })
            .catch(err => {
              SingApp.toast({ title: 'Error', message: 'Error cargando datos: ' + err, type: 'danger' });
            });
        });

        // Botón "Editar este Registro" desde modal Ver
        $('#btnEditarDesdeVer').on('click', function(e) {
          e.preventDefault();
          cerrarModalId('#modalVerRegistro');
          if (activeViewRecordId) {
            $(`.btn-edit-reg[data-id="${activeViewRecordId}"]`).first().trigger('click');
          }
        });

        // 2. BOTÓN EDITAR REGISTRO COMPLETO (POPUP)
        $(document).on('click', '.btn-edit-reg', function(e) {
          e.preventDefault();
          const id = $(this).data('id');

          fetch(`{{ url('/registrosat1/registro') }}/${id}`)
            .then(res => res.json())
            .then(resData => {
              if (!resData.success || !resData.data) {
                SingApp.toast({ title: 'Error', message: 'No se pudo cargar el registro.', type: 'danger' });
                return;
              }
              const d = resData.data;
              $('#edit_reg_id').val(d.id);
              $('#edit_reg_exp').val(d.exp || '');
              $('#edit_reg_identidad').val(d.identidad || '');
              $('#edit_reg_nombre_paciente').val(d.nombre_paciente || '');
              $('#edit_reg_sexo').val(d.sexo ? (d.sexo.charAt(0).toUpperCase() || d.sexo) : 'H');
              $('#edit_reg_edad').val(d.edad ?? '');
              $('#edit_reg_tipo').val(d.tipo ? d.tipo.toUpperCase() : 'A');
              $('#edit_reg_fecha_nacimiento').val(d.fecha_nacimiento || '');
              $('#edit_reg_telefono').val(d.telefono || '');
              $('#edit_reg_etnia').val(d.etnia || '');
              $('#edit_reg_cond').val(d.cond ? d.cond.toUpperCase() : 'N');
              $('#edit_reg_cod_col').val(d.cod_col || '');
              $('#edit_reg_colonia').val(d.colonia || '');

              $('#edit_reg_fecha').val(d.fecha || '');
              $('#edit_reg_se').val(d.se ?? '');
              $('#edit_reg_medico').val(d.medico || '');
              $('#edit_reg_prof').val(d.prof || '');
              $('#edit_reg_jornada').val(d.jornada ? d.jornada.toUpperCase() : 'MATUTINA');
              $('#edit_reg_referido_a').val(d.referido_a || '');
              $('#edit_reg_referido_de').val(d.referido_de || '');
              $('#edit_reg_pg_emb').val(d.pg_emb || '');

              // Llenar diagnósticos 1 al 7
              for (let i = 1; i <= 7; i++) {
                const cod = d['cod_' + i] || (i === 1 ? d.cod : '') || '';
                const diag = d['diagnostico_' + i] || (i === 1 ? d.diagnostico : '') || '';
                const cond = d['cond_' + i] || (i === 1 ? d.cond : '') || '';

                $(`#edit_reg_cod_${i}`).val(cod);
                $(`#edit_reg_diagnostico_${i}`).val(diag);
                $(`#edit_reg_cond_${i}`).val(cond ? cond.toUpperCase() : '');
              }

              abrirModalId('#modalEditarRegistroCompleto');
            })
            .catch(err => {
              SingApp.toast({ title: 'Error', message: 'Error cargando datos para edición: ' + err, type: 'danger' });
            });
        });

        // Búsqueda bidireccional Código <-> Diagnóstico en los 7 slots de edición
        $(document).on('input change blur', '.edit-diag-code-input', function() {
          const idx = $(this).data('index');
          const rawCod = $(this).val();
          if (!rawCod) return;
          const codVal = String(rawCod).trim();
          if (codVal === '') return;

          const found = catalogoDiagsGlobal.find(d => 
            String(d.codigo || '').trim().toUpperCase() === codVal.toUpperCase()
          );

          if (found && found.patologia) {
            $(`#edit_reg_diagnostico_${idx}`).val(found.patologia);
          }
        });

        $(document).on('input change blur', '.edit-diag-name-input', function() {
          const idx = $(this).data('index');
          const val = $(this).val().trim().toUpperCase();
          if (!val) return;

          const found = catalogoDiagsGlobal.find(d => (d.patologia || '').toUpperCase() === val);
          if (found && found.codigo) {
            $(`#edit_reg_cod_${idx}`).val(found.codigo);
          }
        });

        // Enviar Formulario de Edición Completa
        $('#formEditarRegistroCompleto').on('submit', function(e) {
          e.preventDefault();
          const btnSubmit = $('#btnGuardarEditFull');
          const origHtml = btnSubmit.html();
          btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

          const formData = new FormData(this);
          const dataObj = {};
          formData.forEach((val, key) => { dataObj[key] = val; });

          fetch("{{ route('registrosat1.updateFull') }}", {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Accept': 'application/json'
            },
            body: JSON.stringify(dataObj)
          })
          .then(res => res.json())
          .then(data => {
            btnSubmit.prop('disabled', false).html(origHtml);
            if (data.success) {
              cerrarModalId('#modalEditarRegistroCompleto');
              self.updateRecordInStore(data.data);
              Swal.fire({
                title: '¡Actualizado con Éxito!',
                text: data.message,
                icon: 'success',
                timer: 2400,
                showConfirmButton: false,
                background: 'var(--bg-surface, #1e293b)',
                color: 'var(--text-primary, #f8fafc)'
              });
            } else {
              Swal.fire({ title: 'Error', text: data.message, icon: 'error', background: 'var(--bg-surface, #1e293b)', color: 'var(--text-primary, #f8fafc)' });
            }
          })
          .catch(err => {
            btnSubmit.prop('disabled', false).html(origHtml);
            Swal.fire({ title: 'Error', text: 'Error al actualizar el registro: ' + err, icon: 'error' });
          });
        });

        // 3. BOTÓN ELIMINAR INDIVIDUAL
        $(document).on('click', '.btn-delete-reg', function(e) {
          e.preventDefault();
          const id = $(this).data('id');
          const rec = self.allRecords.find(r => String(r.cells[49]) === String(id));
          const pacName = rec ? rec.cells[8] : 'este paciente';

          Swal.fire({
            title: '¿Eliminar registro?',
            html: `¿Desea eliminar el registro de <strong>${escHtml(pacName)}</strong> (ID: #${id})?<br><small class="text-muted mt-1 d-block">También puede activar la selección múltiple para eliminar varios a la vez.</small>`,
            icon: 'warning',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonColor: '#ef4444',
            denyButtonColor: '#3b82f6',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-trash3"></i> Sí, eliminar este',
            denyButtonText: '<i class="bi bi-check2-square"></i> Activar Multiselección',
            cancelButtonText: 'Cancelar',
            background: 'var(--bg-surface, #1e293b)',
            color: 'var(--text-primary, #f8fafc)'
          }).then((result) => {
            if (result.isConfirmed) {
              fetch(`{{ url('/registrosat1') }}/${id}`, {
                method: 'DELETE',
                headers: {
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                  'Accept': 'application/json'
                }
              })
              .then(res => res.json())
              .then(data => {
                if (data.success) {
                  self.removeRecordFromStore(id);
                  Swal.fire({
                    title: '¡Eliminado!',
                    text: data.message,
                    icon: 'success',
                    timer: 1800,
                    showConfirmButton: false,
                    background: 'var(--bg-surface, #1e293b)',
                    color: 'var(--text-primary, #f8fafc)'
                  });
                } else {
                  Swal.fire({ title: 'Error', text: data.message, icon: 'error', background: 'var(--bg-surface, #1e293b)', color: 'var(--text-primary, #f8fafc)' });
                }
              })
              .catch(err => Swal.fire({ title: 'Error', text: 'Error en la petición: ' + err, icon: 'error' }));
            } else if (result.isDenied) {
              self.toggleMultiSelect(true);
              self.selectedRecordIds.add(String(id));
              self.table.querySelectorAll(`.row-select-checkbox[data-id="${id}"]`).forEach(cb => {
                cb.checked = true;
                cb.closest('tr')?.classList.add('row-selected');
              });
              self.updateSelectedCount();
            }
          });
        });
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
              <td colspan="51" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
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
