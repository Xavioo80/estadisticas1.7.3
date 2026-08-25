<?php
require_once __DIR__ . '/config.php';
$pageTitle = isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME . ' Dashboard';
$initialTheme = get_current_theme();
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?= htmlspecialchars($initialTheme); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle); ?></title>
  
  <!-- Anti-FOUC (Flash of Unstyled Color Scheme & Sidebar State) inline script -->
  <script>
    (function() {
      try {
        var theme = localStorage.getItem('sing_theme');
        if (theme) {
          document.documentElement.setAttribute('data-theme', theme);
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
          document.documentElement.setAttribute('data-theme', 'dark');
        }

        var isSidebarPinned = localStorage.getItem('sing_sidebar_collapsed');
        if (isSidebarPinned === 'false') {
          document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.remove('sidebar-collapsed');
          });
        }

        if (sessionStorage.getItem('sing_sidebar_navigating') === 'true' || document.cookie.indexOf('sing_sidebar_hover=true') !== -1) {
          document.documentElement.classList.add('sidebar-hover-active');
          document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('sidebar-hover-active');
            var appSidebar = document.querySelector('.app-sidebar');
            if (appSidebar) appSidebar.classList.add('is-expanded-hold');
          });
        }
      } catch (e) {}
    })();
  </script>

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap Icons CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- ApexCharts CDN -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <!-- Third-party Plugins CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

  <!-- Sing App Core Stylesheets -->
  <link rel="stylesheet" href="assets/css/sing-theme.css">
  <link rel="stylesheet" href="assets/css/sing-layout.css">
  <link rel="stylesheet" href="assets/css/sing-components.css">
</head>
<body class="sidebar-collapsed" data-theme="<?= htmlspecialchars($initialTheme); ?>">
<div class="app-wrapper">
