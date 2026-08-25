# Sing App Dashboard - Versión Modular PHP con Modo Oscuro Adaptado

Esta plantilla es una adaptación completa y profesional de la interfaz de **Sing App Dashboard** (de Flatlogic) construida para proyectos **PHP nativos** (compatible con XAMPP, Apache y servidores PHP 7.4+ y 8.x).

Incluye un sistema integral de **Modo Oscuro y Claro** con tokens de diseño CSS, prevención de parpadeo en la carga (Anti-FOUC), persistencia dual (`localStorage` y cookies para PHP) y recálculo dinámico de gráficos en tiempo real.

---

## 📁 Estructura del Proyecto

```
c:\xampp\htdocs\jquery\
├── assets/
│   ├── css/
│   │   ├── sing-theme.css       # Tokens de variables CSS (Modo Claro / Oscuro)
│   │   ├── sing-layout.css      # Estructura del layout (Sidebar, Navbar, Grid, Responsivo)
│   │   └── sing-components.css  # Tarjetas, botones, tablas, formularios, modales y widgets
│   ├── js/
│   │   ├── sing-theme.js        # Gestor de cambio de tema (Dark/Light + eventos + cookies)
│   │   ├── sing-app.js          # Control de sidebar, dropdowns, acciones de cards y toasts
│   │   └── sing-charts.js       # Gráficos dinámicos con soporte automático de modo oscuro
├── includes/
│   ├── config.php               # Configuración global, detección de tema en PHP y menú activo
│   ├── header.php               # Head HTML, CSS y script anti-FOUC
│   ├── sidebar.php              # Menú lateral interactivo con grupos y submenús
│   ├── navbar.php               # Barra superior con buscador, botón de Dark Mode y perfil
│   ├── footer.php               # Pie de página y copyrights
│   └── scripts.php              # Inclusión de librerías JS (Bootstrap Icons, ApexCharts, Sing App JS)
├── index.php                    # Dashboard principal / Analytics
├── tables.php                   # Vista de tablas interactivas con búsqueda en tiempo real
├── forms.php                    # Vista de formularios y controles modernos
├── charts.php                   # Vista de gráficos estadísticos (ApexCharts)
├── ui-elements.php              # Vista de componentes UI, modales, alertas y widgets
├── typography.php               # Guía de tokens de color, tipografía y componentes base
└── README.md                    # Esta guía
```

---

## 🌓 Cómo Funciona el Sistema de Modo Oscuro

El sistema de Modo Oscuro está diseñado para que **cualquier vista nueva que crees herede automáticamente los estilos correctos** sin escribir CSS adicional:

1. **Tokens de Diseño CSS (`assets/css/sing-theme.css`)**:
   - Variables como `--bg-body`, `--card-bg`, `--text-primary`, `--border-color`, `--color-primary`, etc., cambian sus valores según el atributo `[data-theme="dark"]` o `[data-theme="light"]`.
2. **Prevención de Parpadeo (Anti-FOUC)**:
   - Un script inline ultrarrápido en el `<head>` (`includes/header.php`) detecta `localStorage.getItem('sing_theme')` y aplica el atributo antes de que el navegador renderice la página.
3. **Persistencia Dual (JS + PHP)**:
   - Al hacer clic en el botón de tema (o presionar `Alt + Shift + D`), `sing-theme.js` actualiza el DOM, guarda en `localStorage` y actualiza la cookie `sing_theme`.
   - `includes/config.php` lee la cookie en el servidor (`get_current_theme()`) para renderizar el HTML inicial con el tema exacto.
4. **Sincronización de Gráficos (ApexCharts)**:
   - `assets/js/sing-charts.js` escucha el evento global `sing:theme-change` y actualiza los colores de cuadrícula, ejes y tooltips en tiempo real sin recargar la página.

---

## 🚀 Cómo Crear una Nueva Vista en PHP

Para crear una nueva página (por ejemplo `clientes.php`), simplemente usa la siguiente estructura modular:

```php
<?php
$pageTitle = 'Gestión de Clientes';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="app-main">
  <?php require_once __DIR__ . '/includes/navbar.php'; ?>

  <main class="app-content">
    <!-- Encabezado de Página -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Gestión de Clientes</h1>
        <?php render_breadcrumb(['Módulos' => '#', 'Clientes' => '']); ?>
      </div>
      <div class="page-actions">
        <button class="btn btn-primary btn-sm">
          <i class="bi bi-plus-lg"></i> Nuevo Cliente
        </button>
      </div>
    </div>

    <!-- Contenido de tu vista -->
    <div class="sing-card">
      <div class="card-header">
        <h2 class="card-title">Listado Principal</h2>
      </div>
      <div class="card-body">
        <p>Tu contenido PHP / Base de datos aquí.</p>
      </div>
    </div>
  </main>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php require_once __DIR__ . '/includes/scripts.php'; ?>
```

---

## 🛠 Componentes y Funcionalidades Listas para Usar

### 1. Tarjetas con Barra de Acciones
```html
<div class="sing-card">
  <div class="card-header">
    <h2 class="card-title">Mi Tarjeta</h2>
    <div class="card-actions">
      <button class="card-action-btn" data-action="reload" title="Recargar"><i class="bi bi-arrow-clockwise"></i></button>
      <button class="card-action-btn" data-action="collapse" title="Colapsar"><i class="bi bi-chevron-up"></i></button>
      <button class="card-action-btn" data-action="fullscreen" title="Pantalla Completa"><i class="bi bi-fullscreen"></i></button>
      <button class="card-action-btn" data-action="close" title="Cerrar"><i class="bi bi-x-lg"></i></button>
    </div>
  </div>
  <div class="card-body">
    Contenido del widget...
  </div>
</div>
```

### 2. Disparador de Notificaciones Toast en JavaScript
```javascript
SingApp.toast({
  title: 'Operación Exitosa',
  message: 'El registro se ha guardado en la base de datos.',
  type: 'success', // 'primary' | 'success' | 'warning' | 'danger'
  duration: 4000
});
```

### 3. Alternar Tema desde Cualquier Elemento
```html
<button onclick="SingTheme.toggle()">Cambiar Modo Oscuro / Claro</button>
```

---

## 🌐 Cómo Ejecutar en XAMPP

1. Inicia **Apache** en el Panel de Control de XAMPP.
2. Abre tu navegador y accede a:
   ```
   http://localhost/jquery/
   ```
3. Podrás navegar por el Dashboard, interactuar con el interruptor de modo oscuro en la barra superior, colapsar el menú lateral, filtrar tablas y probar todos los controles.
