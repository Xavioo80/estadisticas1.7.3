/**
 * Sing App Main Application Script
 * Enhanced Sidebar Animations, Multi-Level Submenus, Hover-To-Expand State Management, Toolbars, Dropdowns & Toasts
 */

(function () {
  'use strict';

  // ==========================================================================
  // SAFE STORAGE WRAPPER (Prevents Tracking Prevention & Sandbox Exceptions)
  // ==========================================================================
  const memStore = {};

  const SafeStorage = {
    getLocal(key) {
      try {
        if (typeof window !== 'undefined' && window.localStorage) {
          return window.localStorage.getItem(key);
        }
      } catch (e) {}
      return memStore['loc_' + key] || null;
    },
    setLocal(key, val) {
      memStore['loc_' + key] = val;
      try {
        if (typeof window !== 'undefined' && window.localStorage) {
          window.localStorage.setItem(key, val);
        }
      } catch (e) {}
    },
    getSession(key) {
      try {
        if (typeof window !== 'undefined' && window.sessionStorage) {
          return window.sessionStorage.getItem(key);
        }
      } catch (e) {}
      return memStore['sess_' + key] || null;
    },
    setSession(key, val) {
      memStore['sess_' + key] = val;
      try {
        if (typeof window !== 'undefined' && window.sessionStorage) {
          window.sessionStorage.setItem(key, val);
        }
      } catch (e) {}
    },
    removeSession(key) {
      delete memStore['sess_' + key];
      try {
        if (typeof window !== 'undefined' && window.sessionStorage) {
          window.sessionStorage.removeItem(key);
        }
      } catch (e) {}
    },
    setCookie(name, val, maxAge) {
      try {
        document.cookie = name + '=' + (val || '') + '; path=/; max-age=' + (maxAge || 86400) + '; SameSite=Lax';
      } catch (e) {}
    }
  };

  // ==========================================================================
  // SIDEBAR CONTROLS & ANIMATIONS
  // ==========================================================================
  function initSidebar() {
    const sidebarToggleBtns = document.querySelectorAll('[data-toggle="sidebar"], .navbar-toggle-btn');
    const overlay = document.querySelector('.sidebar-overlay') || createSidebarOverlay();
    const appSidebar = document.querySelector('.app-sidebar');

    sidebarToggleBtns.forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        if (window.innerWidth <= 992) {
          // Mobile: Toggle drawer open/close
          document.body.classList.toggle('sidebar-open');
        } else {
          // Desktop: Toggle pinned full-width mode vs mini hover-to-expand mode
          document.body.classList.toggle('sidebar-collapsed');
          const isCollapsed = document.body.classList.contains('sidebar-collapsed');
          if (!isCollapsed) {
            document.body.classList.remove('sidebar-hover-active');
            if (appSidebar) appSidebar.classList.remove('is-expanded-hold');
          }
          SafeStorage.setLocal('sing_sidebar_collapsed', isCollapsed ? 'true' : 'false');
          // Dispatch single resize after CSS transition finishes for charts
          setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
          }, 240);
        }
      });
    });

    // Close mobile drawer when clicking the background overlay
    overlay.addEventListener('click', function () {
      document.body.classList.remove('sidebar-open');
    });

    // Restore desktop collapsed state (defaults to collapsed/mini on desktop for hover-to-expand)
    if (window.innerWidth > 992) {
      const savedState = SafeStorage.getLocal('sing_sidebar_collapsed');
      if (savedState === 'false') {
        document.body.classList.remove('sidebar-collapsed');
      } else {
        document.body.classList.add('sidebar-collapsed');
      }
    }

    // 1. Submenu Accordion & State Persistence across page loads
    const hasSubmenuItems = document.querySelectorAll('.sidebar-item.has-submenu');
    
    function saveSubmenuStates() {
      const openSubmenuIds = [];
      hasSubmenuItems.forEach((item, idx) => {
        const subId = item.getAttribute('data-submenu') || idx.toString();
        if (item.classList.contains('open')) {
          openSubmenuIds.push(subId);
        }
      });
      SafeStorage.setSession('sing_open_submenus', JSON.stringify(openSubmenuIds));
      SafeStorage.setCookie('sing_open_submenus', encodeURIComponent(JSON.stringify(openSubmenuIds)), 86400);
    }

    // Restore open submenu states from sessionStorage
    const savedSubmenus = SafeStorage.getSession('sing_open_submenus');
    if (savedSubmenus !== null) {
      try {
        const openSubmenuIds = JSON.parse(savedSubmenus);
        if (Array.isArray(openSubmenuIds)) {
          hasSubmenuItems.forEach((item, idx) => {
            const subId = item.getAttribute('data-submenu') || idx.toString();
            if (openSubmenuIds.includes(subId)) {
              item.classList.add('open');
            } else {
              // Keep open if contains the currently active page
              if (!item.querySelector('.sidebar-submenu li.active')) {
                item.classList.remove('open');
              }
            }
          });
        }
      } catch (e) {}
    } else {
      saveSubmenuStates();
    }

    // Submenu Toggle on Click
    const dropdownLinks = document.querySelectorAll('.sidebar-item.has-submenu > .sidebar-link');
    dropdownLinks.forEach(link => {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const parentItem = this.closest('.sidebar-item');
        if (!parentItem) return;
        const isAlreadyOpen = parentItem.classList.contains('open');

        // Toggle current item
        parentItem.classList.toggle('open', !isAlreadyOpen);
        saveSubmenuStates();
      });
    });

    // 2. Sidebar Menu Scroll Position Preservation with Debounce
    const sidebarMenu = document.querySelector('.sidebar-menu');
    if (sidebarMenu) {
      const restoreScrollPos = () => {
        const savedScroll = SafeStorage.getSession('sing_sidebar_scroll');
        if (savedScroll !== null) {
          sidebarMenu.scrollTop = parseInt(savedScroll, 10);
        }
      };
      restoreScrollPos();
      requestAnimationFrame(restoreScrollPos);
      setTimeout(restoreScrollPos, 50);

      // Debounced scroll listener to avoid firing storage events on every pixel
      let scrollTimer = null;
      sidebarMenu.addEventListener('scroll', function () {
        if (scrollTimer) clearTimeout(scrollTimer);
        scrollTimer = setTimeout(() => {
          SafeStorage.setSession('sing_sidebar_scroll', sidebarMenu.scrollTop.toString());
        }, 150);
      }, { passive: true });
    }

    // Save scroll on unload and on every link click
    window.addEventListener('beforeunload', function () {
      if (sidebarMenu) {
        SafeStorage.setSession('sing_sidebar_scroll', sidebarMenu.scrollTop.toString());
      }
    });

    // Click Ripple Micro-interaction for all sidebar links
    const allSidebarLinks = document.querySelectorAll('.sidebar-link');
    allSidebarLinks.forEach(link => {
      link.addEventListener('click', function (e) {
        createRippleEffect(e, this);
        if (sidebarMenu) {
          SafeStorage.setSession('sing_sidebar_scroll', sidebarMenu.scrollTop.toString());
        }
        // Click ripple and save scroll
      });
    });
  }

  /**
   * Creates a modern ripple expansion wave on click
   */
  function createRippleEffect(event, element) {
    const circle = document.createElement('span');
    const diameter = Math.max(element.clientWidth, element.clientHeight);
    const radius = diameter / 2;
    const rect = element.getBoundingClientRect();

    circle.style.width = circle.style.height = `${diameter}px`;
    circle.style.left = `${event.clientX - rect.left - radius}px`;
    circle.style.top = `${event.clientY - rect.top - radius}px`;
    circle.classList.add('ripple-wave');

    const ripple = element.querySelector('.ripple-wave');
    if (ripple) {
      ripple.remove();
    }

    element.appendChild(circle);
    setTimeout(() => circle.remove(), 600);
  }

  function createSidebarOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
    return overlay;
  }

  // ==========================================================================
  // CARD / WIDGET ACTIONS (Reload, Collapse, Fullscreen, Close)
  // ==========================================================================
  function initCardActions() {
    document.addEventListener('click', function (e) {
      const reloadBtn = e.target.closest('[data-action="reload"]');
      const collapseBtn = e.target.closest('[data-action="collapse"]');
      const fullscreenBtn = e.target.closest('[data-action="fullscreen"]');
      const closeBtn = e.target.closest('[data-action="close"]');

      if (reloadBtn) {
        e.preventDefault();
        const card = reloadBtn.closest('.sing-card, .card');
        if (!card) return;

        let loader = card.querySelector('.card-loader-overlay');
        if (!loader) {
          loader = document.createElement('div');
          loader.className = 'card-loader-overlay';
          loader.innerHTML = '<div class="spinner"></div>';
          card.appendChild(loader);
        }

        card.classList.add('is-loading');
        setTimeout(() => {
          card.classList.remove('is-loading');
          SingApp.toast({
            title: 'Datos Actualizados',
            message: 'El widget ha sincronizado la información con éxito.',
            type: 'info'
          });
        }, 900);
      }

      if (collapseBtn) {
        e.preventDefault();
        const card = collapseBtn.closest('.sing-card, .card');
        if (card) {
          card.classList.toggle('is-collapsed');
          const icon = collapseBtn.querySelector('i');
          if (icon) {
            icon.classList.toggle('bi-chevron-down');
            icon.classList.toggle('bi-chevron-up');
          }
        }
      }

      if (fullscreenBtn) {
        e.preventDefault();
        const card = fullscreenBtn.closest('.sing-card, .card, .sing-card-excel-fullscreen');
        if (card) {
          const isNativeFs = document.fullscreenElement || document.webkitFullscreenElement;
          const isCssFs = card.classList.contains('is-fullscreen');
          const icon = fullscreenBtn.querySelector('i');

          if (!isNativeFs && !isCssFs) {
            // Enter Fullscreen (HTML5 API with CSS fallback)
            if (card.requestFullscreen) {
              card.requestFullscreen().catch(() => card.classList.add('is-fullscreen'));
            } else if (card.webkitRequestFullscreen) {
              card.webkitRequestFullscreen();
            } else {
              card.classList.add('is-fullscreen');
            }
            card.classList.add('is-fullscreen');
            if (icon) {
              icon.classList.remove('bi-fullscreen');
              icon.classList.add('bi-fullscreen-exit');
            }
            fullscreenBtn.setAttribute('title', 'Salir de Pantalla Completa');
          } else {
            // Exit Fullscreen
            if (document.exitFullscreen && document.fullscreenElement) {
              document.exitFullscreen().catch(() => {});
            } else if (document.webkitExitFullscreen && document.webkitFullscreenElement) {
              document.webkitExitFullscreen();
            }
            card.classList.remove('is-fullscreen');
            if (icon) {
              icon.classList.remove('bi-fullscreen-exit');
              icon.classList.add('bi-fullscreen');
            }
            fullscreenBtn.setAttribute('title', 'Pantalla Completa');
          }
        }
      }

      if (closeBtn) {
        e.preventDefault();
        const card = closeBtn.closest('.sing-card, .card');
        if (card) {
          card.style.transition = 'opacity 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1)';
          card.style.opacity = '0';
          card.style.transform = 'scale(0.95)';
          setTimeout(() => card.remove(), 300);
        }
      }
    });

    // Sync fullscreen state when exiting via ESC key
    document.addEventListener('fullscreenchange', function () {
      if (!document.fullscreenElement) {
        document.querySelectorAll('.sing-card.is-fullscreen, .card.is-fullscreen, .sing-card-excel-fullscreen.is-fullscreen').forEach(card => {
          card.classList.remove('is-fullscreen');
        });
        document.querySelectorAll('[data-action="fullscreen"]').forEach(btn => {
          const icon = btn.querySelector('i');
          if (icon) {
            icon.classList.remove('bi-fullscreen-exit');
            icon.classList.add('bi-fullscreen');
          }
          btn.setAttribute('title', 'Pantalla Completa');
        });
      }
    });
  }

  // ==========================================================================
  // DROPDOWNS
  // ==========================================================================
  function initDropdowns() {
    // Si Bootstrap ya gestiona los dropdowns con jQuery, evitar conflicto de doble alternancia (toggle)
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.dropdown !== 'undefined') {
      return;
    }
    document.addEventListener('click', function (e) {
      const toggle = e.target.closest('[data-toggle="dropdown"]');
      const allDropdowns = document.querySelectorAll('.dropdown');

      if (toggle) {
        e.preventDefault();
        const parent = toggle.closest('.dropdown');
        allDropdowns.forEach(dd => {
          if (dd !== parent) dd.classList.remove('show');
        });
        if (parent) parent.classList.toggle('show');
      } else if (!e.target.closest('.dropdown-menu')) {
        allDropdowns.forEach(dd => dd.classList.remove('show'));
      }
    });
  }

  // ==========================================================================
  // TOAST NOTIFICATIONS HELPER
  // ==========================================================================
  function getToastContainer() {
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    return container;
  }

  function showToast({ title = 'Notificación', message = '', type = 'primary', duration = 4000 }) {
    const container = getToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast border-${type}`;

    let iconClass = 'bi-info-circle-fill text-primary';
    if (type === 'success') iconClass = 'bi-check-circle-fill text-success';
    if (type === 'warning') iconClass = 'bi-exclamation-triangle-fill text-warning';
    if (type === 'danger') iconClass = 'bi-x-circle-fill text-danger';

    toast.innerHTML = `
      <div class="toast-header">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          <i class="bi ${iconClass}"></i>
          <span>${title}</span>
        </div>
        <button type="button" class="alert-dismiss" style="font-size: 0.9rem;" onclick="this.closest('.toast').remove()">
          <i class="bi bi-x"></i>
        </button>
      </div>
      <div class="toast-body">${message}</div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
      toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(100%)';
      setTimeout(() => toast.remove(), 300);
    }, duration);
  }

  // ==========================================================================
  // TODO LIST INTERACTION
  // ==========================================================================
  function initTodoList() {
    document.addEventListener('change', function (e) {
      if (e.target.classList.contains('todo-checkbox')) {
        const item = e.target.closest('.todo-item');
        if (item) {
          item.classList.toggle('completed', e.target.checked);
        }
      }
    });
  }

  // Global SingApp API
  window.SingApp = {
    init: function () {
      initSidebar();
      initCardActions();
      initDropdowns();
      initTodoList();
    },
    toast: showToast,
    storage: SafeStorage
  };

  // Auto initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.SingApp.init);
  } else {
    window.SingApp.init();
  }
})();
