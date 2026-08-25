@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@section('content')
<!-- Page Header & Flatlogic Breadcrumb Banner -->
<div class="page-header">
  <div>
    <h1 class="page-title">Analytics</h1>
    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.84rem; color: var(--text-muted); margin-top: 0.25rem;">
      <span style="font-weight: 700; font-size: 0.72rem; letter-spacing: 0.06em; text-transform: uppercase; color: var(--color-primary); background: var(--color-primary-light); padding: 0.15rem 0.5rem; border-radius: var(--radius-xs);">USTED ESTÁ AQUÍ</span>
      <span>App</span>
      <i class="bi bi-chevron-right" style="font-size: 0.7rem;"></i>
      <span>Principal</span>
      <i class="bi bi-chevron-right" style="font-size: 0.7rem;"></i>
      <span style="color: var(--text-primary); font-weight: 600;">Analytics</span>
    </div>
  </div>
  <div class="page-actions">
    <!-- Period Switcher Buttons -->
    <div style="display: flex; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 2px;">
      <button type="button" class="btn btn-sm btn-subtle" data-chart-period="daily" style="border: none; border-radius: var(--radius-xs);">Diario</button>
      <button type="button" class="btn btn-sm btn-subtle" data-chart-period="weekly" style="border: none; border-radius: var(--radius-xs);">Semanal</button>
      <button type="button" class="btn btn-sm btn-primary active" data-chart-period="monthly" style="border: none; border-radius: var(--radius-xs);">Mensual</button>
    </div>
    <button class="btn btn-gradient-primary btn-sm" onclick="SingApp.toast({title: 'Reporte', message: 'Descargando reporte analítico en PDF...', type: 'primary'})">
      <i class="bi bi-download"></i> Exportar
    </button>
  </div>
</div>

<!-- 1. Top KPI Stats Cards Grid (Sing App Signature Cards) -->
<div class="grid grid-cols-12" style="margin-bottom: 1.5rem;">
  <!-- Card 1: Visitas -->
  <div class="col-3">
    <div class="sing-card stat-card">
      <div class="stat-header">
        <span class="stat-title">Visitas Totales</span>
        <div class="stat-icon primary">
          <i class="bi bi-globe2"></i>
        </div>
      </div>
      <div class="stat-value">482,900</div>
      <div class="stat-footer">
        <span class="stat-trend positive">
          <i class="bi bi-arrow-up-right"></i> +8.3%
        </span>
        <span class="stat-caption">vs período anterior</span>
      </div>
      <div id="sparklineVisits" class="stat-sparkline"></div>
    </div>
  </div>

  <!-- Card 2: Ingresos Acumulados -->
  <div class="col-3">
    <div class="sing-card stat-card">
      <div class="stat-header">
        <span class="stat-title">Ingresos Totales</span>
        <div class="stat-icon success">
          <i class="bi bi-currency-dollar"></i>
        </div>
      </div>
      <div class="stat-value">$128,450</div>
      <div class="stat-footer">
        <span class="stat-trend positive">
          <i class="bi bi-arrow-up-right"></i> +14.2%
        </span>
        <span class="stat-caption">meta mensual 92%</span>
      </div>
      <div id="sparklineRevenue" class="stat-sparkline"></div>
    </div>
  </div>

  <!-- Card 3: Descargas / Actividad -->
  <div class="col-3">
    <div class="sing-card stat-card">
      <div class="stat-header">
        <span class="stat-title">Descargas App</span>
        <div class="stat-icon warning">
          <i class="bi bi-cloud-arrow-down-fill"></i>
        </div>
      </div>
      <div class="stat-value">12,845</div>
      <div class="stat-footer">
        <span class="stat-trend positive">
          <i class="bi bi-arrow-up-right"></i> +4.6%
        </span>
        <span class="stat-caption">nuevos usuarios iOS/Android</span>
      </div>
      <div id="sparklineDownloads" class="stat-sparkline"></div>
    </div>
  </div>

  <!-- Card 4: Sesiones Activas -->
  <div class="col-3">
    <div class="sing-card stat-card">
      <div class="stat-header">
        <span class="stat-title">Sesiones Activas</span>
        <div class="stat-icon purple">
          <i class="bi bi-activity"></i>
        </div>
      </div>
      <div class="stat-value">1,492</div>
      <div class="stat-footer">
        <span class="stat-trend positive">
          <i class="bi bi-arrow-up-right"></i> +1.8%
        </span>
        <span class="stat-caption">en tiempo real</span>
      </div>
      <div id="sparklineActive" class="stat-sparkline"></div>
    </div>
  </div>
