@extends('layouts.app')

@section('title', 'Visitas & Audiencia Global')

@section('content')
<!-- Page Header & Breadcrumbs -->
<div class="page-header">
  <div>
    <h1 class="page-title">Visitas & Audiencia</h1>
    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.84rem; color: var(--text-muted); margin-top: 0.25rem;">
      <span style="font-weight: 700; font-size: 0.72rem; letter-spacing: 0.06em; text-transform: uppercase; color: var(--color-primary); background: var(--color-primary-light); padding: 0.15rem 0.5rem; border-radius: var(--radius-xs);">USTED ESTÁ AQUÍ</span>
      <span>App</span>
      <i class="bi bi-chevron-right" style="font-size: 0.7rem;"></i>
      <span>Principal</span>
      <i class="bi bi-chevron-right" style="font-size: 0.7rem;"></i>
      <span style="color: var(--text-primary); font-weight: 600;">Visitas</span>
    </div>
  </div>
  <div class="page-actions">
    <button class="btn btn-outline-secondary btn-sm" onclick="SingTheme.toggle()">
      <i class="bi bi-moon-stars"></i> Cambiar Modo
    </button>
    <button class="btn btn-primary btn-sm" onclick="SingApp.toast({title: 'Filtro de Audiencia', message: 'Rango de fechas aplicado con éxito.', type: 'info'})">
      <i class="bi bi-calendar-event"></i> Filtrar Período
    </button>
  </div>
</div>

<!-- 1. Top Stat Row -->
<div class="grid grid-cols-12" style="margin-bottom: 1.5rem;">
  <div class="col-4">
    <div class="sing-card stat-card">
      <div class="stat-header">
        <span class="stat-title">Usuarios Únicos (Mes)</span>
        <div class="stat-icon primary"><i class="bi bi-person-check-fill"></i></div>
      </div>
      <div class="stat-value">342,120</div>
      <div class="stat-footer">
        <span class="stat-trend positive"><i class="bi bi-arrow-up-right"></i> +12.4%</span>
        <span class="stat-caption">nuevos visitantes</span>
      </div>
    </div>
  </div>

  <div class="col-4">
    <div class="sing-card stat-card">
      <div class="stat-header">
        <span class="stat-title">Duración Promedio de Sesión</span>
        <div class="stat-icon success"><i class="bi bi-stopwatch-fill"></i></div>
      </div>
      <div class="stat-value">4m 38s</div>
      <div class="stat-footer">
        <span class="stat-trend positive"><i class="bi bi-arrow-up-right"></i> +0m 42s</span>
        <span class="stat-caption">mayor retención</span>
      </div>
    </div>
  </div>

  <div class="col-4">
    <div class="sing-card stat-card">
      <div class="stat-header">
        <span class="stat-title">Porcentaje de Rebote</span>
        <div class="stat-icon warning"><i class="bi bi-arrow-left-right"></i></div>
      </div>
      <div class="stat-value">28.4%</div>
      <div class="stat-footer">
        <span class="stat-trend positive"><i class="bi bi-arrow-down-right"></i> -3.2%</span>
        <span class="stat-caption">mejora de conversión</span>
      </div>
    </div>
  </div>
</div>

