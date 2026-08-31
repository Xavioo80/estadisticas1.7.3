<!-- Top Navigation Bar -->
<header class="app-navbar">
  <div class="navbar-left">
    <!-- Sidebar Toggle Button -->
    <button type="button" class="navbar-toggle-btn" data-toggle="sidebar" title="Alternar Menú">
      <i class="bi bi-list"></i>
    </button>

    <!-- Botón Alerta Not a la izquierda del buscador -->
    <a href="{{ route('alertas.index') }}" class="btn-navbar-alerta-not" title="Alerta Not - Portal y Herramientas Integradas">
      <i class="bi bi-bell-fill"></i>
      <span>Alerta Not</span>
    </a>

    <!-- Search Bar -->
    <div class="navbar-search">
      <i class="bi bi-search"></i>
      <input type="text" placeholder="Buscar en Estadísticas 1.7 (Ctrl + K)..." id="globalSearchInput">
    </div>
  </div>

  <div class="navbar-right">
    <!-- Acceso rápido: Ingresos AT-1 (index) -->
    <a href="{{ route('ingresos.index') }}" class="navbar-action-btn" title="Ingresos AT-1" style="text-decoration:none; width:38px; height:38px; border-radius:var(--radius-sm,6px); display:inline-flex; align-items:center; justify-content:center;">
      <i class="bi bi-file-earmark-medical-fill" style="font-size: 1.5rem; color: var(--text-primary); line-height: 1;"></i>
    </a>

    <!-- Acceso rápido: Registros AT1 -->
    <a href="{{ route('registrosat1') }}" class="navbar-action-btn" title="Registros AT-1" style="text-decoration:none; width:38px; height:38px; border-radius:var(--radius-sm,6px); display:inline-flex; align-items:center; justify-content:center;">
      <i class="bi bi-table" style="font-size: 1.48rem; color: var(--text-primary); line-height: 1;"></i>
    </a>

    <!-- Acceso rápido: Informes AT1 -->
    <a href="{{ route('informesat1') }}" class="navbar-action-btn" title="Informes AT-1" style="text-decoration:none; width:38px; height:38px; border-radius:var(--radius-sm,6px); display:inline-flex; align-items:center; justify-content:center;">
      <i class="bi bi-file-earmark-bar-graph-fill" style="font-size: 1.5rem; color: var(--color-primary); line-height: 1;"></i>
    </a>

    <!-- Quick Add New Record Button (Green with Plus Icon) -->
    <a href="{{ route('ingresos.create') }}" class="btn-navbar-add-green" title="Nuevo Registro (AT-1)">
      <i class="bi bi-plus-lg" style="font-size: 1.35rem;"></i>
    </a>

    <!-- Quick Bloc de Notas & Tareas Button -->
    <a href="{{ route('notas.index') }}" class="navbar-action-btn" title="Bloc de Notas & Gestor de Tareas" style="text-decoration: none;">
      <i class="bi bi-journal-text" style="font-size: 1.15rem; color: var(--text-primary);"></i>
    </a>

    <!-- Acceso rápido: Correo Gmail en Ventana Popup -->
    <button type="button" class="navbar-action-btn btn-navbar-gmail" onclick="abrirPopupGmail()" title="Correo Gmail (estadisticacissanmiguel@gmail.com)" style="border:none; background:transparent; width:38px; height:38px; border-radius:var(--radius-sm,6px); display:inline-flex; align-items:center; justify-content:center; cursor:pointer;">
      <i class="bi bi-envelope-at-fill" style="font-size: 1.35rem; color: #ea4335; line-height: 1;"></i>
    </button>

    <!-- Quick Theme Switcher Button -->
    <button type="button" class="theme-toggle-btn" data-toggle="theme" title="Alternar Modo Claro / Oscuro (Alt+Shift+D)">
      <i class="bi bi-sun-fill icon-sun"></i>
      <i class="bi bi-moon-stars-fill icon-moon"></i>
    </button>

    <!-- Notifications Dropdown (Campana del Header Principal) -->
    <div class="dropdown">
      <button type="button" class="navbar-action-btn" id="navbar-bell-btn" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false" title="Notificaciones y Notas adhesivas" style="position: relative;">
        <i class="bi bi-bell" style="font-size: 1.35rem;"></i>
        <span class="navbar-bell-badge" id="navbar-bell-count" style="display: none;">0</span>
      </button>
      <div class="dropdown-menu dropdown-menu-right" id="navbar-bell-dropdown">
        <!-- Header con diseño premium -->
        <div class="fsn-bell-header">
          <div class="d-flex align-items-center gap-2">
            <div class="fsn-bell-icon-box">
              <i class="bi bi-bell-fill"></i>
            </div>
            <div>
              <div class="fsn-bell-title">Notificaciones & Notas</div>
              <small class="text-muted" style="font-size: 0.70rem; display: block; line-height: 1;">Alertas y notas fijadas</small>
            </div>
          </div>
          <span class="fsn-pill-count" id="navbar-bell-badge-text">0 Activas</span>
        </div>
        
        <!-- Lista dinámica de tarjetas de notas fijadas / alertas -->
        <div id="navbar-pinned-notes-list" class="fsn-bell-list">
          <div class="fsn-bell-empty" id="navbar-bell-empty">
            <div class="fsn-empty-icon">
              <i class="bi bi-check2-circle"></i>
            </div>
            <div class="font-weight-bold mb-1" style="font-size: 0.84rem; color: var(--text-primary);">¡Todo al día!</div>
            <small class="text-muted" style="font-size: 0.72rem;">No tienes alertas ni notas fijadas en este momento</small>
          </div>
        </div>

        <!-- Footer estilizado con acciones directas -->
        <div class="fsn-bell-footer">
          <button type="button" class="fsn-btn-restore" id="btn-restore-all-notes" onclick="window.mostrarNotasFlotantes();" style="display: none;">
            <i class="bi bi-window-stack mr-1"></i> Mostrar todas
          </button>
          <a href="{{ route('notas.index') }}" class="fsn-link-board ml-auto">
            <span>Ver Tablero</span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
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