</div>

<!-- 2. Main Chart (Sing App Signature Area Analytics with Period Selector) -->
<div class="sing-card" style="margin-bottom: 1.5rem;">
  <div class="card-header">
    <div>
      <h2 class="card-title">
        <i class="bi bi-graph-up text-primary"></i> Gráfico Principal (Main Chart)
      </h2>
      <div class="card-subtitle">Monitoreo de tráfico, visitas e ingresos con escala temporal configurable</div>
    </div>
    <div class="card-actions">
      <button class="card-action-btn" data-action="reload" title="Recargar"><i class="bi bi-arrow-clockwise"></i></button>
      <button class="card-action-btn" data-action="collapse" title="Colapsar"><i class="bi bi-chevron-up"></i></button>
      <button class="card-action-btn" data-action="fullscreen" title="Pantalla Completa"><i class="bi bi-fullscreen"></i></button>
    </div>
  </div>
  <div class="card-body">
    <div id="mainAnalyticsChart"></div>
  </div>
</div>

<!-- 3. Traffic Channels & Server Load Row -->
<div class="grid grid-cols-12" style="margin-bottom: 1.5rem;">
  <!-- Traffic Sources Donut -->
  <div class="col-6">
    <div class="sing-card" style="height: 100%;">
      <div class="card-header">
        <div>
          <h2 class="card-title">
            <i class="bi bi-pie-chart-fill text-purple"></i> Canales de Adquisición
          </h2>
          <div class="card-subtitle">Fuentes de tráfico y conversión de visitantes</div>
        </div>
        <div class="card-actions">
          <button class="card-action-btn" data-action="reload" title="Recargar"><i class="bi bi-arrow-clockwise"></i></button>
        </div>
      </div>
      <div class="card-body" style="display: flex; align-items: center; justify-content: center;">
        <div id="trafficShareChart" style="width: 100%;"></div>
      </div>
    </div>
  </div>

  <!-- Server & System Resource Health -->
  <div class="col-6">
    <div class="sing-card" style="height: 100%;">
      <div class="card-header">
        <div>
          <h2 class="card-title">
            <i class="bi bi-cpu text-info"></i> Rendimiento del Sistema
          </h2>
          <div class="card-subtitle">Carga de infraestructura PHP / Apache y base de datos</div>
        </div>
        <div class="card-actions">
          <span class="badge badge-soft-success">En Línea</span>
        </div>
      </div>
      <div class="card-body" style="display: flex; flex-direction: column; justify-content: space-around;">
        <div style="margin-bottom: 1.25rem;">
          <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 0.4rem;">
            <span style="font-weight: 500;"><i class="bi bi-cpu-fill text-primary"></i> Procesador CPU (Cluster 8 Cores)</span>
            <span style="font-weight: 600; color: var(--color-primary);">34%</span>
          </div>
          <div style="height: 8px; background-color: var(--border-color); border-radius: var(--radius-full); overflow: hidden;">
            <div style="width: 34%; height: 100%; background: linear-gradient(90deg, var(--color-primary), #60a5fa); border-radius: var(--radius-full);"></div>
          </div>
        </div>

        <div style="margin-bottom: 1.25rem;">
          <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 0.4rem;">
            <span style="font-weight: 500;"><i class="bi bi-memory text-success"></i> Memoria RAM Asignada</span>
            <span style="font-weight: 600; color: var(--color-success);">58% (4.6 GB / 8 GB)</span>
          </div>
          <div style="height: 8px; background-color: var(--border-color); border-radius: var(--radius-full); overflow: hidden;">
            <div style="width: 58%; height: 100%; background: var(--color-success); border-radius: var(--radius-full);"></div>
          </div>
        </div>

        <div>
          <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 0.4rem;">
            <span style="font-weight: 500;"><i class="bi bi-hdd-network text-warning"></i> Almacenamiento NVMe SSD</span>
            <span style="font-weight: 600; color: var(--color-warning);">28% (140 GB / 500 GB)</span>
          </div>
          <div style="height: 8px; background-color: var(--border-color); border-radius: var(--radius-full); overflow: hidden;">
            <div style="width: 28%; height: 100%; background: var(--color-warning); border-radius: var(--radius-full);"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 4. Transactions Table & Tasks Widget Row -->
