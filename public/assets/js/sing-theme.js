/**
 * Sing App Theme Switcher Engine
 * Handles Dark/Light Mode switching, dual persistence (safe localStorage + cookies for PHP),
 * and custom events for Chart re-rendering without page refresh.
 */

(function () {
  'use strict';

  const STORAGE_KEY = 'sing_theme';
  const COOKIE_KEY = 'sing_theme';
  const THEME_LIGHT = 'light';
  const THEME_DARK = 'dark';

  // Safe Memory Fallback for environments with Tracking Prevention / Blocked Storage
  const memoryStore = {};

  function safeGetStorage(key) {
    try {
      if (typeof window !== 'undefined' && window.localStorage) {
        return window.localStorage.getItem(key);
      }
    } catch (e) {}
    return memoryStore[key] || null;
  }

  function safeSetStorage(key, value) {
    memoryStore[key] = value;
    try {
      if (typeof window !== 'undefined' && window.localStorage) {
        window.localStorage.setItem(key, value);
      }
    } catch (e) {}
  }

  /**
   * Set cookie helper for PHP synchronization
   */
  function setCookie(name, value, days) {
    try {
      let expires = '';
      if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = '; expires=' + date.toUTCString();
      }
      document.cookie = name + '=' + (value || '') + expires + '; path=/; SameSite=Lax';
    } catch (e) {}
  }

  /**
   * Get current active theme
   */
  function getTheme() {
    return document.documentElement.getAttribute('data-theme') || THEME_DARK;
  }

  /**
   * Apply theme to DOM, localStorage and cookies
   */
  function setTheme(theme) {
    const validTheme = theme === THEME_LIGHT ? THEME_LIGHT : THEME_DARK;

    document.documentElement.setAttribute('data-theme', validTheme);
    if (document.body) {
      document.body.setAttribute('data-theme', validTheme);
    }

    try {
      if (typeof window !== 'undefined' && window.localStorage) {
        window.localStorage.setItem('sing_theme', validTheme);
      }
    } catch(e) {}

    safeSetStorage(STORAGE_KEY, validTheme);

    // Set cookie for PHP server-side recognition (1 year)
    setCookie(COOKIE_KEY, validTheme, 365);

    // Notify listeners (e.g. ApexCharts, custom views)
    window.dispatchEvent(new CustomEvent('sing:theme-change', {
      detail: { theme: validTheme }
    }));
  }

  /**
   * Toggle between Dark and Light mode
   */
  function toggleTheme() {
    const currentTheme = getTheme();
    const newTheme = currentTheme === THEME_DARK ? THEME_LIGHT : THEME_DARK;
    setTheme(newTheme);
    return newTheme;
  }

  /**
   * Initialize theme listeners when DOM is ready
   */
  function initTheme() {
    // Delegated click listener on document for all theme toggle buttons and icons
    document.addEventListener('click', function (e) {
      const toggle = e.target.closest('[data-toggle="theme"], .theme-toggle-btn');
      if (toggle) {
        e.preventDefault();
        toggleTheme();
      }
    });

    // Keyboard shortcut: Alt + Shift + D
    document.addEventListener('keydown', function (e) {
      if (e.altKey && e.shiftKey && (e.key === 'D' || e.key === 'd')) {
        e.preventDefault();
        toggleTheme();
      }
    });

    // Listen to system preference changes if no manual preference stored
    if (!safeGetStorage(STORAGE_KEY)) {
      if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
          if (!safeGetStorage(STORAGE_KEY)) {
            setTheme(e.matches ? THEME_DARK : THEME_LIGHT);
          }
        });
      }
    }
  }

  // Expose to global SingApp namespace
  window.SingTheme = {
    get: getTheme,
    set: setTheme,
    toggle: toggleTheme,
    init: initTheme,
    safeGet: safeGetStorage,
    safeSet: safeSetStorage
  };

  // Auto initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
  } else {
    initTheme();
  }
})();
