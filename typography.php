<?php
$pageTitle = 'Tipografía & Guía de Temas';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="app-main">
  <?php require_once __DIR__ . '/includes/navbar.php'; ?>

  <main class="app-content">
    <!-- Page Header & Breadcrumbs -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Tipografía & Guía de Temas</h1>
        <?php render_breadcrumb(['Componentes' => '#', 'Tipografía' => '']); ?>
      </div>
      <div class="page-actions">
        <button class="btn btn-outline-primary btn-sm" onclick="SingTheme.toggle()">
          <i class="bi bi-moon-stars"></i> Alternar Tema
        </button>
      </div>
    </div>

    <!-- 1. Paleta de Colores & Tokens CSS -->
    <div class="sing-card" style="margin-bottom: 1.5rem;">
      <div class="card-header">
        <div>
          <h2 class="card-title"><i class="bi bi-palette text-primary"></i> Paleta de Colores y Tokens CSS</h2>
          <div class="card-subtitle">Usa estas variables CSS para crear cualquier vista nueva y mantener la compatibilidad con el modo oscuro</div>
        </div>
      </div>
      <div class="card-body">
        <div class="grid grid-cols-12" style="gap: 1rem;">
          <!-- Primary -->
          <div class="col-3">
            <div style="background: var(--color-primary); color: #fff; padding: 1.25rem; border-radius: var(--radius-md); text-align: center;">
              <div style="font-weight: 700; font-size: 1.1rem;">Primary</div>
              <div style="font-size: 0.8rem; opacity: 0.9;"><code>--color-primary</code></div>
            </div>
          </div>
          <!-- Success -->
          <div class="col-3">
            <div style="background: var(--color-success); color: #fff; padding: 1.25rem; border-radius: var(--radius-md); text-align: center;">
              <div style="font-weight: 700; font-size: 1.1rem;">Success</div>
              <div style="font-size: 0.8rem; opacity: 0.9;"><code>--color-success</code></div>
            </div>
          </div>
          <!-- Warning -->
          <div class="col-3">
            <div style="background: var(--color-warning); color: #fff; padding: 1.25rem; border-radius: var(--radius-md); text-align: center;">
              <div style="font-weight: 700; font-size: 1.1rem;">Warning</div>
              <div style="font-size: 0.8rem; opacity: 0.9;"><code>--color-warning</code></div>
            </div>
          </div>
          <!-- Danger -->
          <div class="col-3">
            <div style="background: var(--color-danger); color: #fff; padding: 1.25rem; border-radius: var(--radius-md); text-align: center;">
              <div style="font-weight: 700; font-size: 1.1rem;">Danger</div>
              <div style="font-size: 0.8rem; opacity: 0.9;"><code>--color-danger</code></div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-12" style="gap: 1rem; margin-top: 1rem;">
          <!-- Surface Card -->
          <div class="col-4">
            <div style="background: var(--bg-surface); color: var(--text-primary); border: 1px solid var(--border-color); padding: 1.25rem; border-radius: var(--radius-md); text-align: center;">
              <div style="font-weight: 700;">Superficie de Tarjeta</div>
              <div style="font-size: 0.8rem; color: var(--text-muted);"><code>--bg-surface</code> / <code>--card-bg</code></div>
            </div>
          </div>
          <!-- Body Background -->
          <div class="col-4">
            <div style="background: var(--bg-body); color: var(--text-primary); border: 1px solid var(--border-color); padding: 1.25rem; border-radius: var(--radius-md); text-align: center;">
              <div style="font-weight: 700;">Fondo Principal</div>
              <div style="font-size: 0.8rem; color: var(--text-muted);"><code>--bg-body</code></div>
            </div>
          </div>
          <!-- Borders -->
          <div class="col-4">
            <div style="background: var(--bg-subtle); color: var(--text-primary); border: 1px solid var(--border-color); padding: 1.25rem; border-radius: var(--radius-md); text-align: center;">
              <div style="font-weight: 700;">Bordes y Líneas</div>
              <div style="font-size: 0.8rem; color: var(--text-muted);"><code>--border-color</code></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Escala Tipográfica -->
    <div class="grid grid-cols-12">
      <!-- Encabezados -->
      <div class="col-6">
        <div class="sing-card">
          <div class="card-header">
            <h2 class="card-title"><i class="bi bi-fonts text-info"></i> Jerarquía de Encabezados</h2>
          </div>
          <div class="card-body">
            <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">H1. Encabezado de Página Principal</h1>
            <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">H2. Encabezado de Sección o Módulo</h2>
            <h3 style="font-size: 1.3rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);">H3. Título de Tarjeta o Widget</h3>
            <h4 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);">H4. Subtítulo o Grupo</h4>
            <h5 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);">H5. Título Menor</h5>
            <h6 style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">H6. Encabezado de Tabla o Label</h6>
          </div>
        </div>
      </div>

      <!-- Párrafos, Bloques y Citas -->
      <div class="col-6">
        <div class="sing-card">
          <div class="card-header">
            <h2 class="card-title"><i class="bi bi-card-text text-success"></i> Texto y Bloques de Código</h2>
          </div>
          <div class="card-body">
            <p style="color: var(--text-primary); margin-bottom: 1rem; line-height: 1.7;">
              <strong>Texto Estándar:</strong> Este texto utiliza <code>var(--text-primary)</code> asegurando legibilidad óptima tanto sobre fondos claros como oscuros sin necesidad de añadir reglas condicionales complejas.
            </p>

            <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.9rem;">
              <em>Texto Secundario/Muted:</em> Ideal para fechas, descripciones auxiliares y metadatos con <code>var(--text-muted)</code>.
            </p>

            <div style="background: var(--bg-surface-hover); border-left: 4px solid var(--color-primary); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem;">
              <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary);">Ejemplo de Estructura en PHP para Nuevas Vistas:</div>
              <pre style="margin-top: 0.5rem; font-size: 0.82rem; color: var(--color-primary); overflow-x: auto;"><code>&lt;?php
$pageTitle = 'Nombre de Vista';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?&gt;
&lt;div class="app-main"&gt;
  &lt;?php require_once __DIR__ . '/includes/navbar.php'; ?&gt;
  &lt;main class="app-content"&gt;
    &lt;!-- Tu contenido aquí heredando el tema --&gt;
  &lt;/main&gt;
  &lt;?php require_once __DIR__ . '/includes/footer.php'; ?&gt;
&lt;?php require_once __DIR__ . '/includes/scripts.php'; ?&gt;</code></pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php require_once __DIR__ . '/includes/scripts.php'; ?>