<div class="grid grid-cols-12">
  <!-- Data Table: Recent Transactions -->
  <div class="col-8">
    <div class="sing-card">
      <div class="card-header">
        <div>
          <h2 class="card-title">
            <i class="bi bi-receipt-cutoff text-success"></i> Órdenes y Transacciones Recientes
          </h2>
          <div class="card-subtitle">Registro de compras y cobros procesados en tiempo real</div>
        </div>
        <div class="card-actions">
          <a href="{{ route('tables') }}" class="btn btn-outline-primary btn-sm">Ver Todas</a>
          <button class="card-action-btn" data-action="reload" title="Recargar"><i class="bi bi-arrow-clockwise"></i></button>
        </div>
      </div>
      <div class="table-responsive">
        <table class="sing-table table-hover">
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Servicio / Producto</th>
              <th>Fecha</th>
              <th>Monto</th>
              <th>Estado</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div class="user-cell">
                  <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&auto=format&fit=crop&q=80" alt="Avatar">
                  <div>
                    <div class="user-cell-name">Elena Rostova</div>
                    <div class="user-cell-email">elena@example.com</div>
                  </div>
                </div>
              </td>
              <td>Plan Enterprise Sing</td>
              <td>24 Ago, 2026</td>
              <td><strong>$1,299.00</strong></td>
              <td><span class="badge badge-soft-success">Completado</span></td>
              <td>
                <button class="btn btn-subtle btn-sm btn-icon" title="Ver Detalle" onclick="SingApp.toast({title: 'Detalle', message: 'Orden #9214 abierta.', type: 'info'})">
                  <i class="bi bi-eye"></i>
                </button>
              </td>
            </tr>
            <tr>
              <td>
                <div class="user-cell">
                  <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80" alt="Avatar">
                  <div>
                    <div class="user-cell-name">Carlos Mendoza</div>
                    <div class="user-cell-email">carlos.m@example.com</div>
                  </div>
                </div>
              </td>
              <td>Licencia Dashboard Pro</td>
              <td>23 Ago, 2026</td>
              <td><strong>$349.00</strong></td>
              <td><span class="badge badge-soft-primary">Procesando</span></td>
              <td>
                <button class="btn btn-subtle btn-sm btn-icon" title="Ver Detalle" onclick="SingApp.toast({title: 'Detalle', message: 'Orden #9213 abierta.', type: 'info'})">
                  <i class="bi bi-eye"></i>
                </button>
              </td>
            </tr>
            <tr>
              <td>
                <div class="user-cell">
                  <img src="https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=100&auto=format&fit=crop&q=80" alt="Avatar">
                  <div>
                    <div class="user-cell-name">Mateo Benítez</div>
                    <div class="user-cell-email">mateo.b@example.com</div>
                  </div>
                </div>
              </td>
              <td>Módulo Soporte 24/7</td>
              <td>22 Ago, 2026</td>
              <td><strong>$199.00</strong></td>
              <td><span class="badge badge-soft-warning">Pendiente</span></td>
              <td>
                <button class="btn btn-subtle btn-sm btn-icon" title="Ver Detalle" onclick="SingApp.toast({title: 'Detalle', message: 'Orden #9212 abierta.', type: 'info'})">
                  <i class="bi bi-eye"></i>
                </button>
              </td>
            </tr>
            <tr>
              <td>
                <div class="user-cell">
                  <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80" alt="Avatar">
                  <div>
                    <div class="user-cell-name">Lucía Morales</div>
                    <div class="user-cell-email">lucia.m@example.com</div>
                  </div>
                </div>
              </td>
              <td>Upgrade Base de Datos</td>
              <td>21 Ago, 2026</td>
              <td><strong>$540.00</strong></td>
              <td><span class="badge badge-soft-success">Completado</span></td>
              <td>
                <button class="btn btn-subtle btn-sm btn-icon" title="Ver Detalle" onclick="SingApp.toast({title: 'Detalle', message: 'Orden #9211 abierta.', type: 'info'})">
                  <i class="bi bi-eye"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Right Column: Priority Task ToDo List -->
  <div class="col-4">
    <div class="sing-card" style="height: 100%;">
      <div class="card-header">
        <div>
          <h2 class="card-title">
            <i class="bi bi-check2-square text-warning"></i> Tareas & Prioridades
          </h2>
          <div class="card-subtitle">Actividades pendientes del equipo</div>
        </div>
        <div class="card-actions">
          <button class="card-action-btn" data-action="collapse"><i class="bi bi-chevron-up"></i></button>
        </div>
      </div>
      <div class="card-body">
        <ul class="todo-list">
          <li class="todo-item completed">
            <input type="checkbox" class="todo-checkbox" checked id="task1">
            <div class="todo-content">
              <label for="task1" class="todo-title">Adaptar Modo Oscuro a Laravel</label>
              <div class="todo-meta"><i class="bi bi-clock"></i> Terminado hace 10 min</div>
            </div>
          </li>
          <li class="todo-item">
            <input type="checkbox" class="todo-checkbox" id="task2">
            <div class="todo-content">
              <label for="task2" class="todo-title">Integrar ApexCharts con tokens CSS</label>
              <div class="todo-meta"><i class="bi bi-flag-fill text-danger"></i> Alta prioridad</div>
            </div>
          </li>
          <li class="todo-item">
            <input type="checkbox" class="todo-checkbox" id="task3">
            <div class="todo-content">
              <label for="task3" class="todo-title">Validar formularios y tablas dinámicas</label>
              <div class="todo-meta"><i class="bi bi-calendar"></i> Para mañana</div>
            </div>
          </li>
          <li class="todo-item">
            <input type="checkbox" class="todo-checkbox" id="task4">
            <div class="todo-content">
              <label for="task4" class="todo-title">Desplegar versión Laravel en XAMPP</label>
              <div class="todo-meta"><i class="bi bi-hdd-stack"></i> Producción</div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Main Area Chart with monthly default
    SingCharts.initMainAnalytics('mainAnalyticsChart', 'monthly');

    // Acquisition Donut Chart
    SingCharts.initMarketShare('trafficShareChart');

    // KPI Mini sparklines
    SingCharts.initSparkline('sparklineVisits', [30, 40, 35, 50, 49, 60, 70, 91, 125], '#4d7cfe');
    SingCharts.initSparkline('sparklineRevenue', [25, 30, 45, 38, 55, 60, 75, 80, 110], '#22c55e');
    SingCharts.initSparkline('sparklineDownloads', [10, 18, 14, 25, 22, 35, 42, 48, 55], '#f59e0b');
    SingCharts.initSparkline('sparklineActive', [45, 40, 35, 30, 32, 28, 35, 40, 49], '#8b5cf6');
  });
</script>
@endpush
