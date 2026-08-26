<!-- Top Navigation Bar -->
<header class="app-navbar">
  <div class="navbar-left">
    <!-- Sidebar Toggle Button -->
    <button type="button" class="navbar-toggle-btn" data-toggle="sidebar" title="Alternar Menú">
      <i class="bi bi-list"></i>
    </button>

    <!-- Search Bar -->
    <div class="navbar-search">
      <i class="bi bi-search"></i>
      <input type="text" placeholder="Buscar en Estadísticas 1.7 (Ctrl + K)..." id="globalSearchInput">
    </div>
  </div>

  <div class="navbar-right">
    <!-- Quick Add New Record Button (Green with Plus Icon) -->
    <a href="{{ route('ingresos.create') }}" class="btn-navbar-add-green" title="Nuevo Registro (AT-1)">
      <i class="bi bi-plus-lg"></i>
    </a>

    <!-- Quick Theme Switcher Button -->
    <button type="button" class="theme-toggle-btn" data-toggle="theme" title="Alternar Modo Claro / Oscuro (Alt+Shift+D)">
      <i class="bi bi-sun-fill icon-sun"></i>
      <i class="bi bi-moon-stars-fill icon-moon"></i>
    </button>

    <!-- Notifications Dropdown -->
    <div class="dropdown">
      <button type="button" class="navbar-action-btn" data-toggle="dropdown" title="Notificaciones">
        <i class="bi bi-bell"></i>
        <span class="badge-dot"></span>
      </button>
      <div class="dropdown-menu" style="min-width: 320px; right: 0;">
        <div class="dropdown-header" style="display: flex; justify-content: space-between; align-items: center;">
          <span>Notificaciones</span>
          <span class="badge badge-soft-primary">3 Nuevas</span>
        </div>
        <a href="#" class="dropdown-item" style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);" onclick="SingApp.toast({title: 'Servidor', message: 'Carga de CPU normalizada al 28%', type: 'success'}); return false;">
          <div style="width: 32px; height: 32px; border-radius: var(--radius-full); background: var(--color-success-light); color: var(--color-success); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="bi bi-hdd-network"></i>
          </div>
          <div>
            <div style="font-size: 0.85rem; font-weight: 600;">Servidor restablecido</div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">Hace 5 minutos</div>
          </div>
        </a>
        <a href="#" class="dropdown-item" style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-color);" onclick="SingApp.toast({title: 'Nueva Venta', message: 'Factura #INV-9402 pagada ($450.00)', type: 'primary'}); return false;">
          <div style="width: 32px; height: 32px; border-radius: var(--radius-full); background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="bi bi-credit-card-2-front"></i>
          </div>
          <div>
            <div style="font-size: 0.85rem; font-weight: 600;">Nueva venta confirmada</div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">Hace 24 minutos</div>
          </div>
        </a>
        <a href="#" class="dropdown-item" style="padding: 0.75rem 1rem;" onclick="SingApp.toast({title: 'Alerta', message: 'Copia de seguridad semanal completada', type: 'info'}); return false;">
          <div style="width: 32px; height: 32px; border-radius: var(--radius-full); background: var(--color-warning-light); color: var(--color-warning); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="bi bi-cloud-arrow-up"></i>
          </div>
          <div>
            <div style="font-size: 0.85rem; font-weight: 600;">Backup completado</div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">Hace 2 horas</div>
          </div>
        </a>
      </div>
    </div>

    <!-- User Profile Menu -->
    <div class="dropdown">
      <div class="navbar-user" data-toggle="dropdown">
        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80" alt="User Avatar" class="navbar-user-avatar">
        <div class="navbar-user-details" style="display: none; @media(min-width: 768px){ display: flex; }">
          <span class="navbar-user-name">Alexandre Rivera</span>
          <span class="navbar-user-role">Administrador Senior</span>
        </div>
        <i class="bi bi-chevron-down" style="font-size: 0.75rem; color: var(--text-muted); margin-left: 0.25rem;"></i>
      </div>
      <div class="dropdown-menu" style="right: 0;">
        <div class="dropdown-header">
          <div style="font-weight: 600; color: var(--text-primary);">Alexandre Rivera</div>
          <div style="font-size: 0.75rem; color: var(--text-muted);">alex.rivera@singapp.io</div>
        </div>
        <a href="#" class="dropdown-item" onclick="SingApp.toast({title: 'Perfil', message: 'Navegando a configuración de cuenta...', type: 'info'}); return false;">
          <i class="bi bi-person-circle"></i> Mi Perfil
        </a>
        <a href="#" class="dropdown-item" onclick="SingApp.toast({title: 'Ajustes', message: 'Abriendo panel de preferencias...', type: 'info'}); return false;">
          <i class="bi bi-sliders"></i> Preferencias
        </a>
        <a href="#" class="dropdown-item" onclick="SingTheme.toggle(); return false;">
          <i class="bi bi-moon-stars"></i> Cambiar Tema
        </a>
        <div class="dropdown-divider"></div>
        <a href="#" class="dropdown-item" style="color: var(--color-danger);" onclick="SingApp.toast({title: 'Sesión', message: 'Cerrando sesión de usuario...', type: 'danger'}); return false;">
          <i class="bi bi-box-arrow-right" style="color: var(--color-danger);"></i> Cerrar Sesión
        </a>
      </div>
    </div>
  </div>
</header>
