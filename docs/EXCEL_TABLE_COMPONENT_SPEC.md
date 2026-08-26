# Especificación y Guía de Reutilización: Componente Sing Excel Table

Este documento contiene la arquitectura completa, tokens de diseño, paleta de colores (Modo Claro & Oscuro), lógica JavaScript y plantillas HTML para **reutilizar la tabla interactiva tipo Excel en cualquier vista del proyecto**.

---

## 1. Archivos Modulares del Componente

| Recurso | Ruta | Descripción |
|---|---|---|
| **Estilos CSS** | [`public/assets/css/sing-excel-table.css`](file:///E:/Templates%20Laravel/estadisticas-1.7.1-main/public/assets/css/sing-excel-table.css) | Estilos completos: pantalla completa, sticky headers, filtros en cascada, date tree y scrollbars. |
| **Motor JS** | [`public/assets/js/sing-excel-table.js`](file:///E:/Templates%20Laravel/estadisticas-1.7.1-main/public/assets/js/sing-excel-table.js) | Motor progresivo en memoria: carga por chunks de 200, ordenamiento, búsqueda, filtros aditivos y exportación `.xlsx`. |

---

## 2. Variables CSS & Paleta de Colores del Sistema

El componente respeta automáticamente las variables del tema de `sing-theme.css`:

```css
/* Modo Oscuro (Predeterminado) */
[data-theme="dark"] {
  --bg-body: #0b1120;
  --bg-surface: #0f172a;
  --bg-surface-hover: #1e293b;
  --bg-surface-elevated: #161f36;
  --text-primary: #f8fafc;
  --text-secondary: #94a3b8;
  --text-muted: #64748b;
  --border-color: #1e293b;
  --input-bg: #0b1120;
  --input-border: #334155;
  --color-primary: #4d7cfe;
}

/* Modo Claro */
[data-theme="light"] {
  --bg-body: #f1f5f9;
  --bg-surface: #ffffff;
  --bg-surface-hover: #f8fafc;
  --bg-surface-elevated: #f1f5f9;
  --text-primary: #0f172a;
  --text-secondary: #475569;
  --text-muted: #94a3b8;
  --border-color: #e2e8f0;
  --input-bg: #ffffff;
  --input-border: #cbd5e1;
  --color-primary: #4d7cfe;
}
```

---

## 3. Cómo Implementar la Tabla en una Nueva Vista Blade

### Paso 1: En el Controlador Laravel (`Controller.php`)
Serializa la colección de datos a una matriz 2D compacta (un array de arrays de valores de celdas):

```php
public function miReporte(Request $request)
{
    $registros = MiModelo::query()
        ->limit(10000)
        ->get()
        ->map(function ($row) {
            return [
                $row->id,
                $row->campo1,
                $row->fecha ? Carbon::parse($row->fecha)->format('Y-m-d') : '',
                $row->total,
                // ... demas columnas
            ];
        });

    return view('reports.mi_reporte', compact('registros'));
}
```

---

### Paso 2: En la Vista Blade (`mi_reporte.blade.php`)

```html
@extends('layouts.app')

@section('title', 'Mi Reporte Excel')

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/sing-excel-table.css') }}">
@endpush

@section('content')
<div class="sing-card-excel-fullscreen">
  <!-- 1. Header Toolbar -->
  <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; padding: 0.45rem 0.85rem; border-bottom: 1px solid var(--border-color);">
    <!-- Título -->
    <div style="display: flex; align-items: center; gap: 0.55rem;">
      <div style="width: 28px; height: 28px; border-radius: var(--radius-xs, 4px); background: linear-gradient(135deg, var(--color-primary), #6366f1); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
        <i class="bi bi-table"></i>
      </div>
      <div>
        <h2 class="card-title" style="font-size: 0.92rem; margin-bottom: 0; font-weight: 700; color: var(--text-primary);">
          Mi Reporte Excel
        </h2>
      </div>
    </div>

    <!-- Toolbar Acciones -->
    <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
      <!-- Búsqueda Rápida Global -->
      <div style="position: relative; width: 200px;">
        <i class="bi bi-search" style="position: absolute; left: 0.65rem; top: 0.52rem; color: var(--text-muted); font-size: 0.78rem;"></i>
        <input type="text" id="excelGlobalSearch" class="form-control form-control-sm" style="padding-left: 1.85rem; height: 32px; font-size: 0.82rem;" placeholder="Buscar en tabla...">
      </div>

      <!-- Botón Limpiar Filtros -->
      <button type="button" class="btn btn-toolbar-clear btn-sm" id="btnResetAllExcelFilters" style="display: none; height: 32px;">
        <i class="bi bi-funnel-fill"></i> Limpiar
      </button>

      <!-- Botón Exportar XLSX -->
      <button type="button" class="btn btn-toolbar-xlsx btn-sm" id="btnExportExcelXLSX" style="height: 32px;" title="Exportar a Excel (.xlsx)">
        <i class="bi bi-file-earmark-spreadsheet-fill"></i> XLSX
      </button>

      <!-- Botón Pantalla Completa -->
      <button type="button" class="btn btn-toolbar-reset btn-sm btn-icon" data-action="fullscreen" title="Pantalla Completa" style="height: 32px; width: 32px;">
        <i class="bi bi-fullscreen"></i>
      </button>
    </div>
  </div>

  <!-- 2. Contenedor de Tabla con Scroll -->
  <div class="excel-table-scroll">
    <table class="sing-table-excel" id="miTablaExcel">
      <thead>
        <tr>
          <th class="col-row-num">#</th>
          <th data-col="0" data-title="Campo 1">
            <div class="excel-th-content">
              <span>Campo 1</span>
              <button type="button" class="excel-filter-btn"><i class="bi bi-caret-down-fill"></i></button>
            </div>
          </th>
          <th data-col="1" data-title="Fecha" data-type="date">
            <div class="excel-th-content">
              <span>Fecha</span>
              <button type="button" class="excel-filter-btn"><i class="bi bi-caret-down-fill"></i></button>
            </div>
          </th>
          <th data-col="2" data-title="Total">
            <div class="excel-th-content">
              <span>Total</span>
              <button type="button" class="excel-filter-btn"><i class="bi bi-caret-down-fill"></i></button>
            </div>
          </th>
        </tr>
      </thead>
      <tbody>
        <!-- Las filas se renderizan progresivamente vía JS -->
      </tbody>
    </table>
  </div>

  <!-- 3. Footer / Barra de Estado -->
  <div class="table-excel-footer">
    <div class="table-excel-footer-left">
      <span style="font-weight: 600;">Total: <strong id="excelStatTotal">0</strong></span>
      <span id="excelStatFiltered" class="text-primary font-weight-bold"></span>
      <span id="excelProgressiveBadge" class="badge badge-subtle-info" style="display:none;">
        <span id="excelProgressiveText"></span>
      </span>
      <span id="excelActiveFiltersBadge" class="badge badge-subtle-primary" style="display:none;">
        <i class="bi bi-funnel-fill"></i> <span id="excelActiveFiltersCount">0</span> filtro(s) activo(s)
      </span>
    </div>
  </div>
</div>

<!-- Dataset JSON Embebido -->
<script id="miDataJson" type="application/json">@json($registros)</script>
@endsection

@push('scripts')
  <!-- SheetJS para exportar a XLSX -->
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
  <!-- Motor Sing Excel Table -->
  <script src="{{ asset('assets/js/sing-excel-table.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      window.miExcel = new SingExcelTable('miTablaExcel', {
        dataScriptId: 'miDataJson',
        storagePrefix: 'sing_mi_reporte',
        exportSheetName: 'Reporte',
        exportFileName: 'mi_reporte_datos',
        chunkSize: 200
      });
    });
  </script>
@endpush
```

---

## 4. Opciones Disponibles en `SingExcelTable`

| Opción | Tipo | Predeterminado | Descripción |
|---|---|---|---|
| `tableId` *(requerido)* | `string` | — | ID de la etiqueta `<table>`. |
| `dataScriptId` | `string` | `'registrosDataJson'` | ID del `<script type="application/json">` que contiene el array de datos. |
| `storagePrefix` | `string` | `'sing_excel_' + tableId` | Prefijo de clave para guardar filtros y búsqueda en `localStorage`. |
| `exportSheetName` | `string` | `'Datos'` | Nombre de la pestaña en el archivo descargado de Excel. |
| `exportFileName` | `string` | `'export_excel'` | Nombre base del archivo `.xlsx` (se le agrega la fecha actual). |
| `chunkSize` | `number` | `200` | Número de registros que se insertan en el DOM por cada lote de scroll. |

---

## 5. Características Clave Integradas

1. **Scroll Infinito Progresivo**:
   - Carga 200 filas en ~1ms al deslizarse hacia el final de la tabla, permitiendo consultar decenas de miles de registros sin congelar la interfaz.
2. **Filtros en Cascada & Date Tree**:
   - Agrupación automática jerárquica para columnas con `data-type="date"` (*Año > Mes > Día*).
   - Conteo de frecuencias de cada valor distinto en tiempo real.
3. **Filtros Aditivos ("Agregar selección al filtro actual")**:
   - Permite buscar un término, seleccionarlo y luego buscar otro término para sumarlo a la vista sin borrar el anterior.
4. **Exportación XLSX Instantánea**:
   - Utiliza SheetJS en el navegador. Respeta exactamente las columnas y filas filtradas, calcula anchos de columna automáticos y descarga el archivo `.xlsx` en < 1 segundo.
5. **Compatibilidad Nativa con Pantalla Completa**:
   - Posicionamiento `position: fixed` relativo a la ventana para que los popovers y modales se mantengan en la capa superior (*Top Layer*) del navegador.
