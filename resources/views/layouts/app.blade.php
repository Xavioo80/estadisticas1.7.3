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
  @stack('styles')
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

<!-- Scripts -->
<script src="{{ asset('assets/js/sing-theme.js') }}?v={{ @filemtime(public_path('assets/js/sing-theme.js')) }}"></script>
<script src="{{ asset('assets/js/sing-app.js') }}?v={{ @filemtime(public_path('assets/js/sing-app.js')) }}"></script>
<script src="{{ asset('assets/js/sing-charts.js') }}?v={{ @filemtime(public_path('assets/js/sing-charts.js')) }}"></script>
@stack('scripts')

</body>
</html>