<!-- 2. Geographic Distribution & Browser Breakdown -->
<div class="grid grid-cols-12" style="margin-bottom: 1.5rem;">
  <!-- Countries / Geography -->
  <div class="col-7">
    <div class="sing-card">
      <div class="card-header">
        <div>
          <h2 class="card-title"><i class="bi bi-geo-alt-fill text-danger"></i> Visitas por País / Región</h2>
          <div class="card-subtitle">Principales territorios con mayor volumen de tráfico</div>
        </div>
        <div class="card-actions">
          <button class="card-action-btn" data-action="reload"><i class="bi bi-arrow-clockwise"></i></button>
        </div>
      </div>
      <div class="card-body">
        <!-- Country 1: Estados Unidos -->
        <div style="margin-bottom: 1.2rem;">
          <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 0.35rem;">
            <span style="font-weight: 600;">🇺🇸 Estados Unidos</span>
            <span style="color: var(--text-muted);">142,500 visitas (41.6%)</span>
          </div>
          <div style="height: 8px; background: var(--border-color); border-radius: var(--radius-full); overflow: hidden;">
            <div style="width: 41.6%; height: 100%; background: var(--color-primary); border-radius: var(--radius-full);"></div>
          </div>
        </div>

        <!-- Country 2: España -->
        <div style="margin-bottom: 1.2rem;">
          <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 0.35rem;">
            <span style="font-weight: 600;">🇪🇸 España</span>
            <span style="color: var(--text-muted);">74,200 visitas (21.7%)</span>
          </div>
          <div style="height: 8px; background: var(--border-color); border-radius: var(--radius-full); overflow: hidden;">
            <div style="width: 21.7%; height: 100%; background: var(--color-success); border-radius: var(--radius-full);"></div>
          </div>
        </div>

        <!-- Country 3: México -->
        <div style="margin-bottom: 1.2rem;">
          <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 0.35rem;">
            <span style="font-weight: 600;">🇲🇽 México</span>
            <span style="color: var(--text-muted);">52,900 visitas (15.4%)</span>
          </div>
          <div style="height: 8px; background: var(--border-color); border-radius: var(--radius-full); overflow: hidden;">
            <div style="width: 15.4%; height: 100%; background: var(--color-warning); border-radius: var(--radius-full);"></div>
          </div>
        </div>

        <!-- Country 4: Colombia -->
        <div style="margin-bottom: 1.2rem;">
          <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 0.35rem;">
            <span style="font-weight: 600;">🇨🇴 Colombia</span>
            <span style="color: var(--text-muted);">38,100 visitas (11.1%)</span>
          </div>
          <div style="height: 8px; background: var(--border-color); border-radius: var(--radius-full); overflow: hidden;">
            <div style="width: 11.1%; height: 100%; background: var(--color-purple); border-radius: var(--radius-full);"></div>
          </div>
        </div>

        <!-- Country 5: Otros -->
        <div>
          <div style="display: flex; justify-content: space-between; font-size: 0.88rem; margin-bottom: 0.35rem;">
            <span style="font-weight: 600;">🌐 Resto del Mundo</span>
            <span style="color: var(--text-muted);">34,420 visitas (10.2%)</span>
          </div>
          <div style="height: 8px; background: var(--border-color); border-radius: var(--radius-full); overflow: hidden;">
            <div style="width: 10.2%; height: 100%; background: var(--text-muted); border-radius: var(--radius-full);"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Browsers & Devices Breakdown -->
  <div class="col-5">
    <div class="sing-card" style="height: 100%;">
      <div class="card-header">
        <div>
          <h2 class="card-title"><i class="bi bi-laptop text-primary"></i> Navegadores & Dispositivos</h2>
          <div class="card-subtitle">Distribución técnica de clientes</div>
        </div>
        <div class="card-actions">
          <button class="card-action-btn" data-action="reload"><i class="bi bi-arrow-clockwise"></i></button>
        </div>
      </div>
      <div class="card-body">
        <div style="display: flex; justify-content: space-around; margin-bottom: 1.5rem; text-align: center;">
          <div>
            <div style="font-size: 1.4rem; font-weight: 700; color: var(--color-primary);">64%</div>
            <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="bi bi-display"></i> Escritorio</div>
          </div>
          <div>
            <div style="font-size: 1.4rem; font-weight: 700; color: var(--color-success);">31%</div>
            <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="bi bi-phone"></i> Móvil</div>
          </div>
          <div>
            <div style="font-size: 1.4rem; font-weight: 700; color: var(--color-warning);">5%</div>
            <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="bi bi-tablet"></i> Tablet</div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="sing-table">
            <thead>
              <tr>
                <th>Navegador</th>
                <th>Sesiones</th>
                <th>Cuota</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><i class="bi bi-browser-chrome text-warning" style="margin-right: 0.4rem;"></i> Google Chrome</td>
                <td>218,900</td>
                <td><span class="badge badge-soft-primary">64%</span></td>
              </tr>
              <tr>
                <td><i class="bi bi-browser-safari text-info" style="margin-right: 0.4rem;"></i> Apple Safari</td>
                <td>68,400</td>
                <td><span class="badge badge-soft-info">20%</span></td>
              </tr>
              <tr>
                <td><i class="bi bi-browser-firefox text-danger" style="margin-right: 0.4rem;"></i> Mozilla Firefox</td>
                <td>34,200</td>
                <td><span class="badge badge-soft-danger">10%</span></td>
              </tr>
              <tr>
                <td><i class="bi bi-browser-edge text-primary" style="margin-right: 0.4rem;"></i> Microsoft Edge</td>
                <td>20,620</td>
                <td><span class="badge badge-soft-success">6%</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
