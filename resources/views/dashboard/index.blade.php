@extends('layouts.app')

@section('title', 'Panel de Control - Estadísticas 1.7')

@push('styles')
<style>
  .app-content {
    padding: 0.55rem 1.15rem 0.35rem !important;
    height: calc(100vh - var(--navbar-height) - var(--footer-height)) !important;
    max-height: calc(100vh - var(--navbar-height) - var(--footer-height)) !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
  }

  .dashboard-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 0.55rem;
    margin-bottom: 0.65rem;
    flex-shrink: 0;
  }

  .kpi-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm, 6px);
    padding: 0.5rem 0.75rem;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: transform var(--transition-fast), box-shadow var(--transition-fast), border-color var(--transition-fast);
  }

  .kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: rgba(var(--color-primary-rgb, 77, 124, 254), 0.4);
  }

  .kpi-card.kpi-gradient-se {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff;
    border-color: #3b82f6;
  }

  [data-theme="dark"] .kpi-card.kpi-gradient-se {
    background: linear-gradient(135deg, #1e40af, #1e3a8a);
    border-color: #3b82f6;
  }

  .kpi-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.15rem;
  }

  .kpi-title {
    font-size: 0.64rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
  }

  .kpi-card.kpi-gradient-se .kpi-title {
    color: rgba(255, 255, 255, 0.88);
  }

  .kpi-icon-wrap {
    width: 22px;
    height: 22px;
    border-radius: var(--radius-xs, 4px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
  }

  .kpi-icon-primary { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
  .kpi-icon-success { background: rgba(16, 185, 129, 0.15); color: #10b981; }
  .kpi-icon-info    { background: rgba(14, 165, 233, 0.15); color: #0ea5e9; }
  .kpi-icon-warning { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
  .kpi-icon-purple  { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }

  .kpi-value {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.15;
    margin-bottom: 0.1rem;
    font-family: var(--font-sans);
  }

  .kpi-card.kpi-gradient-se .kpi-value {
    color: #ffffff;
    font-size: 1.4rem;
  }

  .kpi-footer {
    font-size: 0.65rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .kpi-card.kpi-gradient-se .kpi-footer {
    color: rgba(255, 255, 255, 0.85);
  }

  /* Interactive Period Switcher Buttons */
  .chart-period-btn-group {
    display: flex;
    background: var(--bg-surface-hover);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xs, 4px);
    padding: 2px;
    gap: 2px;
  }

  .chart-period-btn {
    border: none;
    background: transparent;
    color: var(--text-muted);
    font-size: 0.68rem;
    font-weight: 600;
    padding: 0.15rem 0.45rem;
    border-radius: var(--radius-xs, 3px);
    cursor: pointer;
    transition: all var(--transition-fast, 0.15s ease);
  }

  .chart-period-btn:hover {
    color: var(--text-primary);
    background: rgba(var(--color-primary-rgb, 77, 124, 254), 0.1);
  }

  .chart-period-btn.active {
    background: var(--color-primary);
    color: #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
  }

  /* Horizontal Scroll for Chart */
  .chart-horizontal-scroll {
    flex: 1;
    min-height: 0;
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: thin;
    scrollbar-color: var(--border-color) transparent;
  }

  .chart-horizontal-scroll::-webkit-scrollbar {
    height: 4px;
  }

  .chart-horizontal-scroll::-webkit-scrollbar-thumb {
    background-color: var(--border-color);
    border-radius: 4px;
  }

  .chart-inner-canvas {
    height: 100%;
    min-width: 100%;
    transition: min-width 0.2s ease;
  }

  /* Table Customizations for Dark/Light Mode */
  .dashboard-activity-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.78rem;
  }

  .dashboard-activity-table thead th {
    background: var(--bg-surface-hover);
    color: var(--text-muted);
    font-weight: 700;
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.55rem 0.85rem;
    border-bottom: 1px solid var(--border-color);
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .dashboard-activity-table tbody td {
    padding: 0.45rem 0.85rem;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-secondary);
    vertical-align: middle;
  }

  .dashboard-activity-table tbody tr:hover td {
    background: var(--bg-surface-hover);
    color: var(--text-primary);
  }

  .medico-avatar {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-primary), #6366f1);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.68rem;
    font-weight: 700;
    flex-shrink: 0;
    margin-right: 0.5rem;
  }

  .table-scroll-container {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--border-color) transparent;
  }

  .table-scroll-container::-webkit-scrollbar {
    width: 5px;
  }

  .table-scroll-container::-webkit-scrollbar-thumb {
    background-color: var(--border-color);
    border-radius: 4px;
  }
</style>
@endpush

@section('content')
<!-- Page Header & Shortcuts Banner (Compact) -->
<div class="page-header" style="margin-bottom: 0.55rem; flex-shrink: 0;">
  <div>
    <h1 class="page-title" style="font-size: 1.15rem; margin-bottom: 0; display: flex; align-items: center; gap: 0.45rem;">
      <i class="bi bi-house-door-fill text-primary" style="font-size: 1.15rem;"></i>
      Panel de Control & Estadísticas
    </h1>
    <div style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.78rem; color: var(--text-muted); margin-top: 0.15rem;">
      <span style="font-weight: 700; font-size: 0.68rem; letter-spacing: 0.05em; text-transform: uppercase; color: var(--color-primary); background: var(--color-primary-light); padding: 0.1rem 0.45rem; border-radius: var(--radius-xs);">USTED ESTÁ AQUÍ</span>
      <span>App</span>
      <i class="bi bi-chevron-right" style="font-size: 0.65rem;"></i>
      <span style="color: var(--text-primary); font-weight: 600;">Panel Principal</span>
      <span style="font-size: 0.74rem; color: var(--text-muted); margin-left: 0.4rem;">• Año: <strong>{{ $anioAct }}</strong> (Mes: <strong>{{ $mesTexto }}</strong>)</span>
    </div>
  </div>
  <div class="page-actions" style="display: flex; gap: 0.45rem; flex-wrap: wrap;">
    <a href="{{ route('registrosat1') }}" class="btn btn-primary btn-sm" style="height: 28px; padding: 0 0.65rem; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;">
      <i class="bi bi-table"></i> Registros AT1
    </a>
    <a href="{{ route('informesat1') }}" class="btn btn-subtle btn-sm" style="height: 28px; padding: 0 0.65rem; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;">
      <i class="bi bi-file-earmark-bar-graph-fill text-primary"></i> Informes AT1
    </a>
    <a href="{{ route('dashboard-epi') }}" class="btn btn-subtle btn-sm" style="height: 28px; padding: 0 0.65rem; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;">
      <i class="bi bi-shield-shaded text-danger"></i> Vigilancia Epid.
    </a>
  </div>
</div>

<!-- =========================================================================
     1. FILA DE TARJETAS KPI (COMPACTAS)
     ========================================================================= -->
<div class="dashboard-kpi-grid">
  <!-- KPI 1: Semana Epidemiológica -->
  <div class="kpi-card kpi-gradient-se">
    <div class="kpi-card-header">
      <span class="kpi-title">Semana Epi</span>
      <div style="opacity: 0.85; font-size: 1.05rem;"><i class="bi bi-calendar3-week"></i></div>
    </div>
    <div class="kpi-value">SE {{ $seActual }}</div>
    <div class="kpi-footer">
      <span>Año {{ $anioAct }}</span>
    </div>
  </div>

  <!-- KPI 2: Total Año -->
  <div class="kpi-card" style="border-top: 3px solid #3b82f6;">
    <div class="kpi-card-header">
      <span class="kpi-title" style="color: #3b82f6;">Total Año {{ $anioAct }}</span>
      <div class="kpi-icon-wrap kpi-icon-primary">
        <i class="bi bi-calendar-check-fill"></i>
      </div>
    </div>
    <div class="kpi-value">{{ number_format($totalAnio) }}</div>
    <div class="kpi-footer">
      <span>Registros anuales</span>
    </div>
  </div>

  <!-- KPI 3: Total Mes Actual -->
  <div class="kpi-card" style="border-top: 3px solid #10b981;">
    <div class="kpi-card-header">
      <span class="kpi-title" style="color: #10b981;">Mes ({{ $mesTexto }})</span>
      <div class="kpi-icon-wrap kpi-icon-success">
        <i class="bi bi-bar-chart-fill"></i>
      </div>
    </div>
    <div class="kpi-value">{{ number_format($totalMes) }}</div>
    <div class="kpi-footer">
      <span>Registros mes actual</span>
    </div>
  </div>

  <!-- KPI 4: Total Semana Actual -->
  <div class="kpi-card" style="border-top: 3px solid #0ea5e9;">
    <div class="kpi-card-header">
      <span class="kpi-title" style="color: #0ea5e9;">Esta Semana (SE {{ $seActual }})</span>
      <div class="kpi-icon-wrap kpi-icon-info">
        <i class="bi bi-layers-fill"></i>
      </div>
    </div>
    <div class="kpi-value">{{ number_format($totalSemana) }}</div>
    <div class="kpi-footer">
      <span>Atenciones SE activa</span>
    </div>
  </div>

  <!-- KPI 5: Nuevos vs Subsecuentes -->
  <div class="kpi-card" style="border-top: 3px solid #f59e0b;">
    <div class="kpi-card-header">
      <span class="kpi-title" style="color: #f59e0b;">Nuevos (Mes)</span>
      <div class="kpi-icon-wrap kpi-icon-warning">
        <i class="bi bi-person-plus-fill"></i>
      </div>
    </div>
    <div class="kpi-value">{{ number_format($nuevos) }}</div>
    <div class="kpi-footer">
      <span>Subsec.: <strong class="text-primary">{{ number_format($subsec) }}</strong></span>
    </div>
  </div>

  <!-- KPI 6: Médicos Activos -->
  <div class="kpi-card" style="border-top: 3px solid #8b5cf6;">
    <div class="kpi-card-header">
      <span class="kpi-title" style="color: #8b5cf6;">Médicos Activos</span>
      <div class="kpi-icon-wrap kpi-icon-purple">
        <i class="bi bi-people-fill"></i>
      </div>
    </div>
    <div class="kpi-value">{{ number_format($medicosActivosCount) }}</div>
    <div class="kpi-footer">
      <span>Profesionales en mes</span>
    </div>
  </div>
</div>

<!-- =========================================================================
     2. SECCIÓN PRINCIPAL: 2 GRÁFICOS INTERACTIVOS (IZQUIERDA) Y TABLA (DERECHA)
     ========================================================================= -->
<div class="grid grid-cols-12" style="flex: 1; min-height: 0; margin-bottom: 0; gap: 0.85rem; align-items: stretch;">
  
  <!-- COLUMNA IZQUIERDA: 2 GRÁFICOS INTERACTIVOS (5 Columnas) -->
  <div class="col-5" style="display: flex; flex-direction: column; gap: 0.65rem; height: 100%; min-height: 0;">
    
    <!-- Gráfico 1: Evolución Interactiva con Scroll Horizontal -->
    <div class="sing-card" style="padding: 0.65rem 0.85rem; flex: 1.15; min-height: 0; display: flex; flex-direction: column;">
      <div class="card-header" style="padding: 0 0 0.45rem 0; margin-bottom: 0.35rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; flex-wrap: wrap; gap: 0.4rem;">
        <div style="display: flex; align-items: center; gap: 0.45rem;">
          <div style="width: 22px; height: 22px; border-radius: var(--radius-xs); background: rgba(var(--color-primary-rgb), 0.15); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">
            <i class="bi bi-graph-up-arrow"></i>
          </div>
          <div>
            <h3 id="chartActiveTitle" class="card-title" style="font-size: 0.8rem; font-weight: 700; margin-bottom: 0;">Evolución Mensual ({{ $anioAct }})</h3>
          </div>
        </div>

        <!-- Selector Interactivo de Períodos: Días, Semanas, Meses, Años -->
        <div class="chart-period-btn-group">
          <button type="button" class="chart-period-btn" data-period="dias" title="Desplazamiento horizontal por días">Días</button>
          <button type="button" class="chart-period-btn" data-period="semanas" title="Semanas epidemiológicas">Semanas</button>
          <button type="button" class="chart-period-btn active" data-period="meses" title="Meses del año actual">Meses</button>
          <button type="button" class="chart-period-btn" data-period="anios" title="Histórico por años">Años</button>
        </div>
      </div>

      <!-- Contenedor con Scroll Horizontal para Gráficos Extensos -->
      <div id="chartScrollWrap" class="chart-horizontal-scroll">
        <div id="chartInnerContainer" class="chart-inner-canvas">
          <div id="monthlyEvolutionChart" style="height: 100%; width: 100%;"></div>
        </div>
      </div>
    </div>

    <!-- Gráfico 2: Donut Condición de Pacientes -->
    <div class="sing-card" style="padding: 0.65rem 0.85rem; flex: 0.85; min-height: 0; display: flex; flex-direction: column;">
      <div class="card-header" style="padding: 0 0 0.45rem 0; margin-bottom: 0.35rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
        <div style="display: flex; align-items: center; gap: 0.45rem;">
          <div style="width: 22px; height: 22px; border-radius: var(--radius-xs); background: rgba(16, 185, 129, 0.15); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">
            <i class="bi bi-pie-chart-fill"></i>
          </div>
          <div>
            <h3 class="card-title" style="font-size: 0.82rem; font-weight: 700; margin-bottom: 0;">Condición de Atención</h3>
          </div>
        </div>
        <span style="font-size: 0.68rem; color: var(--text-muted);">Mes Actual</span>
      </div>
      <div style="flex: 1; min-height: 0; display: flex; align-items: center; justify-content: center;">
        <div id="patientConditionChart" style="width: 100%;"></div>
      </div>
      <div style="display: flex; justify-content: space-around; padding-top: 0.4rem; border-top: 1px solid var(--border-color); font-size: 0.72rem; flex-shrink: 0;">
        <div style="text-align: center;">
          <span style="display: block; color: var(--text-muted); font-size: 0.64rem;">Nuevos</span>
          <strong style="color: #3b82f6;">{{ number_format($nuevos) }}</strong>
        </div>
        <div style="text-align: center;">
          <span style="display: block; color: var(--text-muted); font-size: 0.64rem;">Subsecuentes</span>
          <strong style="color: #10b981;">{{ number_format($subsec) }}</strong>
        </div>
        <div style="text-align: center;">
          <span style="display: block; color: var(--text-muted); font-size: 0.64rem;">Total Mes</span>
          <strong style="color: var(--text-primary);">{{ number_format($totalMes) }}</strong>
        </div>
      </div>
    </div>
  </div>

  <!-- COLUMNA DERECHA: TABLA DE ACTIVIDAD RECIENTE (7 Columnas con Scroll Interno) -->
  <div class="col-7" style="height: 100%; min-height: 0;">
    <div class="sing-card" style="padding: 0; overflow: hidden; height: 100%; display: flex; flex-direction: column;">
      <div class="card-header" style="padding: 0.65rem 1rem; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
        <div style="display: flex; align-items: center; gap: 0.45rem;">
          <div style="width: 26px; height: 26px; border-radius: var(--radius-sm); background: linear-gradient(135deg, var(--color-primary), #6366f1); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.82rem;">
            <i class="bi bi-clock-history"></i>
          </div>
          <div>
            <h3 class="card-title" style="font-size: 0.86rem; font-weight: 700; margin-bottom: 0;">Actividad Reciente por Médico</h3>
            <span style="font-size: 0.68rem; color: var(--text-muted);">Últimos registros ingresados</span>
          </div>
        </div>
        <span class="badge badge-subtle-primary" style="padding: 0.25rem 0.5rem; font-size: 0.68rem;">
          <i class="bi bi-activity"></i> {{ $registrosRecientes->count() }} lotes
        </span>
      </div>

      <div class="table-scroll-container">
        @if($registrosRecientes->isEmpty())
          <div style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
            <i class="bi bi-inbox" style="font-size: 1.8rem; display: block; margin-bottom: 0.4rem; opacity: 0.5;"></i>
            <strong>Sin registros recientes en la base de datos</strong>
          </div>
        @else
          <table class="dashboard-activity-table">
            <thead>
              <tr>
                <th>Médico / Profesional</th>
                <th style="text-align: center;">Usuario</th>
                <th style="text-align: center;">Fecha</th>
                <th style="text-align: center;">SE</th>
                <th style="text-align: center;">Día</th>
                <th style="text-align: center;">Mes</th>
                <th style="text-align: center;">AT1</th>
              </tr>
            </thead>
            <tbody>
              @foreach($registrosRecientes as $reg)
                <tr>
                  <td>
                    <div style="display: flex; align-items: center;">
                      <div class="medico-avatar">
                        {{ strtoupper(substr($reg->medico ?? 'M', 0, 2)) }}
                      </div>
                      <span style="font-weight: 600; color: var(--text-primary); font-size: 0.76rem;">{{ $reg->medico ?? 'Sin nombre' }}</span>
                    </div>
                  </td>
                  <td style="text-align: center; font-size: 0.72rem; color: var(--text-muted);">
                    @php
                      $key = ($reg->medico ?? '') . '|' . $reg->fecha;
                      echo $usuariosData[$key]->usuarios ?? '<span style="opacity:0.6;">-</span>';
                    @endphp
                  </td>
                  <td style="text-align: center; font-size: 0.74rem; font-weight: 500;">
                    {{ $reg->fecha_formateada ?? $reg->fecha }}
                  </td>
                  <td style="text-align: center;">
                    <span class="badge badge-subtle-info" style="font-size: 0.68rem; padding: 0.15rem 0.35rem;">SE {{ $reg->se }}</span>
                  </td>
                  <td style="text-align: center;">
                    <span class="badge badge-subtle-primary" style="font-weight: 700; font-size: 0.72rem; padding: 0.15rem 0.45rem;">
                      {{ $reg->total_dia }}
                    </span>
                  </td>
                  <td style="text-align: center;">
                    <span class="badge badge-subtle-success" style="font-weight: 700; font-size: 0.72rem; padding: 0.15rem 0.45rem;">
                      {{ $totalMedicoMes[$reg->medico ?? ''] ?? 0 }}
                    </span>
                  </td>
                  <td style="text-align: center;">
                    <a href="{{ route('ingresos.detalles-medico', ['fecha' => $reg->fecha, 'medico' => $reg->medico]) }}"
                       class="btn btn-subtle btn-sm btn-icon"
                       style="width: 24px; height: 24px; font-size: 0.7rem;"
                       title="Ver atenciones de {{ $reg->medico }} — {{ $reg->fecha }}">
                      <i class="bi bi-arrow-up-right"></i>
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.body.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.07)' : 'rgba(0, 0, 0, 0.06)';

    // Datasets interactivos para Días, Semanas, Meses y Años
    const chartDatasets = @json($chartDatasets);
    let currentPeriod = 'meses';

    const defaultDataset = chartDatasets[currentPeriod] || chartDatasets['meses'];
    const scrollWrap = document.getElementById('chartScrollWrap');
    const innerContainer = document.getElementById('chartInnerContainer');

    // 1. Gráfico de Evolución Principal (Interactivo con Scroll Horizontal)
    const evolutionOptions = {
      series: [{
        name: 'Atenciones Registradas',
        data: defaultDataset.data
      }],
      chart: {
        type: 'area',
        height: '100%',
        width: '100%',
        toolbar: { show: false },
        fontFamily: 'Inter, sans-serif',
        background: 'transparent',
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 350
        }
      },
      colors: ['#3b82f6'],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.4,
          opacityTo: 0.04,
          stops: [0, 95, 100]
        }
      },
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 2.5 },
      xaxis: {
        categories: defaultDataset.categories,
        labels: { 
          style: { colors: textColor, fontSize: '10px' },
          rotate: -45,
          rotateAlways: false,
          hideOverlappingLabels: true
        },
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      yaxis: {
        labels: {
          style: { colors: textColor, fontSize: '10px' },
          formatter: val => val >= 1000 ? (val / 1000).toFixed(0) + 'k' : val
        }
      },
      grid: { borderColor: gridColor, strokeDashArray: 3, padding: { top: 0, bottom: 0, left: 10, right: 10 } },
      tooltip: {
        theme: isDark ? 'dark' : 'light',
        y: { formatter: val => Number(val).toLocaleString() + ' atenciones' }
      }
    };

    const evolutionChart = new ApexCharts(document.querySelector("#monthlyEvolutionChart"), evolutionOptions);
    evolutionChart.render();

    // Manejador de botones interactivos (Días / Semanas / Meses / Años)
    const periodButtons = document.querySelectorAll('.chart-period-btn');
    const titleElem = document.getElementById('chartActiveTitle');

    periodButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        const period = this.getAttribute('data-period');
        if (period === currentPeriod || !chartDatasets[period]) return;

        periodButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        currentPeriod = period;

        const ds = chartDatasets[period];
        if (titleElem) {
          titleElem.textContent = ds.title;
        }

        let newWidth = '100%';
        let rotateAngle = -45;

        if (period === 'dias') {
          // Espacio holgado de 48px por cada día para que las fechas no se amontonen
          const calculatedW = Math.max(scrollWrap.clientWidth, ds.categories.length * 48);
          newWidth = calculatedW + 'px';
          if (innerContainer) innerContainer.style.minWidth = newWidth;
        } else if (period === 'semanas' && ds.categories.length > 25) {
          const calculatedW = Math.max(scrollWrap.clientWidth, ds.categories.length * 36);
          newWidth = calculatedW + 'px';
          if (innerContainer) innerContainer.style.minWidth = newWidth;
        } else {
          newWidth = '100%';
          rotateAngle = 0;
          if (innerContainer) innerContainer.style.minWidth = '100%';
          if (scrollWrap) scrollWrap.scrollLeft = 0;
        }

        // Actualizar datos del gráfico y ancho explícito de ApexCharts
        evolutionChart.updateOptions({
          chart: {
            width: newWidth
          },
          xaxis: {
            categories: ds.categories,
            labels: {
              rotate: rotateAngle,
              hideOverlappingLabels: true
            }
          },
          series: [{
            name: 'Atenciones Registradas',
            data: ds.data
          }]
        });

        // Auto-desplazar al extremo derecho si es Días
        if (period === 'dias' && scrollWrap) {
          setTimeout(() => {
            scrollWrap.scrollLeft = scrollWrap.scrollWidth;
          }, 150);
        }
      });
    });

    // 2. Gráfico Donut de Pacientes Nuevos vs Subsecuentes
    const nuevosVal = {{ (int)$nuevos }};
    const subsecVal = {{ (int)$subsec }};

    const conditionOptions = {
      series: [nuevosVal > 0 || subsecVal > 0 ? nuevosVal : 1, nuevosVal > 0 || subsecVal > 0 ? subsecVal : 1],
      labels: ['Nuevos', 'Subsecuentes'],
      chart: {
        type: 'donut',
        height: 125,
        fontFamily: 'Inter, sans-serif',
        background: 'transparent'
      },
      colors: ['#3b82f6', '#10b981'],
      stroke: { width: 2, colors: [isDark ? '#1e293b' : '#ffffff'] },
      dataLabels: { enabled: false },
      legend: { show: false },
      tooltip: {
        theme: isDark ? 'dark' : 'light',
        y: { formatter: val => Number(val).toLocaleString() + ' pacientes' }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '72%',
            labels: {
              show: true,
              total: {
                show: true,
                label: 'Total',
                color: textColor,
                fontSize: '11px',
                formatter: () => '{{ number_format($totalMes) }}'
              },
              value: {
                color: isDark ? '#ffffff' : '#0f172a',
                fontSize: '14px',
                fontWeight: 700
              }
            }
          }
        }
      }
    };

    const conditionChart = new ApexCharts(document.querySelector("#patientConditionChart"), conditionOptions);
    conditionChart.render();

    // Re-render charts on theme toggle
    window.addEventListener('themeChanged', function(e) {
      const dark = e.detail && e.detail.theme === 'dark';
      const tc = dark ? '#94a3b8' : '#64748b';
      const gc = dark ? 'rgba(255, 255, 255, 0.07)' : 'rgba(0, 0, 0, 0.06)';

      evolutionChart.updateOptions({
        xaxis: { labels: { style: { colors: tc } } },
        yaxis: { labels: { style: { colors: tc } } },
        grid: { borderColor: gc },
        tooltip: { theme: dark ? 'dark' : 'light' }
      });

      conditionChart.updateOptions({
        stroke: { colors: [dark ? '#1e293b' : '#ffffff'] },
        tooltip: { theme: dark ? 'dark' : 'light' }
      });
    });
  });
</script>
@endpush
