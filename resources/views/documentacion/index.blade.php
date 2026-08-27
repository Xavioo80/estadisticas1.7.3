@extends('layouts.app')

@section('title', 'Gestor de Documentación y Archivos')

@push('styles')
<style>
  /* Contenedor principal de Documentación */
  .doc-container {
    padding: 0.5rem 0.5rem 2rem;
  }

  /* Barra de KPIs */
  .doc-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1.25rem;
  }

  .doc-kpi-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 0.85rem 1.15rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--shadow-sm);
    transition: transform var(--transition-fast), box-shadow var(--transition-fast), border-color var(--transition-fast);
  }

  .doc-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: rgba(var(--color-primary-rgb), 0.4);
  }

  .doc-kpi-icon {
    width: 44px;
    height: 44px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
  }

  .doc-kpi-icon.primary { background: var(--color-primary-light); color: var(--color-primary); }
  .doc-kpi-icon.success { background: var(--color-success-light); color: var(--color-success); }
  .doc-kpi-icon.warning { background: var(--color-warning-light); color: var(--color-warning); }
  .doc-kpi-icon.purple  { background: var(--color-purple-light);  color: var(--color-purple);  }

  .doc-kpi-info {
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  .doc-kpi-label {
    font-size: 0.75rem;
    color: var(--text-muted);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .doc-kpi-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.2;
    margin-top: 2px;
  }

  /* Barra de herramientas / Explorador */
  .doc-toolbar {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 0.75rem 1rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
    box-shadow: var(--shadow-sm);
  }

  .doc-breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.4rem;
    font-size: 0.9rem;
  }

  .doc-breadcrumb a {
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-xs);
    transition: background-color var(--transition-fast);
  }

  .doc-breadcrumb a:hover {
    background-color: var(--color-primary-light);
  }

  .doc-breadcrumb .separator {
    color: var(--text-muted);
    font-size: 0.8rem;
  }

  .doc-breadcrumb .current {
    color: var(--text-primary);
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    background: var(--bg-subtle);
    border-radius: var(--radius-xs);
  }

  .doc-search-box {
    position: relative;
    min-width: 240px;
  }

  .doc-search-box i {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 0.9rem;
  }

  .doc-search-input {
    width: 100%;
    padding: 0.45rem 0.75rem 0.45rem 2.2rem;
    font-size: 0.85rem;
    background: var(--bg-subtle);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    transition: border-color var(--transition-fast), background-color var(--transition-fast);
  }

  .doc-search-input:focus {
    background: var(--bg-surface);
    border-color: var(--color-primary);
    outline: none;
  }

  /* Grid de Carpetas */
  .doc-section-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .doc-section-title i {
    color: var(--color-primary);
  }

  .doc-folders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 0.85rem;
    margin-bottom: 1.5rem;
  }

  .doc-folder-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 0.9rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    text-decoration: none;
    color: var(--text-primary);
    box-shadow: var(--shadow-sm);
    transition: all var(--transition-fast);
    position: relative;
  }

  .doc-folder-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: rgba(var(--color-primary-rgb), 0.5);
    color: var(--color-primary);
    text-decoration: none;
  }

  .doc-folder-main {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    overflow: hidden;
    flex: 1;
  }

  .doc-folder-icon {
    font-size: 1.75rem;
    line-height: 1;
    transition: transform var(--transition-fast);
    flex-shrink: 0;
  }

  .doc-folder-card:hover .doc-folder-icon {
    transform: scale(1.08);
  }

  /* Colores de Carpetas */
  .folder-color-primary { color: #4d7cfe; }
  .folder-color-success { color: #22c55e; }
  .folder-color-warning { color: #f59e0b; }
  .folder-color-danger  { color: #ef4444; }
  .folder-color-purple  { color: #8b5cf6; }
  .folder-color-info    { color: #06b6d4; }
  .folder-color-teal    { color: #14b8a6; }

  .doc-folder-details {
    overflow: hidden;
  }

  .doc-folder-name {
    font-size: 0.88rem;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
    color: var(--text-primary);
  }

  .doc-folder-card:hover .doc-folder-name {
    color: var(--color-primary);
  }

  .doc-folder-count {
    font-size: 0.72rem;
    color: var(--text-muted);
    margin-top: 2px;
  }

  .doc-folder-actions {
    display: flex;
    align-items: center;
    gap: 0.2rem;
    opacity: 0.7;
    transition: opacity var(--transition-fast);
  }

  .doc-folder-card:hover .doc-folder-actions {
    opacity: 1;
  }

  .doc-folder-btn {
    background: transparent;
    border: none;
    color: var(--text-muted);
    padding: 4px 6px;
    border-radius: var(--radius-xs);
    font-size: 0.8rem;
    cursor: pointer;
    transition: all var(--transition-fast);
  }

  .doc-folder-btn:hover {
    background: var(--bg-subtle);
    color: var(--text-primary);
  }

  /* Grid de Archivos */
  .doc-files-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
  }

  .doc-file-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 1rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: var(--shadow-sm);
    transition: all var(--transition-fast);
    position: relative;
  }

  .doc-file-card.is-missing {
    border-color: rgba(var(--color-warning-rgb), 0.4);
    background: linear-gradient(to bottom, var(--bg-surface), var(--bg-subtle));
  }

  .doc-file-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
    border-color: rgba(var(--color-primary-rgb), 0.4);
  }

  .doc-file-header {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    margin-bottom: 0.75rem;
  }

  .doc-file-badge-icon {
    width: 42px;
    height: 42px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
  }

  .ext-pdf  { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
  .ext-doc  { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
  .ext-xls  { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
  .ext-img  { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
  .ext-zip  { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
  .ext-txt  { background: rgba(100, 116, 139, 0.12); color: #64748b; }
  .ext-other{ background: rgba(100, 116, 139, 0.12); color: #64748b; }

  .doc-file-info {
    flex: 1;
    overflow: hidden;
  }

  .doc-file-title {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--text-primary);
    word-break: break-word;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.3;
    margin-bottom: 0.25rem;
  }

  .doc-file-cat {
    font-size: 0.7rem;
    color: var(--color-primary);
    background: var(--color-primary-light);
    padding: 2px 6px;
    border-radius: 4px;
    display: inline-block;
    font-weight: 500;
    margin-bottom: 0.25rem;
  }

  .badge-missing-disk {
    font-size: 0.68rem;
    color: #b45309;
    background: rgba(245, 158, 11, 0.15);
    border: 1px solid rgba(245, 158, 11, 0.3);
    padding: 2px 6px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-weight: 600;
  }

  .doc-file-desc {
    font-size: 0.75rem;
    color: var(--text-muted);
    line-height: 1.25;
    margin-bottom: 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .doc-file-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.72rem;
    color: var(--text-muted);
    border-top: 1px solid var(--border-color);
    padding-top: 0.6rem;
    margin-top: auto;
  }

  .doc-file-actions {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: 0.75rem;
  }

  .doc-btn-action {
    flex: 1;
    padding: 0.35rem 0.5rem;
    font-size: 0.78rem;
    font-weight: 500;
    border-radius: var(--radius-xs);
    border: 1px solid var(--border-color);
    background: var(--bg-surface);
    color: var(--text-primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    cursor: pointer;
    text-decoration: none;
    transition: all var(--transition-fast);
  }

  .doc-btn-action:hover {
    background: var(--bg-subtle);
    color: var(--color-primary);
    border-color: var(--color-primary);
    text-decoration: none;
  }

  .doc-btn-action.btn-view:hover {
    background: var(--color-primary-light);
    color: var(--color-primary);
    border-color: var(--color-primary);
  }

  .doc-btn-action.btn-replace {
    background: var(--color-warning-light);
    color: var(--color-warning);
    border-color: rgba(var(--color-warning-rgb), 0.3);
  }

  .doc-btn-action.btn-replace:hover {
    background: var(--color-warning);
    color: #fff;
  }

  .doc-btn-action.btn-delete:hover {
    background: var(--color-danger-light);
    color: var(--color-danger);
    border-color: var(--color-danger);
  }

  /* Vista de Tabla */
  .doc-table-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    margin-bottom: 1.5rem;
  }

  .doc-table {
    width: 100%;
    margin-bottom: 0;
    border-collapse: collapse;
  }

  .doc-table th {
    background: var(--bg-subtle);
    color: var(--text-muted);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border-color);
    border-top: none;
  }

  .doc-table td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
    color: var(--text-primary);
    font-size: 0.85rem;
    border-bottom: 1px solid var(--border-color);
  }

  .doc-table tbody tr:hover {
    background-color: var(--bg-surface-hover);
  }

  /* Swatches de Colores para Carpetas */
  .color-swatch-group {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
  }

  .color-swatch-label {
    cursor: pointer;
    position: relative;
  }

  .color-swatch-label input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
  }

  .color-swatch {
    width: 28px;
    height: 28px;
    border-radius: var(--radius-full);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.8rem;
    transition: transform var(--transition-fast), box-shadow var(--transition-fast);
  }

  .color-swatch-label input:checked + .color-swatch {
    transform: scale(1.18);
    box-shadow: 0 0 0 2px var(--bg-surface), 0 0 0 4px currentColor;
  }

  /* Dropzone de Archivos */
  .dropzone-area {
    border: 2px dashed var(--border-color);
    border-radius: var(--radius-md);
    padding: 1.75rem 1rem;
    text-align: center;
    background: var(--bg-subtle);
    cursor: pointer;
    transition: all var(--transition-fast);
  }

  .dropzone-area.dragover,
  .dropzone-area:hover {
    border-color: var(--color-primary);
    background: var(--color-primary-light);
  }

  .dropzone-icon {
    font-size: 2.2rem;
    color: var(--color-primary);
    margin-bottom: 0.5rem;
  }

  .file-preview-list {
    max-height: 180px;
    overflow-y: auto;
    margin-top: 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
  }

  .file-preview-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xs);
    padding: 0.4rem 0.65rem;
    font-size: 0.8rem;
  }

  /* Empty state */
  .doc-empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    background: var(--bg-surface);
    border: 1px dashed var(--border-color);
    border-radius: var(--radius-md);
    margin-bottom: 1.5rem;
  }

  .doc-empty-icon {
    font-size: 3.5rem;
    color: var(--text-muted);
    opacity: 0.6;
    margin-bottom: 0.75rem;
  }

  .doc-empty-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
  }

  .doc-empty-subtitle {
    font-size: 0.85rem;
    color: var(--text-muted);
    max-width: 400px;
    margin: 0 auto 1.25rem;
  }

  /* Visor Modal */
  .preview-modal-body {
    padding: 0;
    height: 75vh;
    display: flex;
    flex-direction: column;
    background: var(--bg-subtle);
  }

  .preview-iframe {
    width: 100%;
    height: 100%;
    border: none;
  }

  .preview-img-container {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    padding: 1rem;
    overflow: auto;
  }

  .preview-img-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow-md);
  }
</style>
@endpush

@section('content')
<div class="doc-container">

  <!-- Mensajes de Estado Flash -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" style="background: var(--color-success-light); color: var(--color-success); border-color: rgba(var(--color-success-rgb), 0.3);">
      <i class="bi bi-check-circle-fill mr-2"></i> {{ session('success') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: inherit;">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" style="background: var(--color-danger-light); color: var(--color-danger); border-color: rgba(var(--color-danger-rgb), 0.3);">
      <i class="bi bi-exclamation-triangle-fill mr-2"></i> {{ session('error') }}
      <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color: inherit;">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  <!-- Barra de KPIs y Métricas -->
  <div class="doc-kpi-grid">
    <div class="doc-kpi-card">
      <div class="doc-kpi-icon primary">
        <i class="bi bi-folder-fill"></i>
      </div>
      <div class="doc-kpi-info">
        <span class="doc-kpi-label">Carpetas</span>
        <span class="doc-kpi-value">{{ $totalCategorias }}</span>
      </div>
    </div>

    <div class="doc-kpi-card">
      <div class="doc-kpi-icon success">
        <i class="bi bi-file-earmark-text-fill"></i>
      </div>
      <div class="doc-kpi-info">
        <span class="doc-kpi-label">Documentos</span>
        <span class="doc-kpi-value">{{ $totalDocumentos }}</span>
      </div>
    </div>

    <div class="doc-kpi-card">
      <div class="doc-kpi-icon warning">
        <i class="bi bi-hdd-fill"></i>
      </div>
      <div class="doc-kpi-info">
        <span class="doc-kpi-label">Espacio Usado</span>
        <span class="doc-kpi-value">
          @php
            $bytes = $totalTamano ?? 0;
            if ($bytes >= 1073741824) {
                $formattedSize = number_format($bytes / 1073741824, 2) . ' GB';
            } elseif ($bytes >= 1048576) {
                $formattedSize = number_format($bytes / 1048576, 2) . ' MB';
            } elseif ($bytes >= 1024) {
                $formattedSize = number_format($bytes / 1024, 1) . ' KB';
            } else {
                $formattedSize = $bytes . ' B';
            }
          @endphp
          {{ $formattedSize }}
        </span>
      </div>
    </div>

    <div class="doc-kpi-card">
      <div class="doc-kpi-icon purple">
        <i class="bi bi-layers-fill"></i>
      </div>
      <div class="doc-kpi-info">
        <span class="doc-kpi-label">Ubicación Actual</span>
        <span class="doc-kpi-value text-truncate" style="font-size: 0.95rem;" title="{{ $categoriaActual ? $categoriaActual->nombre : 'Raíz (Principal)' }}">
          {{ $categoriaActual ? $categoriaActual->nombre : 'Raíz (Principal)' }}
        </span>
      </div>
    </div>
  </div>

  <!-- Barra de Herramientas y Navegación -->
  <div class="doc-toolbar">
    <!-- Breadcrumbs -->
    <div class="doc-breadcrumb">
      <a href="{{ route('documentacion.index') }}" title="Ir a la carpeta principal">
        <i class="bi bi-house-door-fill"></i> Documentación
      </a>

      @if($categoriaActual && count($breadcrumb) > 0)
        @foreach($breadcrumb as $index => $item)
          <span class="separator"><i class="bi bi-chevron-right"></i></span>
          @if($loop->last)
            <span class="current"><i class="bi bi-folder2-open mr-1"></i> {{ $item->nombre }}</span>
          @else
            <a href="{{ route('documentacion.index', ['categoria' => $item->id]) }}">{{ $item->nombre }}</a>
          @endif
        @endforeach
      @endif
    </div>

    <!-- Acciones Rápidas y Búsqueda -->
    <div class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
      <!-- Buscador en tiempo real -->
      <div class="doc-search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="docSearch" class="doc-search-input" placeholder="Buscar documento o carpeta..." autocomplete="off">
      </div>

      <!-- Alternador de Vista (Cuadrícula / Lista) -->
      <div class="btn-group" role="group" aria-label="Modo de vista">
        <button type="button" class="btn btn-sm btn-outline-secondary active" id="btnViewGrid" title="Vista Cuadrícula">
          <i class="bi bi-grid-fill"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnViewList" title="Vista Lista">
          <i class="bi bi-list-ul"></i>
        </button>
      </div>

      <!-- Botón Nueva Carpeta -->
      <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#modalNuevaCarpeta">
        <i class="bi bi-folder-plus mr-1"></i> Nueva Carpeta
      </button>

      <!-- Botón Subir Archivo -->
      <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalSubirArchivos">
        <i class="bi bi-cloud-arrow-up-fill mr-1"></i> Subir Archivos
      </button>
    </div>
  </div>

  @php
    // Carpetas a mostrar en la vista actual
    $carpetasMostrar = $categoriaActual ? $categoriaActual->subcarpetas : $categorias;
    // Documentos a mostrar en la vista actual
    $documentosMostrar = $categoriaActual ? $categoriaActual->documentos : $documentosSinCategoria;
  @endphp

  <!-- SECCIÓN 1: CARPETAS / SUBCARPETAS -->
  @if($carpetasMostrar && $carpetasMostrar->count() > 0)
    <div class="mb-4">
      <div class="doc-section-title">
        <i class="bi bi-folder2-open"></i>
        <span>{{ $categoriaActual ? 'Subcarpetas' : 'Carpetas del Repositorio' }} ({{ $carpetasMostrar->count() }})</span>
      </div>

      <div class="doc-folders-grid" id="foldersContainer">
        @foreach($carpetasMostrar as $carpeta)
          @php
            $colorClass = 'folder-color-' . ($carpeta->color ?? 'primary');
            $docCount = $carpeta->documentos ? $carpeta->documentos->count() : ($carpeta->documentos_count ?? 0);
            $subCount = $carpeta->subcarpetas ? $carpeta->subcarpetas->count() : ($carpeta->subcarpetas_count ?? 0);
          @endphp
          <div class="doc-folder-card folder-item-filter" data-name="{{ strtolower($carpeta->nombre) }}">
            <a href="{{ route('documentacion.index', ['categoria' => $carpeta->id]) }}" class="doc-folder-main">
              <i class="bi bi-folder-fill doc-folder-icon {{ $colorClass }}"></i>
              <div class="doc-folder-details">
                <span class="doc-folder-name" title="{{ $carpeta->nombre }}">{{ $carpeta->nombre }}</span>
                <span class="doc-folder-count">
                  {{ $docCount }} {{ $docCount == 1 ? 'archivo' : 'archivos' }}
                  @if($subCount > 0)
                    · {{ $subCount }} {{ $subCount == 1 ? 'subcarpeta' : 'subcarpetas' }}
                  @endif
                </span>
              </div>
            </a>

            <!-- Acciones de Carpeta -->
            <div class="doc-folder-actions dropdown">
              <button class="doc-folder-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Opciones">
                <i class="bi bi-three-dots-vertical"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-right" style="background: var(--bg-surface); border-color: var(--border-color); box-shadow: var(--shadow-md);">
                <a class="dropdown-item" href="{{ route('documentacion.index', ['categoria' => $carpeta->id]) }}" style="color: var(--text-primary);">
                  <i class="bi bi-folder2-open mr-2 text-primary"></i> Abrir Carpeta
                </a>
                <button type="button" class="dropdown-item btn-edit-folder" 
                        data-id="{{ $carpeta->id }}" 
                        data-nombre="{{ $carpeta->nombre }}" 
                        data-color="{{ $carpeta->color ?? 'primary' }}"
                        style="color: var(--text-primary);">
                  <i class="bi bi-pencil mr-2 text-warning"></i> Renombrar / Color
                </button>
                <div class="dropdown-divider" style="border-color: var(--border-color);"></div>
                <form action="{{ route('documentacion.destroyFolder', $carpeta->id) }}" method="POST" class="form-delete-folder">
                  @csrf
                  @method('DELETE')
                  <button type="button" class="dropdown-item text-danger btn-confirm-delete-folder" data-count="{{ $docCount }}">
                    <i class="bi bi-trash mr-2"></i> Eliminar Carpeta
                  </button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif

  <!-- SECCIÓN 2: DOCUMENTOS -->
  <div class="mb-4">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div class="doc-section-title mb-0">
        <i class="bi bi-file-earmark-text"></i>
        <span>
          {{ $categoriaActual ? 'Archivos en ' . $categoriaActual->nombre : 'Archivos en Raíz' }} 
          (<span id="docCountBadge">{{ $documentosMostrar ? $documentosMostrar->count() : 0 }}</span>)
        </span>
      </div>

      @if($categoriaActual)
        <a href="{{ $categoriaActual->parent_id ? route('documentacion.index', ['categoria' => $categoriaActual->parent_id]) : route('documentacion.index') }}" 
           class="btn btn-xs btn-outline-secondary" style="font-size: 0.75rem;">
          <i class="bi bi-arrow-return-left mr-1"></i> Subir un Nivel
        </a>
      @endif
    </div>

    @if(!$documentosMostrar || $documentosMostrar->count() == 0)
      <div class="doc-empty-state" id="emptyStateBox">
        <div class="doc-empty-icon">
          <i class="bi bi-folder2"></i>
        </div>
        <h5 class="doc-empty-title">Esta carpeta no contiene archivos</h5>
        <p class="doc-empty-subtitle">Sube nuevos documentos, manuales, hojas de cálculo o informes en esta ubicación.</p>
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalSubirArchivos">
          <i class="bi bi-cloud-arrow-up-fill mr-1"></i> Subir Primer Archivo
        </button>
      </div>
    @else
      <!-- VISTA 1: CUADRÍCULA (GRID) -->
      <div class="doc-files-grid" id="filesGridContainer">
        @foreach($documentosMostrar as $doc)
          @php
            $ext = strtolower($doc->extension ?? pathinfo($doc->nombre_original, PATHINFO_EXTENSION));
            $badgeClass = 'ext-other';
            $iconClass = 'bi-file-earmark';

            if (in_array($ext, ['pdf'])) {
                $badgeClass = 'ext-pdf';
                $iconClass = 'bi-file-earmark-pdf-fill';
            } elseif (in_array($ext, ['doc', 'docx'])) {
                $badgeClass = 'ext-doc';
                $iconClass = 'bi-file-earmark-word-fill';
            } elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) {
                $badgeClass = 'ext-xls';
                $iconClass = 'bi-file-earmark-excel-fill';
            } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                $badgeClass = 'ext-img';
                $iconClass = 'bi-file-earmark-image-fill';
            } elseif (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) {
                $badgeClass = 'ext-zip';
                $iconClass = 'bi-file-earmark-zip-fill';
            } elseif (in_array($ext, ['txt', 'md', 'json', 'log'])) {
                $badgeClass = 'ext-txt';
                $iconClass = 'bi-file-earmark-text-fill';
            }

            // Formato de tamaño
            $dBytes = $doc->tamano ?? 0;
            if ($dBytes >= 1048576) {
                $dSize = number_format($dBytes / 1048576, 2) . ' MB';
            } elseif ($dBytes >= 1024) {
                $dSize = number_format($dBytes / 1024, 1) . ' KB';
            } else {
                $dSize = $dBytes . ' B';
            }

            $fileExists = $doc->existe_fisicamente;
            $isViewable = in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'txt']);
          @endphp

          <div class="doc-file-card file-item-filter {{ !$fileExists ? 'is-missing' : '' }}" data-name="{{ strtolower($doc->nombre_original . ' ' . $doc->descripcion) }}" data-ext="{{ $ext }}">
            <div>
              <div class="doc-file-header">
                <div class="doc-file-badge-icon {{ $badgeClass }}">
                  <i class="bi {{ $iconClass }}"></i>
                </div>
                <div class="doc-file-info">
                  <span class="doc-file-title" title="{{ $doc->nombre_original }}">{{ $doc->nombre_original }}</span>
                  <div class="d-flex align-items-center flex-wrap" style="gap: 4px;">
                    @if($doc->categoria)
                      <span class="doc-file-cat">{{ $doc->categoria->nombre }}</span>
                    @endif
                    @if(!$fileExists)
                      <span class="badge-missing-disk" title="El archivo no está físicamente en el disco. Haz clic en Re-subir para vincularlo.">
                        <i class="bi bi-exclamation-triangle-fill"></i> No en disco
                      </span>
                    @endif
                  </div>
                </div>
              </div>

              @if($doc->descripcion)
                <p class="doc-file-desc" title="{{ $doc->descripcion }}">{{ $doc->descripcion }}</p>
              @endif
            </div>

            <div>
              <div class="doc-file-meta">
                <span><i class="bi bi-hdd mr-1"></i> {{ $dSize }}</span>
                <span><i class="bi bi-calendar3 mr-1"></i> {{ $doc->created_at ? $doc->created_at->format('d/m/Y') : '-' }}</span>
              </div>

              <div class="doc-file-actions">
                @if($fileExists)
                  @if($isViewable)
                    <button type="button" class="doc-btn-action btn-view btn-preview-doc" 
                            data-id="{{ $doc->id }}" 
                            data-name="{{ $doc->nombre_original }}" 
                            data-ext="{{ $ext }}"
                            data-url="{{ route('documentacion.view', $doc->id) }}"
                            title="Previsualizar">
                      <i class="bi bi-eye"></i> Ver
                    </button>
                  @endif

                  <a href="{{ route('documentacion.download', $doc->id) }}" class="doc-btn-action btn-view" title="Descargar archivo">
                    <i class="bi bi-download"></i> Bajar
                  </a>
                @else
                  <!-- Botón Re-subir si el archivo físico no se encuentra -->
                  <button type="button" class="doc-btn-action btn-replace btn-trigger-replace" 
                          data-id="{{ $doc->id }}" 
                          data-name="{{ $doc->nombre_original }}"
                          title="Volver a subir el archivo físico">
                    <i class="bi bi-cloud-arrow-up-fill"></i> Re-subir
                  </button>
                @endif

                <form action="{{ route('documentacion.destroy', $doc->id) }}" method="POST" class="d-inline form-delete-doc">
                  @csrf
                  @method('DELETE')
                  <button type="button" class="doc-btn-action btn-delete btn-confirm-delete-doc" title="Eliminar registro">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <!-- VISTA 2: TABLA (LISTA) -->
      <div class="doc-table-card d-none" id="filesTableContainer">
        <div class="table-responsive">
          <table class="table doc-table">
            <thead>
              <tr>
                <th>Archivo</th>
                <th>Carpeta / Ubicación</th>
                <th>Estado</th>
                <th>Extensión</th>
                <th>Tamaño</th>
                <th>Fecha Subida</th>
                <th class="text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach($documentosMostrar as $doc)
                @php
                  $ext = strtolower($doc->extension ?? pathinfo($doc->nombre_original, PATHINFO_EXTENSION));
                  $badgeClass = 'ext-other';
                  $iconClass = 'bi-file-earmark';

                  if (in_array($ext, ['pdf'])) {
                      $badgeClass = 'ext-pdf';
                      $iconClass = 'bi-file-earmark-pdf-fill';
                  } elseif (in_array($ext, ['doc', 'docx'])) {
                      $badgeClass = 'ext-doc';
                      $iconClass = 'bi-file-earmark-word-fill';
                  } elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) {
                      $badgeClass = 'ext-xls';
                      $iconClass = 'bi-file-earmark-excel-fill';
                  } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                      $badgeClass = 'ext-img';
                      $iconClass = 'bi-file-earmark-image-fill';
                  } elseif (in_array($ext, ['zip', 'rar', '7z'])) {
                      $badgeClass = 'ext-zip';
                      $iconClass = 'bi-file-earmark-zip-fill';
                  }

                  $dBytes = $doc->tamano ?? 0;
                  if ($dBytes >= 1048576) {
                      $dSize = number_format($dBytes / 1048576, 2) . ' MB';
                  } elseif ($dBytes >= 1024) {
                      $dSize = number_format($dBytes / 1024, 1) . ' KB';
                  } else {
                      $dSize = $dBytes . ' B';
                  }

                  $fileExists = $doc->existe_fisicamente;
                  $isViewable = in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'txt']);
                @endphp
                <tr class="file-item-filter" data-name="{{ strtolower($doc->nombre_original . ' ' . $doc->descripcion) }}" data-ext="{{ $ext }}">
                  <td>
                    <div class="d-flex align-items-center" style="gap: 0.6rem;">
                      <div class="doc-file-badge-icon {{ $badgeClass }}" style="width: 32px; height: 32px; font-size: 1.1rem;">
                        <i class="bi {{ $iconClass }}"></i>
                      </div>
                      <div>
                        <div class="font-weight-600" style="color: var(--text-primary);">{{ $doc->nombre_original }}</div>
                        @if($doc->descripcion)
                          <div class="text-muted small" style="font-size: 0.72rem;">{{ $doc->descripcion }}</div>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td>
                    @if($doc->categoria)
                      <span class="doc-file-cat">{{ $doc->categoria->nombre }}</span>
                    @else
                      <span class="text-muted small">Raíz</span>
                    @endif
                  </td>
                  <td>
                    @if($fileExists)
                      <span class="badge badge-success" style="font-size: 0.68rem; font-weight: 500;">Disponible</span>
                    @else
                      <span class="badge-missing-disk">No en disco</span>
                    @endif
                  </td>
                  <td><span class="badge badge-secondary" style="font-size: 0.72rem; text-transform: uppercase;">{{ $ext }}</span></td>
                  <td>{{ $dSize }}</td>
                  <td>{{ $doc->created_at ? $doc->created_at->format('d/m/Y H:i') : '-' }}</td>
                  <td class="text-right">
                    <div class="d-inline-flex" style="gap: 0.3rem;">
                      @if($fileExists)
                        @if($isViewable)
                          <button type="button" class="btn btn-xs btn-outline-primary btn-preview-doc" 
                                  data-id="{{ $doc->id }}" 
                                  data-name="{{ $doc->nombre_original }}" 
                                  data-ext="{{ $ext }}"
                                  data-url="{{ route('documentacion.view', $doc->id) }}"
                                  title="Ver Vista Previa">
                            <i class="bi bi-eye"></i>
                          </button>
                        @endif

                        <a href="{{ route('documentacion.download', $doc->id) }}" class="btn btn-xs btn-outline-secondary" title="Descargar">
                          <i class="bi bi-download"></i>
                        </a>
                      @else
                        <button type="button" class="btn btn-xs btn-warning btn-trigger-replace" 
                                data-id="{{ $doc->id }}" 
                                data-name="{{ $doc->nombre_original }}" 
                                title="Re-subir archivo físico">
                          <i class="bi bi-cloud-arrow-up-fill"></i> Re-subir
                        </button>
                      @endif

                      <form action="{{ route('documentacion.destroy', $doc->id) }}" method="POST" class="d-inline form-delete-doc">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-xs btn-outline-danger btn-confirm-delete-doc" title="Eliminar">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif
  </div>

