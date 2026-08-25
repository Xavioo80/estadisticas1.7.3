/**
 * Sing App Main Application Script
 * Enhanced Sidebar Animations, Multi-Level Submenus, Hover-To-Expand State Management, Toolbars, Dropdowns & Toasts
 */

(function () {
  'use strict';

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
          try {
            localStorage.setItem('sing_sidebar_collapsed', isCollapsed ? 'true' : 'false');
          } catch (err) {}
        }
      });
    });

    // Close mobile drawer when clicking the background overlay
    overlay.addEventListener('click', function () {
      document.body.classList.remove('sidebar-open');
    });

    // Restore desktop collapsed state (defaults to collapsed/mini on desktop for hover-to-expand)
    if (window.innerWidth > 992) {
      const savedState = localStorage.getItem('sing_sidebar_collapsed');
      if (savedState === 'false') {
        document.body.classList.remove('sidebar-collapsed');
      } else {
        document.body.classList.add('sidebar-collapsed');
      }
    }

    // 1. Submenu Accordion & State Persistence across page loads (Zero shift/jump)
    const hasSubmenuItems = document.querySelectorAll('.sidebar-item.has-submenu');
    
    function saveSubmenuStates() {
      const openSubmenuIds = [];
      hasSubmenuItems.forEach((item, idx) => {
        const subId = item.getAttribute('data-submenu') || idx.toString();
        if (item.classList.contains('open')) {
          openSubmenuIds.push(subId);
        }
      });
      try {
        sessionStorage.setItem('sing_open_submenus', JSON.stringify(openSubmenuIds));
        document.cookie = "sing_open_submenus=" + encodeURIComponent(JSON.stringify(openSubmenuIds)) + "; path=/; max-age=86400; SameSite=Lax";
      } catch (e) {}
    }

    // Restore open submenu states from sessionStorage
    const savedSubmenus = sessionStorage.getItem('sing_open_submenus');
    if (savedSubmenus !== null) {
      try {
        const openSubmenuIds = JSON.parse(savedSubmenus);
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

    // 2. Sidebar Menu Scroll Position Preservation (100% Static Scroll Lock)
    const sidebarMenu = document.querySelector('.sidebar-menu');
    if (sidebarMenu) {
      const restoreScrollPos = () => {
        const savedScroll = sessionStorage.getItem('sing_sidebar_scroll');
        if (savedScroll !== null) {
          sidebarMenu.scrollTop = parseInt(savedScroll, 10);
        }
      };
      restoreScrollPos();
      requestAnimationFrame(restoreScrollPos);
      setTimeout(restoreScrollPos, 30);
      setTimeout(restoreScrollPos, 100);

      sidebarMenu.addEventListener('scroll', function () {
        sessionStorage.setItem('sing_sidebar_scroll', sidebarMenu.scrollTop.toString());
      }, { passive: true });
    }

    // Save scroll on unload and on every link click
    window.addEventListener('beforeunload', function () {
      if (sidebarMenu) {
        sessionStorage.setItem('sing_sidebar_scroll', sidebarMenu.scrollTop.toString());
      }
    });

    // Click Ripple Micro-interaction for all sidebar links
    const allSidebarLinks = document.querySelectorAll('.sidebar-link');
    allSidebarLinks.forEach(link => {
      link.addEventListener('click', function (e) {
        createRippleEffect(e, this);
        if (sidebarMenu) {
          sessionStorage.setItem('sing_sidebar_scroll', sidebarMenu.scrollTop.toString());
        }
        if (this.getAttribute('href') && !this.getAttribute('href').startsWith('javascript')) {
          sessionStorage.setItem('sing_sidebar_navigating', 'true');
          document.cookie = "sing_sidebar_hover=true; path=/; max-age=15; SameSite=Lax";
        }
      });
    });

    // Hover & Interaction Management (Flatlogic Sing App signature: overlay hover with zero page displacement)
    if (appSidebar) {
      let isHoveringSidebar = false;

      function setSidebarStatePersistence(active) {
        if (active) {
          try {
            sessionStorage.setItem('sing_sidebar_navigating', 'true');
            document.cookie = "sing_sidebar_hover=true; path=/; max-age=15; SameSite=Lax";
          } catch (e) {}
        } else {
          try {
            sessionStorage.removeItem('sing_sidebar_navigating');
            document.cookie = "sing_sidebar_hover=; path=/; max-age=0; SameSite=Lax";
          } catch (e) {}
        }
      }

      let resizeDebounce = null;
      function dispatchLayoutResize() {
        clearTimeout(resizeDebounce);
        resizeDebounce = setTimeout(() => {
          window.dispatchEvent(new Event('resize'));
        }, 310);
      }

      function expandSidebar() {
        if (document.body.classList.contains('sidebar-collapsed')) {
          appSidebar.classList.add('is-expanded-hold');
          document.body.classList.add('sidebar-hover-active');
          document.documentElement.classList.add('sidebar-hover-active');
          setSidebarStatePersistence(true);
          dispatchLayoutResize();
        }
      }

      function collapseSidebar() {
        appSidebar.classList.remove('is-expanded-hold');
        document.body.classList.remove('sidebar-hover-active');
        document.documentElement.classList.remove('sidebar-hover-active');
        setSidebarStatePersistence(false);
        dispatchLayoutResize();
      }

      // Check if arriving from a navigation click inside the sidebar
      if (sessionStorage.getItem('sing_sidebar_navigating') === 'true' || document.cookie.indexOf('sing_sidebar_hover=true') !== -1) {
        expandSidebar();
        isHoveringSidebar = true;
      }

      appSidebar.addEventListener('mouseenter', function () {
        isHoveringSidebar = true;
        expandSidebar();
      });

      appSidebar.addEventListener('mouseleave', function (e) {
        if (e.clientX > 260 || e.clientX <= 0 || e.clientY <= 0 || e.clientY >= window.innerHeight) {
          isHoveringSidebar = false;
          collapseSidebar();
        }
      });

      // Mousemove watcher: seamlessly maintain open state while cursor is in sidebar (width <= 260px)
      // and collapse cleanly when the mouse moves into the main content (x > 260px)
      document.addEventListener('mousemove', function (e) {
        if (window.innerWidth > 992 && document.body.classList.contains('sidebar-collapsed')) {
          if (e.clientX <= 80 && !isHoveringSidebar) {
            isHoveringSidebar = true;
            expandSidebar();
          } else if (e.clientX > 260 && isHoveringSidebar) {
            isHoveringSidebar = false;
            collapseSidebar();
          }
        }
      });

      // Keep open when clicking any element/button/submenu inside sidebar
      appSidebar.addEventListener('click', function () {
        isHoveringSidebar = true;
        expandSidebar();
        setSidebarStatePersistence(true);
      });
    }
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
        const card = fullscreenBtn.closest('.sing-card, .card');
        if (card) {
          card.classList.toggle('is-fullscreen');
          const icon = fullscreenBtn.querySelector('i');
          if (icon) {
            icon.classList.toggle('bi-fullscreen');
            icon.classList.toggle('bi-fullscreen-exit');
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
  }

  // ==========================================================================
  // DROPDOWNS
  // ==========================================================================
  function initDropdowns() {
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
    toast: showToast
  };

  // Auto initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.SingApp.init);
  } else {
    window.SingApp.init();
  }
})();
