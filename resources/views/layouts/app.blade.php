<!DOCTYPE html>
<html lang="es" data-theme="{{ request()->cookie('sing_theme', 'dark') }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Estadísticas 1.7') }}</title>
  
  <!-- Anti-FOUC (Flash of Unstyled Color Scheme & Sidebar State) inline script -->
  <script>
    (function() {
      try {
        var theme = null;
        try {
          if (window.localStorage) {
            theme = localStorage.getItem('sing_theme');
          }
        } catch(e){}
        
        if (!theme) {
          try {
            var match = document.cookie.match(new RegExp('(^| )sing_theme=([^;]+)'));
            if (match) theme = match[2];
          } catch(e){}
        }
        
        if (theme) {
          document.documentElement.setAttribute('data-theme', theme);
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
          document.documentElement.setAttribute('data-theme', 'light');
        } else {
          document.documentElement.setAttribute('data-theme', 'dark');
        }

        var isSidebarPinned = null;
        try { isSidebarPinned = window.localStorage ? localStorage.getItem('sing_sidebar_collapsed') : null; } catch(e){}
        if (isSidebarPinned === 'false') {
          document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.remove('sidebar-collapsed');
          });
        }
      } catch (e) {}
    })();
  </script>

  <!-- AngularJS Anti-FOUC (prevents uncompiled template expressions during initial page load) -->
  <style>
    [ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
      display: none !important;
    }
  </style>

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap Icons CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Core Libraries: jQuery, Popper, Bootstrap 4 JS, SweetAlert2 -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- ApexCharts CDN -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <!-- Third-party Plugins CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

  <!-- Sing App / Estadísticas 1.7 Core Stylesheets -->
  <link rel="stylesheet" href="{{ asset('assets/css/sing-theme.css') }}?v={{ @filemtime(public_path('assets/css/sing-theme.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/sing-layout.css') }}?v={{ @filemtime(public_path('assets/css/sing-layout.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/sing-components.css') }}?v={{ @filemtime(public_path('assets/css/sing-components.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/sing-informes.css') }}?v={{ @filemtime(public_path('assets/css/sing-informes.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/sing-sticky-notes.css') }}?v={{ @filemtime(public_path('assets/css/sing-sticky-notes.css')) }}">
  @stack('styles')

  <!-- ── Floating Pinned Sticky Notes (Global - All Pages) ──────────────── -->
  <style>
    /* ── CONTENEDOR DE NOTAS FLOTANTES FIJADAS (sobre toda la app) ── */
    #floating-pinned-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 0;
      height: 0;
      z-index: 9990;
      pointer-events: none;
    }

    .floating-sticky-note {
      position: fixed;
      width: 290px;
      min-height: 240px;
      max-height: 420px;
      border-radius: 10px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: 0 12px 40px rgba(0,0,0,0.35), 0 4px 12px rgba(0,0,0,0.2);
      border: 1px solid var(--sticky-border);
      background: var(--sticky-bg);
      color: var(--sticky-text);
      pointer-events: all;
      transition: box-shadow 0.15s ease, transform 0.1s ease;
      resize: both;
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }

    .floating-sticky-note:hover {
      box-shadow: 0 16px 50px rgba(0,0,0,0.4), 0 6px 16px rgba(0,0,0,0.25);
    }

    .floating-sticky-note.is-dragging {
      opacity: 0.92;
      box-shadow: 0 24px 60px rgba(0,0,0,0.45), 0 8px 20px rgba(0,0,0,0.3);
      transform: rotate(1.2deg) scale(1.01);
      z-index: 9999 !important;
      cursor: grabbing !important;
    }

    /* Titlebar: fondo coloreado, arrastrable */
    .floating-sticky-header {
      height: 38px;
      background: var(--sticky-header-bg, #ca8a04);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 8px;
      color: #ffffff;
      user-select: none;
      flex-shrink: 0;
      cursor: grab;
    }
    .floating-sticky-header:active { cursor: grabbing; }

    .floating-sticky-title {
      flex: 1 1 0%;
      font-size: 0.84rem;
      font-weight: 700;
      color: #ffffff;
      padding: 0 8px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .floating-pin-badge {
      font-size: 0.78rem;
      color: #fef08a;
      transform: rotate(45deg);
      margin-right: 4px;
      flex-shrink: 0;
    }

    .floating-sticky-header .fsn-btn {
      background: transparent;
      border: none;
      color: rgba(255,255,255,0.9);
      width: 26px;
      height: 26px;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.9rem;
      cursor: pointer;
      transition: background 0.12s;
      flex-shrink: 0;
    }
    .floating-sticky-header .fsn-btn:hover { background: rgba(0,0,0,0.2); }
    .floating-sticky-header .fsn-btn.fsn-close:hover { background: #ef4444; }

    /* Cuerpo */
    .floating-sticky-body {
      padding: 12px 14px;
      flex: 1 1 auto;
      overflow-y: auto;
      font-size: 0.88rem;
      line-height: 1.55;
      color: var(--sticky-text);
      word-break: break-word;
      scrollbar-width: thin;
      scrollbar-color: var(--sticky-border) transparent;
      cursor: default;
    }

    /* Checklist en nota flotante */
    .fsn-checklist-row {
      display: flex;
      align-items: flex-start;
      gap: 7px;
      padding: 2px 0;
      font-size: 0.84rem;
    }
    .fsn-check-input {
      margin-top: 3px;
      width: 15px;
      height: 15px;
      border-radius: 3px;
      cursor: pointer;
      accent-color: var(--sticky-header-bg);
      flex-shrink: 0;
    }
    .fsn-check-text {
      flex: 1 1 0%;
      color: var(--sticky-text);
    }
    .fsn-check-text.done {
      text-decoration: line-through;
      opacity: 0.5;
    }

    /* Toolbar inferior */
    .floating-sticky-toolbar {
      height: 34px;
      border-top: 1px solid var(--sticky-toolbar-border);
      background: var(--sticky-toolbar-bg);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 10px;
      flex-shrink: 0;
    }
    .floating-sticky-toolbar .fsn-goto {
      font-size: 0.7rem;
      font-weight: 600;
      color: var(--sticky-meta-text);
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 4px;
      opacity: 0.75;
      transition: opacity 0.12s;
    }
    .floating-sticky-toolbar .fsn-goto:hover { opacity: 1; }
    .floating-sticky-toolbar .fsn-time {
      font-size: 0.7rem;
      font-family: monospace;
      color: var(--sticky-meta-text);
    }

    /* Dog-ear corner */
    .floating-sticky-dogear {
      position: absolute;
      bottom: 0;
      right: 0;
      width: 0;
      height: 0;
      border-style: solid;
      border-width: 0 0 10px 10px;
      border-color: transparent transparent var(--sticky-dogear-bg) transparent;
      pointer-events: none;
    }
  </style>
</head>
<body class="sidebar-collapsed">
<div class="app-wrapper">

  <!-- Sidebar Navigation -->
  @include('layouts.sidebar')

  <!-- Main Application Wrapper -->
  <div class="app-main">

    <!-- Top Navbar -->
    @include('layouts.navbar')

    <!-- Content Area -->
    <main class="app-content">
      @yield('content')
      {{ $slot ?? '' }}
    </main>

    <!-- Footer -->
    @include('layouts.footer')

  </div>
</div>

<!-- ── Floating Pinned Sticky Notes Container (Global overlay) ─────── -->
<div id="floating-pinned-container"></div>

<!-- Scripts -->
<script src="{{ asset('assets/js/sing-theme.js') }}?v={{ @filemtime(public_path('assets/js/sing-theme.js')) }}"></script>
<script src="{{ asset('assets/js/sing-app.js') }}?v={{ @filemtime(public_path('assets/js/sing-app.js')) }}"></script>
<script src="{{ asset('assets/js/sing-charts.js') }}?v={{ @filemtime(public_path('assets/js/sing-charts.js')) }}"></script>
@stack('scripts')

<!-- ── FLOATING PINNED STICKY NOTES: Global JS (All Pages) ────────────── -->
<script>
(function() {
  // ── Shared Color Helpers ─────────────────────────────────────────────
  function getStickyClass(color) {
    if (!color) return 'yellow';
    color = color.toLowerCase();
    if (color.includes('22c55e') || color.includes('10b981')) return 'green';
    if (color.includes('ec4899') || color.includes('ef4444')) return 'pink';
    if (color.includes('a855f7') || color.includes('8b5cf6')) return 'purple';
    if (color.includes('3b82f6') || color.includes('06b6d4')) return 'blue';
    if (color.includes('64748b') || color.includes('6c757d')) return 'charcoal';
    return 'yellow';
  }

  // ── Estado de ventanas (posición, minimizadas, cerradas en session) ──
  var closedThisSession = {};
  try {
    var saved = sessionStorage.getItem('fsn_closed');
    if (saved) closedThisSession = JSON.parse(saved) || {};
  } catch(e) {}

  var positions = {};
  try {
    var savedPos = localStorage.getItem('fsn_positions');
    if (savedPos) positions = JSON.parse(savedPos) || {};
  } catch(e) {}

  function savePositions() {
    try { localStorage.setItem('fsn_positions', JSON.stringify(positions)); } catch(e) {}
  }

  function saveClosedSession() {
    try { sessionStorage.setItem('fsn_closed', JSON.stringify(closedThisSession)); } catch(e) {}
  }

  // ── Actualizar campana de notificaciones en el header principal ───
  function updateNavbarBell(notas, closedCount) {
    var bellCount = document.getElementById('navbar-bell-count');
    var badgeText = document.getElementById('navbar-bell-badge-text');
    var listContainer = document.getElementById('navbar-pinned-notes-list');
    var restoreBtn = document.getElementById('btn-restore-all-notes');

    if (!bellCount) return;

    var numNotas = (notas || []).length;
    // Mostrar número en la campana si hay notas ocultas o notas fijadas
    var countToShow = closedCount > 0 ? closedCount : numNotas;

    if (countToShow > 0) {
      bellCount.textContent = countToShow;
      bellCount.style.display = 'flex';
    } else {
      bellCount.style.display = 'none';
    }

    if (badgeText) {
      badgeText.textContent = (closedCount > 0 ? closedCount + ' Oculta(s)' : numNotas + ' Activa(s)');
    }

    if (restoreBtn) {
      restoreBtn.style.display = closedCount > 0 ? 'inline-flex' : 'none';
    }

    if (listContainer) {
      if (!notas || notas.length === 0) {
        listContainer.innerHTML = '<div class="fsn-bell-empty">' +
          '<div class="fsn-empty-icon"><i class="bi bi-check2-circle"></i></div>' +
          '<div class="font-weight-bold mb-1" style="font-size:0.84rem; color:var(--text-primary);">¡Todo al día!</div>' +
          '<small class="text-muted" style="font-size:0.72rem;">No tienes alertas ni notas fijadas en este momento</small>' +
        '</div>';
      } else {
        var html = '';
        notas.forEach(function(nota) {
          var isClosed = closedThisSession[nota.id];
          var tagColor = '#eab308';
          var tagBg = 'rgba(234, 179, 8, 0.14)';
          var tagBorder = 'rgba(234, 179, 8, 0.3)';
          var tipoLabel = 'Nota';
          var tipoIco = 'bi-sticky';

          if ((nota.color || '').includes('22c55e') || nota.tipo === 'checklist') {
            tagColor = '#22c55e';
            tagBg = 'rgba(34, 197, 94, 0.14)';
            tagBorder = 'rgba(34, 197, 94, 0.3)';
            tipoLabel = 'Checklist';
            tipoIco = 'bi-check2-square';
          } else if ((nota.color || '').includes('ec4899') || nota.tipo === 'lista_numerada') {
            tagColor = '#ec4899';
            tagBg = 'rgba(236, 72, 153, 0.14)';
            tagBorder = 'rgba(236, 72, 153, 0.3)';
            tipoLabel = 'Numerada';
            tipoIco = 'bi-list-ol';
          } else if ((nota.color || '').includes('a855f7') || nota.tipo === 'alerta') {
            tagColor = '#a855f7';
            tagBg = 'rgba(168, 85, 247, 0.14)';
            tagBorder = 'rgba(168, 85, 247, 0.3)';
            tipoLabel = 'Alerta';
            tipoIco = 'bi-bell-fill';
          } else if ((nota.color || '').includes('3b82f6')) {
            tagColor = '#3b82f6';
            tagBg = 'rgba(59, 130, 246, 0.14)';
            tagBorder = 'rgba(59, 130, 246, 0.3)';
            tipoLabel = 'Nota';
            tipoIco = 'bi-file-text';
          } else if ((nota.color || '').includes('64748b')) {
            tagColor = '#64748b';
            tagBg = 'rgba(100, 116, 139, 0.14)';
            tagBorder = 'rgba(100, 116, 139, 0.3)';
            tipoLabel = 'Carbón';
            tipoIco = 'bi-file-text';
          }

          var contentSnippet = (nota.contenido || '').replace(/<[^>]*>/g, '').trim();
          if (!contentSnippet && nota.checklist_items && nota.checklist_items.length) {
            var itemsCount = (Array.isArray(nota.checklist_items) ? nota.checklist_items.length : JSON.parse(nota.checklist_items || '[]').length);
            contentSnippet = itemsCount + ' tarea(s) en lista';
          }
          if (!contentSnippet) contentSnippet = 'Sin contenido...';
          if (contentSnippet.length > 55) contentSnippet = contentSnippet.substring(0, 55) + '...';

          html += '<div class="fsn-bell-card" onclick="window.abrirNotaFlotante(' + nota.id + ')" title="Clic para abrir en pantalla">' +
            '<div class="fsn-card-accent" style="background:' + tagColor + '; box-shadow:0 0 8px ' + tagColor + ';"></div>' +
            '<div class="fsn-card-content">' +
              '<div class="d-flex align-items-center justify-content-between mb-1">' +
                '<span class="fsn-card-title">' + escHtml(nota.titulo || 'Nota') + '</span>' +
                '<span class="fsn-type-pill" style="color:' + tagColor + '; background:' + tagBg + '; border:1px solid ' + tagBorder + ';">' +
                  '<i class="bi ' + tipoIco + ' mr-1" style="font-size:0.68rem;"></i>' + tipoLabel +
                '</span>' +
              '</div>' +
              '<p class="fsn-card-snippet">' + escHtml(contentSnippet) + '</p>' +
              '<div class="d-flex align-items-center justify-content-between mt-1 pt-1" style="border-top:1px dashed var(--border-color);">' +
                '<span class="fsn-status-badge ' + (isClosed ? 'is-closed' : 'is-open') + '">' +
                  '<span class="fsn-dot"></span>' +
                  (isClosed ? 'Oculta en dock' : 'En pantalla') +
                '</span>' +
                '<span class="fsn-action-btn">' +
                  '<span>Abrir</span>' +
                  '<i class="bi bi-box-arrow-up-right"></i>' +
                '</span>' +
              '</div>' +
            '</div>' +
          '</div>';
        });
        listContainer.innerHTML = html;
      }
    }
  }

  // ── Cargar notas fijadas desde API ──────────────────────────────────
  function cargarNotasFlotantes() {
    $.ajax({
      url: '/notas?pinned_only=1',
      type: 'GET',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      success: function(data) {
        var notas = data.notas_pinned || [];
        var container = document.getElementById('floating-pinned-container');
        if (container) container.innerHTML = '';
        var closedCount = 0;

        notas.forEach(function(nota, i) {
          if (closedThisSession[nota.id]) {
            closedCount++;
            return;
          }
          renderFloatingNote(nota, i);
        });

        // Actualizar la campana del header principal
        updateNavbarBell(notas, closedCount);
      }
    });
  }

  // ── Renderizar una nota flotante ─────────────────────────────────────
  function renderFloatingNote(nota, index) {
    var colorClass = getStickyClass(nota.color);
    var pos = positions[nota.id];

    // Posición en cascada: desplazar por cada nota
    var defaultTop  = 80 + (index * 44);
    var defaultLeft = 80 + (index * 40);
    var top   = pos ? pos.top  : defaultTop;
    var left  = pos ? pos.left : defaultLeft;

    // Clamp dentro de la pantalla
    top  = Math.max(50, Math.min(top,  window.innerHeight - 250));
    left = Math.max(10, Math.min(left, window.innerWidth  - 300));

    var el = document.createElement('div');
    el.className = 'floating-sticky-note sticky-' + colorClass;
    el.id = 'fsn-' + nota.id;
    el.dataset.id = nota.id;
    el.style.top  = top  + 'px';
    el.style.left = left + 'px';
    el.style.zIndex = 9990 + index;

    // Toolbar hora
    var hora = nota.updated_at ? nota.updated_at.slice(11, 16) : '';

    // Contenido del cuerpo
    var bodyHtml = '';
    if (nota.tipo === 'checklist' && nota.checklist_items) {
      var items = typeof nota.checklist_items === 'string'
        ? JSON.parse(nota.checklist_items)
        : nota.checklist_items;

      if (items && items.length) {
        bodyHtml = items.map(function(item, ci) {
          return '<div class="fsn-checklist-row">' +
            '<input type="checkbox" class="fsn-check-input" ' + (item.done ? 'checked' : '') +
            ' onchange="toggleFsnCheck(' + nota.id + ', ' + ci + ', this)">' +
            '<span class="fsn-check-text ' + (item.done ? 'done' : '') + '">' +
            escHtml(item.text || '') + '</span>' +
            '</div>';
        }).join('');
      }
    } else if (nota.tipo === 'lista_numerada' && nota.checklist_items) {
      var itemsNum = typeof nota.checklist_items === 'string'
        ? JSON.parse(nota.checklist_items)
        : nota.checklist_items;

      if (itemsNum && itemsNum.length) {
        bodyHtml = itemsNum.map(function(item, ci) {
          return '<div class="fsn-checklist-row" style="gap:8px;">' +
            '<span style="font-weight:700; font-size:0.8rem; color:var(--sticky-meta-text); min-width:18px;">' + (ci + 1) + '.</span>' +
            '<span class="fsn-check-text">' + escHtml(item.text || '') + '</span>' +
            '</div>';
        }).join('');
      }
    } else {
      bodyHtml = nota.contenido || '<span style="opacity:0.45;">Sin contenido...</span>';
    }

    el.innerHTML =
      '<div class="floating-sticky-header" id="fsn-header-' + nota.id + '">' +
        '<i class="bi bi-stickies-fill fsn-btn" style="cursor:default; pointer-events:none; font-size:0.85rem;"></i>' +
        '<span class="floating-sticky-title">' + escHtml(nota.titulo || 'Nota rápida') + '</span>' +
        '<i class="bi bi-pin-angle-fill floating-pin-badge" title="Nota fijada"></i>' +
        '<button type="button" class="fsn-btn fsn-close" onclick="cerrarNotaFlotante(' + nota.id + ')" title="Ocultar (mantiene el pin)">' +
          '<i class="bi bi-x-lg"></i>' +
        '</button>' +
      '</div>' +
      '<div class="floating-sticky-body">' + bodyHtml + '</div>' +
      '<div class="floating-sticky-toolbar">' +
        '<a class="fsn-goto" href="/notas" title="Ir al tablero de Notas Rápidas">' +
          '<i class="bi bi-arrow-up-right-square"></i> Notas Rápidas' +
        '</a>' +
        '<span class="fsn-time">' + hora + '</span>' +
      '</div>' +
      '<div class="floating-sticky-dogear"></div>';

    document.getElementById('floating-pinned-container').appendChild(el);

    // Arrastre (drag) por el header
    setupDrag(el, document.getElementById('fsn-header-' + nota.id), nota.id);

    // Animar entrada
    el.style.opacity = '0';
    el.style.transform = 'scale(0.92) translateY(8px)';
    requestAnimationFrame(function() {
      el.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
      el.style.opacity = '1';
      el.style.transform = 'scale(1) translateY(0)';
    });
  }

  // ── Escape HTML básico ───────────────────────────────────────────────
  function escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // ── Cerrar una nota flotante (solo en esta sesión) ──────────────────
  window.cerrarNotaFlotante = function(id) {
    var el = document.getElementById('fsn-' + id);
    if (!el) return;
    el.style.transition = 'opacity 0.18s ease, transform 0.18s ease';
    el.style.opacity = '0';
    el.style.transform = 'scale(0.88) translateY(-6px)';
    setTimeout(function() {
      if (el.parentNode) el.parentNode.removeChild(el);
    }, 200);
    closedThisSession[id] = true;
    saveClosedSession();

    // Actualizar campana en el header principal
    cargarNotasFlotantes();
  };

  // ── Exponer para uso global (página de notas y otros módulos) ──────
  window.cargarNotasFlotantes = cargarNotasFlotantes;

  window.abrirNotaFlotante = function(id) {
    delete closedThisSession[id];
    saveClosedSession();
    var existing = document.getElementById('fsn-' + id);
    if (existing && existing.parentNode) {
      existing.parentNode.removeChild(existing);
    }
    // Asegurar que el contenedor existe
    var container = document.getElementById('floating-pinned-container');
    if (!container) return;
    cargarNotasFlotantes();
  };

  // ── Mostrar/recargar todas las notas flotantes desde la campana ───
  window.mostrarNotasFlotantes = function() {
    closedThisSession = {};
    saveClosedSession();
    var container = document.getElementById('floating-pinned-container');
    if (container) container.innerHTML = '';
    cargarNotasFlotantes();
  };

  // ── Toggle de checklist en nota flotante ─────────────────────────────
  window.toggleFsnCheck = function(notaId, index, checkbox) {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({
      url: '/notas/' + notaId + '/toggle-checklist',
      type: 'POST',
      data: { _token: csrfToken, index: index },
      success: function() {
        var span = checkbox.parentElement.querySelector('.fsn-check-text');
        if (checkbox.checked) {
          span.classList.add('done');
        } else {
          span.classList.remove('done');
        }
      }
    });
  };

  // ── Drag & Drop de ventanas flotantes ────────────────────────────────
  function setupDrag(el, handle, notaId) {
    var isDragging = false;
    var startX, startY, startLeft, startTop;

    handle.addEventListener('mousedown', function(e) {
      if (e.target.classList.contains('fsn-close') || e.target.closest('.fsn-close')) return;
      isDragging = true;
      startX = e.clientX;
      startY = e.clientY;
      var rect = el.getBoundingClientRect();
      startLeft = rect.left;
      startTop  = rect.top;
      el.classList.add('is-dragging');
      // Llevar al frente
      el.style.zIndex = 9999;
      e.preventDefault();
    });

    document.addEventListener('mousemove', function(e) {
      if (!isDragging) return;
      var dx = e.clientX - startX;
      var dy = e.clientY - startY;
      var newLeft = startLeft + dx;
      var newTop  = startTop  + dy;

      // Clamp dentro de viewport
      newLeft = Math.max(0, Math.min(newLeft, window.innerWidth  - el.offsetWidth));
      newTop  = Math.max(0, Math.min(newTop,  window.innerHeight - el.offsetHeight));

      el.style.left = newLeft + 'px';
      el.style.top  = newTop  + 'px';
    });

    document.addEventListener('mouseup', function(e) {
      if (!isDragging) return;
      isDragging = false;
      el.classList.remove('is-dragging');
      // Guardar posición
      positions[notaId] = {
        top:  parseInt(el.style.top),
        left: parseInt(el.style.left)
      };
      savePositions();
    });

    // Click al frente
    el.addEventListener('mousedown', function() {
      document.querySelectorAll('.floating-sticky-note').forEach(function(n) {
        n.style.zIndex = 9990;
      });
      el.style.zIndex = 9999;
    });
  }

  // ── Iniciar al cargar el DOM ──────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined') {
      cargarNotasFlotantes();
    }
  });

})();
</script>

</body>
</html>