</div>

<!-- ========================================================================= -->
<!-- MODALES DEL SISTEMA DE DOCUMENTACIÓN -->
<!-- ========================================================================= -->

<!-- 1. Modal Subir Archivos -->
<div class="modal fade" id="modalSubirArchivos" tabindex="-1" role="dialog" aria-labelledby="modalSubirArchivosLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-md); box-shadow: var(--shadow-lg);">
      <div class="modal-header" style="border-bottom-color: var(--border-color);">
        <h5 class="modal-title font-weight-bold" id="modalSubirArchivosLabel">
          <i class="bi bi-cloud-arrow-up text-primary mr-1"></i> Subir Archivos
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: var(--text-primary);">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="{{ route('documentacion.upload') }}" method="POST" enctype="multipart/form-data" id="formUploadDocs">
        @csrf
        <div class="modal-body">
          <!-- Selección de Carpeta Destino -->
          <div class="form-group">
            <label class="font-weight-600 small" style="color: var(--text-primary);">Carpeta de Destino</label>
            <select name="categoria_id" class="form-control form-control-sm" style="background: var(--bg-subtle); border-color: var(--border-color); color: var(--text-primary);">
              <option value="">-- Carpeta Raíz (Sin Categoría) --</option>
              @foreach($todasCategorias as $catOpt)
                <option value="{{ $catOpt->id }}" {{ ($categoriaActual && $categoriaActual->id == $catOpt->id) ? 'selected' : '' }}>
                  📁 {{ $catOpt->nombre }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- O crear nueva carpeta -->
          <div class="form-group mb-3">
            <label class="font-weight-600 small" style="color: var(--text-primary);">O Crear Nueva Carpeta:</label>
            <input type="text" name="nueva_categoria" class="form-control form-control-sm" placeholder="Nombre de nueva carpeta (opcional)" style="background: var(--bg-subtle); border-color: var(--border-color); color: var(--text-primary);">
          </div>

          <!-- Dropzone para Archivos -->
          <div class="form-group mb-3">
            <label class="font-weight-600 small" style="color: var(--text-primary);">Seleccionar Archivos</label>
            <div class="dropzone-area" id="dropzoneBox" onclick="document.getElementById('inputArchivos').click()">
              <i class="bi bi-cloud-upload dropzone-icon"></i>
              <div class="font-weight-600" style="color: var(--text-primary); font-size: 0.9rem;">Arrastra tus archivos aquí o haz clic para explorar</div>
              <div class="text-muted small mt-1">Soporta PDF, Word, Excel, Imágenes, Comprimidos (Máx. 20MB por archivo)</div>
              <input type="file" name="archivos[]" id="inputArchivos" multiple class="d-none" required>
            </div>

            <!-- Lista previa de archivos seleccionados -->
            <div class="file-preview-list d-none" id="filePreviewList"></div>
          </div>

          <!-- Descripción Opcional -->
          <div class="form-group mb-0">
            <label class="font-weight-600 small" style="color: var(--text-primary);">Descripción o Notas (Opcional)</label>
            <input type="text" name="descripcion" class="form-control form-control-sm" placeholder="Ej. Informe mensual correspondiente a Mayo" style="background: var(--bg-subtle); border-color: var(--border-color); color: var(--text-primary);">
          </div>
        </div>

        <div class="modal-footer" style="border-top-color: var(--border-color);">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sm btn-primary" id="btnSubmitUpload">
            <i class="bi bi-upload mr-1"></i> Subir Archivos
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 2. Modal Nueva Carpeta -->
<div class="modal fade" id="modalNuevaCarpeta" tabindex="-1" role="dialog" aria-labelledby="modalNuevaCarpetaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-md); box-shadow: var(--shadow-lg);">
      <div class="modal-header" style="border-bottom-color: var(--border-color);">
        <h5 class="modal-title font-weight-bold" id="modalNuevaCarpetaLabel">
          <i class="bi bi-folder-plus text-primary mr-1"></i> {{ $categoriaActual ? 'Nueva Subcarpeta' : 'Nueva Carpeta' }}
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: var(--text-primary);">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form action="{{ route('documentacion.storeFolder') }}" method="POST">
        @csrf
        @if($categoriaActual)
          <input type="hidden" name="parent_id" value="{{ $categoriaActual->id }}">
        @endif

        <div class="modal-body">
          @if($categoriaActual)
            <div class="alert alert-info py-2 px-3 small mb-3" style="background: var(--color-primary-light); color: var(--color-primary); border: 1px solid rgba(var(--color-primary-rgb), 0.3);">
              <i class="bi bi-info-circle mr-1"></i> La subcarpeta se creará dentro de: <strong>{{ $categoriaActual->nombre }}</strong>
            </div>
          @endif

          <div class="form-group">
            <label class="font-weight-600 small" style="color: var(--text-primary);">Nombre de la Carpeta</label>
            <input type="text" name="nombre" class="form-control form-control-sm" placeholder="Ej. Informes Mensuales 2026" required autofocus style="background: var(--bg-subtle); border-color: var(--border-color); color: var(--text-primary);">
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-600 small d-block" style="color: var(--text-primary);">Color de la Carpeta</label>
            <div class="color-swatch-group">
              <label class="color-swatch-label" title="Azul Principal">
                <input type="radio" name="color" value="primary" checked>
                <span class="color-swatch" style="background: #4d7cfe;"><i class="bi bi-check check-icon"></i></span>
              </label>
              <label class="color-swatch-label" title="Verde">
                <input type="radio" name="color" value="success">
                <span class="color-swatch" style="background: #22c55e;"><i class="bi bi-check check-icon"></i></span>
              </label>
              <label class="color-swatch-label" title="Naranja / Amarillo">
                <input type="radio" name="color" value="warning">
                <span class="color-swatch" style="background: #f59e0b;"><i class="bi bi-check check-icon"></i></span>
              </label>
              <label class="color-swatch-label" title="Rojo">
                <input type="radio" name="color" value="danger">
                <span class="color-swatch" style="background: #ef4444;"><i class="bi bi-check check-icon"></i></span>
              </label>
              <label class="color-swatch-label" title="Púrpura">
                <input type="radio" name="color" value="purple">
                <span class="color-swatch" style="background: #8b5cf6;"><i class="bi bi-check check-icon"></i></span>
              </label>
              <label class="color-swatch-label" title="Cyan / Info">
                <input type="radio" name="color" value="info">
                <span class="color-swatch" style="background: #06b6d4;"><i class="bi bi-check check-icon"></i></span>
              </label>
              <label class="color-swatch-label" title="Teal / Esmeralda">
                <input type="radio" name="color" value="teal">
                <span class="color-swatch" style="background: #14b8a6;"><i class="bi bi-check check-icon"></i></span>
              </label>
            </div>
          </div>
        </div>

        <div class="modal-footer" style="border-top-color: var(--border-color);">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sm btn-primary">
            <i class="bi bi-folder-check mr-1"></i> Guardar Carpeta
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 3. Modal Editar Carpeta -->
<div class="modal fade" id="modalEditarCarpeta" tabindex="-1" role="dialog" aria-labelledby="modalEditarCarpetaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-md); box-shadow: var(--shadow-lg);">
      <div class="modal-header" style="border-bottom-color: var(--border-color);">
        <h5 class="modal-title font-weight-bold" id="modalEditarCarpetaLabel">
          <i class="bi bi-pencil-square text-warning mr-1"></i> Editar Carpeta
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: var(--text-primary);">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="formEditarCarpeta" action="" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="form-group">
            <label class="font-weight-600 small" style="color: var(--text-primary);">Nombre de la Carpeta</label>
            <input type="text" name="nombre" id="editFolderNombre" class="form-control form-control-sm" required style="background: var(--bg-subtle); border-color: var(--border-color); color: var(--text-primary);">
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-600 small d-block" style="color: var(--text-primary);">Color de la Carpeta</label>
            <div class="color-swatch-group" id="editFolderColorSwatches">
              <label class="color-swatch-label" title="Azul Principal">
                <input type="radio" name="color" value="primary">
                <span class="color-swatch" style="background: #4d7cfe;"><i class="bi bi-check check-icon"></i></span>
              </label>
              <label class="color-swatch-label" title="Verde">
                <input type="radio" name="color" value="success">
                <span class="color-swatch" style="background: #22c55e;"><i class="bi bi-check check-icon"></i></span>
              </label>
              <label class="color-swatch-label" title="Naranja">
                <input type="radio" name="color" value="warning">
                <span class="color-swatch" style="background: #f59e0b;"><i class="bi bi-check check-icon"></i></span>
              </label>
              <label class="color-swatch-label" title="Rojo">
                <input type="radio" name="color" value="danger">
                <span class="color-swatch" style="background: #ef4444;"><i class="bi bi-check check-icon"></i></span>
              </label>
              <label class="color-swatch-label" title="Púrpura">
                <input type="radio" name="color" value="purple">
                <span class="color-swatch" style="background: #8b5cf6;"><i class="bi bi-check check-icon"></i></span>
              </label>
              <label class="color-swatch-label" title="Cyan / Info">
                <input type="radio" name="color" value="info">
                <span class="color-swatch" style="background: #06b6d4;"><i class="bi bi-check check-icon"></i></span>
              </label>
              <label class="color-swatch-label" title="Teal / Esmeralda">
                <input type="radio" name="color" value="teal">
                <span class="color-swatch" style="background: #14b8a6;"><i class="bi bi-check check-icon"></i></span>
              </label>
            </div>
          </div>
        </div>

        <div class="modal-footer" style="border-top-color: var(--border-color);">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sm btn-primary">
            <i class="bi bi-check2-circle mr-1"></i> Actualizar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 4. Modal Reemplazar / Subir Archivo Individual Faltante -->
<div class="modal fade" id="modalReplaceDoc" tabindex="-1" role="dialog" aria-labelledby="modalReplaceDocLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-md); box-shadow: var(--shadow-lg);">
      <div class="modal-header" style="border-bottom-color: var(--border-color);">
        <h5 class="modal-title font-weight-bold" id="modalReplaceDocLabel">
          <i class="bi bi-cloud-arrow-up-fill text-warning mr-1"></i> Re-subir / Vincular Archivo Físico
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: var(--text-primary);">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="formReplaceDoc" action="" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="alert alert-warning py-2 px-3 small mb-3" style="background: var(--color-warning-light); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.3);">
            <i class="bi bi-info-circle mr-1"></i> Selecciona el archivo en tu computadora para restaurar: <br>
            <strong id="replaceDocFileName" class="d-block mt-1 text-truncate"></strong>
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-600 small" style="color: var(--text-primary);">Seleccionar Archivo</label>
            <input type="file" name="archivo" class="form-control-file" required style="color: var(--text-primary);">
          </div>
        </div>

        <div class="modal-footer" style="border-top-color: var(--border-color);">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sm btn-warning font-weight-600">
            <i class="bi bi-upload mr-1"></i> Guardar y Vincular
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 5. Modal Vista Previa de Documentos (PDF / Imágenes) -->
<div class="modal fade" id="modalPreviewDoc" tabindex="-1" role="dialog" aria-labelledby="modalPreviewDocLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-md); box-shadow: var(--shadow-xl); overflow: hidden;">
      <div class="modal-header py-2 px-3" style="border-bottom-color: var(--border-color); background: var(--bg-surface);">
        <div class="d-flex align-items-center text-truncate" style="max-width: 80%;">
          <i class="bi bi-file-earmark-text text-primary mr-2" style="font-size: 1.2rem;"></i>
          <span class="modal-title font-weight-bold text-truncate" id="previewDocTitle" style="font-size: 0.95rem;"></span>
        </div>
        <div class="d-flex align-items-center" style="gap: 0.4rem;">
          <a href="#" id="previewDownloadBtn" class="btn btn-xs btn-outline-primary" style="font-size: 0.75rem;" title="Descargar">
            <i class="bi bi-download mr-1"></i> Descargar
          </a>
          <a href="#" id="previewOpenTabBtn" target="_blank" class="btn btn-xs btn-outline-secondary" style="font-size: 0.75rem;" title="Abrir en pestaña nueva">
            <i class="bi bi-box-arrow-up-right"></i>
          </a>
          <button type="button" class="close ml-2" data-dismiss="modal" aria-label="Close" style="color: var(--text-primary);">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>

      <div class="preview-modal-body" id="previewModalBody">
        <!-- Renderizado dinámico vía JavaScript -->
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {

  // -------------------------------------------------------------
  // 1. FILTRADO Y BÚSQUEDA EN TIEMPO REAL
  // -------------------------------------------------------------
  $('#docSearch').on('input', function() {
    const q = $(this).val().toLowerCase().trim();
    let visibleDocs = 0;

    // Filtrar carpetas
    $('.folder-item-filter').each(function() {
      const name = $(this).data('name') || '';
      if (!q || name.includes(q)) {
        $(this).removeClass('d-none');
      } else {
        $(this).addClass('d-none');
      }
    });

    // Filtrar archivos en Grid y Tabla
    $('.file-item-filter').each(function() {
      const name = $(this).data('name') || '';
      const ext = $(this).data('ext') || '';
      if (!q || name.includes(q) || ext.includes(q)) {
        $(this).removeClass('d-none');
        visibleDocs++;
      } else {
        $(this).addClass('d-none');
      }
    });

    $('#docCountBadge').text(visibleDocs);
  });

  // -------------------------------------------------------------
  // 2. ALTERNANCIA DE VISTAS (GRID VS TABLA)
  // -------------------------------------------------------------
  $('#btnViewGrid').on('click', function() {
    $(this).addClass('active');
    $('#btnViewList').removeClass('active');
    $('#filesGridContainer').removeClass('d-none');
    $('#filesTableContainer').addClass('d-none');
    localStorage.setItem('doc_view_mode', 'grid');
  });

  $('#btnViewList').on('click', function() {
    $(this).addClass('active');
    $('#btnViewGrid').removeClass('active');
    $('#filesGridContainer').addClass('d-none');
    $('#filesTableContainer').removeClass('d-none');
    localStorage.setItem('doc_view_mode', 'list');
  });

  // Restaurar preferencia de vista
  const savedView = localStorage.getItem('doc_view_mode');
  if (savedView === 'list') {
    $('#btnViewList').trigger('click');
  }

  // -------------------------------------------------------------
  // 3. DROPZONE & PREVISUALIZACIÓN DE ARCHIVOS A SUBIR
  // -------------------------------------------------------------
  const dropzone = $('#dropzoneBox');
  const fileInput = $('#inputArchivos');
  const previewList = $('#filePreviewList');

  dropzone.on('dragover dragenter', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).addClass('dragover');
  });

  dropzone.on('dragleave dragend drop', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).removeClass('dragover');
  });

  dropzone.on('drop', function(e) {
    const files = e.originalEvent.dataTransfer.files;
    if (files.length > 0) {
      fileInput[0].files = files;
      updateFilePreview(files);
    }
  });

  fileInput.on('change', function() {
    updateFilePreview(this.files);
  });

  function updateFilePreview(files) {
    previewList.empty();
    if (!files || files.length === 0) {
      previewList.addClass('d-none');
      return;
    }

    previewList.removeClass('d-none');
    for (let i = 0; i < files.length; i++) {
      const file = files[i];
      const sizeFormatted = (file.size / 1024 / 1024).toFixed(2) + ' MB';
      const item = $(`
        <div class="file-preview-item">
          <div class="d-flex align-items-center text-truncate mr-2">
            <i class="bi bi-file-earmark-check mr-2 text-primary"></i>
            <span class="text-truncate">${file.name}</span>
          </div>
          <span class="text-muted small">${sizeFormatted}</span>
        </div>
      `);
      previewList.append(item);
    }
  }

  // Spinner en formulario de subida
  $('#formUploadDocs').on('submit', function() {
    const btn = $('#btnSubmitUpload');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span> Subiendo archivos...');
  });

  // -------------------------------------------------------------
  // 4. EDICIÓN DE CARPETAS
  // -------------------------------------------------------------
  $('.btn-edit-folder').on('click', function() {
    const id = $(this).data('id');
    const nombre = $(this).data('nombre');
    const color = $(this).data('color') || 'primary';

    $('#editFolderNombre').val(nombre);
    $('#editFolderColorSwatches input[value="' + color + '"]').prop('checked', true);

    const updateUrl = "{{ url('documentacion/carpetas') }}/" + id;
    $('#formEditarCarpeta').attr('action', updateUrl);

    $('#modalEditarCarpeta').modal('show');
  });

  // -------------------------------------------------------------
  // 5. RE-SUBIR / VINCULAR ARCHIVO INDIVIDUAL
  // -------------------------------------------------------------
  $('.btn-trigger-replace').on('click', function() {
    const docId = $(this).data('id');
    const docName = $(this).data('name');
    const replaceUrl = "{{ url('documentacion/reemplazar') }}/" + docId;

    $('#replaceDocFileName').text(docName);
    $('#formReplaceDoc').attr('action', replaceUrl);
    $('#modalReplaceDoc').modal('show');
  });

  // -------------------------------------------------------------
  // 6. CONFIRMACIONES SWEETALERT2
  // -------------------------------------------------------------
  $('.btn-confirm-delete-folder').on('click', function(e) {
    e.preventDefault();
    const docCount = parseInt($(this).data('count') || 0);
    const form = $(this).closest('form');

    if (docCount > 0) {
      Swal.fire({
        icon: 'error',
        title: 'Carpeta con archivos',
        text: 'Esta carpeta contiene ' + docCount + ' archivo(s). Debe eliminar o mover los archivos antes de borrar la carpeta.',
        confirmButtonColor: '#4d7cfe'
      });
      return;
    }

    Swal.fire({
      title: '¿Eliminar carpeta?',
      text: 'Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });

  $('.btn-confirm-delete-doc').on('click', function(e) {
    e.preventDefault();
    const form = $(this).closest('form');

    Swal.fire({
      title: '¿Eliminar documento?',
      text: 'El archivo será eliminado permanentemente del sistema.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#ef4444',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });

  // -------------------------------------------------------------
  // 7. VISOR / PREVISUALIZACIÓN DE DOCUMENTOS
  // -------------------------------------------------------------
  $('.btn-preview-doc').on('click', function() {
    const docId = $(this).data('id');
    const docName = $(this).data('name');
    const docExt = $(this).data('ext');
    const docUrl = $(this).data('url');
    const downloadUrl = "{{ url('documentacion/descargar') }}/" + docId;

    $('#previewDocTitle').text(docName);
    $('#previewDownloadBtn').attr('href', downloadUrl);
    $('#previewOpenTabBtn').attr('href', docUrl);

    const body = $('#previewModalBody');
    body.empty();

    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(docExt)) {
      body.html(`
        <div class="preview-img-container">
          <img src="${docUrl}" alt="${docName}">
        </div>
      `);
    } else if (docExt === 'pdf') {
      body.html(`
        <iframe src="${docUrl}" class="preview-iframe" title="${docName}"></iframe>
      `);
    } else {
      body.html(`
        <iframe src="${docUrl}" class="preview-iframe" title="${docName}"></iframe>
      `);
    }

    $('#modalPreviewDoc').modal('show');
  });

});
</script>
@endpush
