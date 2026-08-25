@php
  $openSubmenus = [];
  if (isset($_COOKIE['sing_open_submenus'])) {
      $decoded = json_decode(urldecode($_COOKIE['sing_open_submenus']), true);
      if (is_array($decoded)) {
          $openSubmenus = $decoded;
      }
  } else {
      $openSubmenus = ['dashboard'];
  }
@endphp

<!-- Sidebar Navigation with Auto-Expand on Hover and Multi-Level Support -->
<aside class="app-sidebar {{ (isset($_COOKIE['sing_sidebar_hover']) && $_COOKIE['sing_sidebar_hover'] === 'true') ? 'is-expanded-hold' : '' }}" id="appSidebar">
  <!-- Brand Header -->
  <div class="sidebar-brand">
    <a href="{{ route('dashboard') }}" class="sidebar-logo">
      <div class="logo-icon">
        <i class="bi bi-bar-chart-fill"></i>
      </div>
      <div class="logo-text">
        Estadísticas <span>1.7</span>
      </div>
    </a>
  </div>

  <!-- Navigation Menu List -->
  <ul class="sidebar-menu">
    <!-- GRUPO 1: MENÚ PRINCIPAL -->
    <li class="sidebar-heading">Menú Principal</li>
    
    <!-- Dashboard con sub-items -->
    <li class="sidebar-item has-submenu {{ in_array('dashboard', $openSubmenus) ? 'open' : '' }}" data-submenu="dashboard" data-tooltip="Dashboard">
      <a href="javascript:void(0)" class="sidebar-link">
        <i class="bi bi-speedometer2 nav-icon"></i>
        <span class="nav-label">Dashboard</span>
        <i class="bi bi-chevron-right nav-arrow"></i>
      </a>
      <div class="sidebar-submenu-wrapper">
        <ul class="sidebar-submenu">
          <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="sidebar-link">
              <i class="bi bi-grid-1x2"></i> General
            </a>
          </li>
          <li class="{{ request()->routeIs('visits') ? 'active' : '' }}">
            <a href="{{ route('visits') }}" class="sidebar-link">
              <i class="bi bi-shield-check"></i> Dash. Vigilancia
            </a>
          </li>
          <li class="{{ request()->routeIs('charts') ? 'active' : '' }}">
            <a href="{{ route('charts') }}" class="sidebar-link">
              <i class="bi bi-activity"></i> Dash. Diagnósticos
            </a>
          </li>
        </ul>
      </div>
    </li>

    <li class="sidebar-item" data-tooltip="Notificación SVS">
      <a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Notificación SVS', message: 'Módulo de notificación epidemiológica SVS', type: 'info'});">
        <i class="bi bi-file-earmark-medical-fill nav-icon"></i>
        <span class="nav-label">Notificación SVS</span>
      </a>
    </li>

    <li class="sidebar-item" data-tooltip="Calendario Epi">
      <a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Calendario Epi', message: 'Calendario de semanas epidemiológicas activas', type: 'primary'});">
        <i class="bi bi-calendar3 nav-icon"></i>
        <span class="nav-label">Calendario Epi</span>
      </a>
    </li>

    <li class="sidebar-item {{ request()->routeIs('forms') ? 'active' : '' }}" data-tooltip="Ingreso de Datos">
      <a href="{{ route('forms') }}" class="sidebar-link">
        <i class="bi bi-pencil-square nav-icon"></i>
        <span class="nav-label">Ingreso de Datos</span>
      </a>
    </li>

    <!-- GRUPO 2: REPORTES Y SALIDA -->
    <li class="sidebar-heading">Reportes y Salida</li>

    <li class="sidebar-item {{ request()->routeIs('tables') ? 'active' : '' }}" data-tooltip="Registros AT1">
      <a href="{{ route('tables') }}" class="sidebar-link">
        <i class="bi bi-table nav-icon"></i>
        <span class="nav-label">Registros AT1</span>
        <span class="badge badge-soft-success nav-badge">AT1</span>
      </a>
    </li>

    <li class="sidebar-item" data-tooltip="Informes AT1">
      <a href="{{ route('charts') }}" class="sidebar-link">
        <i class="bi bi-bar-chart-line-fill nav-icon"></i>
        <span class="nav-label">Informes AT1</span>
      </a>
    </li>

    <li class="sidebar-item" data-tooltip="Documentación">
      <a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Documentación', message: 'Manuales y guías del sistema estadístico', type: 'info'});">
        <i class="bi bi-journal-text nav-icon"></i>
        <span class="nav-label">Documentación</span>
      </a>
    </li>

    <!-- Submenú de Informes Médicos y Epidemiológicos -->
    <li class="sidebar-item has-submenu {{ in_array('informes', $openSubmenus) ? 'open' : '' }}" data-submenu="informes" data-tooltip="Informes">
      <a href="javascript:void(0)" class="sidebar-link">
        <i class="bi bi-file-earmark-bar-graph-fill nav-icon"></i>
        <span class="nav-label">Informes</span>
        <i class="bi bi-chevron-right nav-arrow"></i>
      </a>
      <div class="sidebar-submenu-wrapper">
        <ul class="sidebar-submenu">
          <li><a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Atenciones', message: 'Reporte consolidado de atenciones', type: 'info'});"><i class="bi bi-person-check"></i> Atenciones</a></li>
          <li><a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'TB9', message: 'Reporte de programa de tuberculosis TB9', type: 'warning'});"><i class="bi bi-clipboard2-pulse"></i> TB9</a></li>
          <li><a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Implantes', message: 'Registro de implantes y procedimientos', type: 'primary'});"><i class="bi bi-bandaid"></i> Implantes</a></li>
          <li><a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'AT2-r N', message: 'Reporte de registros AT2-r N', type: 'info'});"><i class="bi bi-card-checklist"></i> AT2-r N</a></li>
          <li><a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Morbilidad', message: 'Informe de morbilidad general', type: 'danger'});"><i class="bi bi-virus"></i> Morbilidad</a></li>
          <li><a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'ITS', message: 'Reporte epidemiológico de ITS', type: 'warning'});"><i class="bi bi-shield-plus"></i> ITS</a></li>
          <li><a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Alerta Semanal', message: 'Vigilancia de alertas semanales', type: 'danger'});"><i class="bi bi-exclamation-triangle"></i> Alerta Semanal</a></li>
          <li><a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'TRANS-2', message: 'Reporte de transferencias TRANS-2', type: 'info'});"><i class="bi bi-activity"></i> TRANS-2</a></li>
          <li><a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'SM1-07', message: 'Informe de salud materno infantil SM1-07', type: 'success'});"><i class="bi bi-heart-pulse"></i> SM1-07</a></li>
          <li><a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'SM2', message: 'Informe complementario SM2', type: 'success'});"><i class="bi bi-hospital"></i> SM2</a></li>
          <li><a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'SM3-07', message: 'Informe consolidado SM3-07', type: 'success'});"><i class="bi bi-file-medical"></i> SM3-07</a></li>
          <li><a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Hora Médico', message: 'Rendimiento y productividad por hora médica', type: 'primary'});"><i class="bi bi-clock-history"></i> Hora Médico</a></li>
        </ul>
      </div>
    </li>

    <!-- GRUPO 3: GESTIÓN OTRAS BASES -->
    <li class="sidebar-heading">Gestión Otras Bases</li>

    <li class="sidebar-item" data-tooltip="Pacientes BD">
      <a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Pacientes BD', message: 'Base de datos maestra de pacientes', type: 'info'});">
        <i class="bi bi-person-vcard-fill nav-icon"></i>
        <span class="nav-label">Pacientes BD</span>
      </a>
    </li>

    <!-- Adolescentes con Submenú -->
    <li class="sidebar-item has-submenu {{ in_array('adolescentes', $openSubmenus) ? 'open' : '' }}" data-submenu="adolescentes" data-tooltip="Adolescentes">
      <a href="javascript:void(0)" class="sidebar-link">
        <i class="bi bi-people-fill nav-icon"></i>
        <span class="nav-label">Adolescentes</span>
        <i class="bi bi-chevron-right nav-arrow"></i>
      </a>
      <div class="sidebar-submenu-wrapper">
        <ul class="sidebar-submenu">
          <li><a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Base Adolescentes', message: 'Padrón de población adolescente', type: 'primary'});"><i class="bi bi-person-lines-fill"></i> Base Adolescentes</a></li>
          <li><a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Seguimientos', message: 'Registro de citas y seguimientos', type: 'info'});"><i class="bi bi-clipboard-check"></i> Seguimientos</a></li>
        </ul>
      </div>
    </li>

    <li class="sidebar-item" data-tooltip="Adulto Mayor">
      <a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Adulto Mayor', message: 'Control y atenciones de Adulto Mayor', type: 'success'});">
        <i class="bi bi-heart-fill nav-icon"></i>
        <span class="nav-label">Adulto Mayor</span>
      </a>
    </li>

    <!-- GRUPO 4: ADMINISTRACIÓN -->
    <li class="sidebar-heading">Administración</li>

    <li class="sidebar-item" data-tooltip="Médicos">
      <a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Médicos', message: 'Directorio de personal médico y turnos', type: 'info'});">
        <i class="bi bi-person-badge-fill nav-icon"></i>
        <span class="nav-label">Médicos</span>
      </a>
    </li>

    <li class="sidebar-item" data-tooltip="Diagnósticos">
      <a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Diagnósticos', message: 'Catálogo de diagnósticos y códigos CIE-10', type: 'warning'});">
        <i class="bi bi-clipboard-data-fill nav-icon"></i>
        <span class="nav-label">Diagnósticos</span>
      </a>
    </li>

    <li class="sidebar-item" data-tooltip="Colonias">
      <a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Colonias', message: 'Catálogo de sectores y colonias', type: 'primary'});">
        <i class="bi bi-geo-alt-fill nav-icon"></i>
        <span class="nav-label">Colonias</span>
      </a>
    </li>

    <li class="sidebar-item" data-tooltip="Referencias">
      <a href="javascript:void(0)" class="sidebar-link" onclick="SingApp.toast({title: 'Referencias', message: 'Control de referencias y traslados', type: 'info'});">
        <i class="bi bi-arrow-left-right nav-icon"></i>
        <span class="nav-label">Referencias</span>
      </a>
    </li>

    <!-- GRUPO 5: MÓDULO ADMIN -->
    <li class="sidebar-heading">Módulo Admin</li>

    <li class="sidebar-item {{ request()->routeIs('typography') ? 'active' : '' }}" data-tooltip="Personalización">
      <a href="{{ route('typography') }}" class="sidebar-link">
        <i class="bi bi-palette-fill nav-icon"></i>
        <span class="nav-label">Personalización</span>
      </a>
    </li>

    <li class="sidebar-item {{ request()->routeIs('ui-elements') ? 'active' : '' }}" data-tooltip="Componentes UI">
      <a href="{{ route('ui-elements') }}" class="sidebar-link">
        <i class="bi bi-bounding-box-circles nav-icon"></i>
        <span class="nav-label">Componentes UI</span>
      </a>
    </li>

    <li class="sidebar-item" data-tooltip="Alternar Modo">
      <a href="javascript:void(0)" class="sidebar-link" onclick="SingTheme.toggle();">
        <i class="bi bi-moon-stars-fill nav-icon"></i>
        <span class="nav-label">Alternar Tema</span>
      </a>
    </li>
  </ul>
  <script>
    (function() {
      try {
        var menu = document.querySelector('.sidebar-menu');
        var savedScroll = sessionStorage.getItem('sing_sidebar_scroll');
        if (menu && savedScroll !== null) {
          menu.scrollTop = parseInt(savedScroll, 10);
        }
      } catch (e) {}
    })();
  </script>
</aside>
