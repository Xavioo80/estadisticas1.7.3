@extends('layouts.app')

@section('title', 'Gráficos & Visualizaciones')

@section('content')
<!-- Page Header & Breadcrumbs -->
<div class="page-header">
  <div>
    <h1 class="page-title">Gráficos & Visualizaciones</h1>
    <ul class="page-breadcrumb">
      <li><a href="{{ route('dashboard') }}"><i class="bi bi-house-door"></i> Inicio</a></li>
      <li class="separator"><i class="bi bi-chevron-right"></i></li>
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li class="separator"><i class="bi bi-chevron-right"></i></li>
      <li class="active">Gráficos & KPIs</li>
    </ul>
  </div>
  <div class="page-actions">
    <button class="btn btn-outline-primary btn-sm" onclick="SingTheme.toggle()">
      <i class="bi bi-circle-half"></i> Probar Adaptación de Tema
    </button>
  </div>
</div>

<!-- Alert note about automatic dark mode sync in charts -->
<div class="alert alert-primary">
  <i class="bi bi-info-circle-fill" style="font-size: 1.2rem;"></i>
  <div>
    <strong>Integración Inteligente de Modo Oscuro:</strong> Los gráficos recalculan automáticamente sus cuadrículas, textos, leyendas y tooltips en tiempo real al alternar entre el tema claro y oscuro sin necesidad de recargar la página.
  </div>
</div>

<!-- Grid of Charts -->
<div class="grid grid-cols-12" style="margin-bottom: 1.5rem;">
  <!-- 1. Area Spline Chart -->
  <div class="col-8">
    <div class="sing-card">
      <div class="card-header">
        <div>
          <h2 class="card-title"><i class="bi bi-graph-up-arrow text-primary"></i> Ingresos vs Proyección Anual</h2>
          <div class="card-subtitle">Comparativa de ingresos reales frente a metas estimadas</div>
        </div>
        <div class="card-actions">
          <button class="card-action-btn" data-action="reload"><i class="bi bi-arrow-clockwise"></i></button>
          <button class="card-action-btn" data-action="collapse"><i class="bi bi-chevron-up"></i></button>
          <button class="card-action-btn" data-action="fullscreen"><i class="bi bi-fullscreen"></i></button>
        </div>
      </div>
      <div class="card-body">
        <div id="chartAreaRevenue"></div>
      </div>
    </div>
  </div>

  <!-- 2. Radial / Gauge Target Chart -->
  <div class="col-4">
    <div class="sing-card">
      <div class="card-header">
        <h2 class="card-title"><i class="bi bi-speedometer2 text-success"></i> Meta Mensual</h2>
        <div class="card-actions">
          <button class="card-action-btn" data-action="reload"><i class="bi bi-arrow-clockwise"></i></button>
        </div>
      </div>
      <div class="card-body" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
        <div id="chartRadialGoal" style="width: 100%;"></div>
        <div style="text-align: center; margin-top: -1rem;">
          <div style="font-size: 1.3rem; font-weight: 700; color: var(--text-primary);">$92,400 / $120,000</div>
          <div style="font-size: 0.8rem; color: var(--text-muted);">77% del objetivo mensual alcanzado</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="grid grid-cols-12">
  <!-- 3. Grouped Bar Chart -->
  <div class="col-6">
    <div class="sing-card">
      <div class="card-header">
        <h2 class="card-title"><i class="bi bi-bar-chart-fill text-purple"></i> Rendimiento por Departamentos</h2>
        <div class="card-actions">
          <button class="card-action-btn" data-action="collapse"><i class="bi bi-chevron-up"></i></button>
        </div>
      </div>
      <div class="card-body">
        <div id="chartBarPerformance"></div>
      </div>
    </div>
  </div>

  <!-- 4. Donut Chart Breakdown -->
  <div class="col-6">
    <div class="sing-card">
      <div class="card-header">
        <h2 class="card-title"><i class="bi bi-pie-chart-fill text-warning"></i> Distribución de Recursos del Servidor</h2>
        <div class="card-actions">
          <button class="card-action-btn" data-action="collapse"><i class="bi bi-chevron-up"></i></button>
        </div>
      </div>
      <div class="card-body" style="display: flex; align-items: center; justify-content: center;">
        <div id="chartDonutServer" style="width: 100%;"></div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // 1. Spline Area
    SingCharts.initMainAnalytics('chartAreaRevenue');

    // 2. Bar Chart
    SingCharts.initPerformanceBar('chartBarPerformance');

    // 3. Server Donut
    SingCharts.initMarketShare('chartDonutServer');

    // 4. Radial Goal Chart
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const radialOptions = {
      series: [77],
      chart: {
        height: 260,
        type: 'radialBar',
        background: 'transparent'
      },
      plotOptions: {
        radialBar: {
          hollow: { size: '65%' },
          track: {
            background: isDark ? '#1e293b' : '#e2e8f0',
            strokeWidth: '100%'
          },
          dataLabels: {
            name: { show: false },
            value: {
              fontSize: '28px',
              fontWeight: 700,
              color: isDark ? '#f8fafc' : '#1e293b',
              offsetY: 8,
              formatter: val => `${val}%`
            }
          }
        }
      },
      colors: ['#22c55e'],
      stroke: { lineCap: 'round' }
    };
    const radialChart = new ApexCharts(document.getElementById('chartRadialGoal'), radialOptions);
    radialChart.render();

    // Listen to theme changes for radial bar
    window.addEventListener('sing:theme-change', function(e) {
      const dark = e.detail.theme === 'dark';
      radialChart.updateOptions({
        plotOptions: {
          radialBar: {
            track: { background: dark ? '#1e293b' : '#e2e8f0' },
            dataLabels: {
              value: { color: dark ? '#f8fafc' : '#1e293b' }
            }
          }
        }
      });
    });
  });
</script>
@endpush
