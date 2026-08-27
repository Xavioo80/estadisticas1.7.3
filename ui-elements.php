<?php
$pageTitle = 'Elementos UI & Componentes';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="app-main">
  <?php require_once __DIR__ . '/includes/navbar.php'; ?>

  <main class="app-content">
    <!-- Page Header & Breadcrumbs -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Elementos UI & Componentes</h1>
        <?php render_breadcrumb(['Componentes' => '#', 'Elementos UI' => '']); ?>
      </div>
      <div class="page-actions">
        <button class="btn btn-primary btn-sm" onclick="openDemoModal()">
          <i class="bi bi-window-stack"></i> Abrir Modal Demo
        </button>
      </div>
    </div>

    <!-- 1. Botones & Badges -->
    <div class="grid grid-cols-12" style="margin-bottom: 1.5rem;">
      <!-- Buttons Showcase -->
      <div class="col-7">
        <div class="sing-card">
          <div class="card-header">
            <h2 class="card-title"><i class="bi bi-hand-index-thumb-fill text-primary"></i> Botones Interactivos</h2>
          </div>
          <div class="card-body">
            <div class="form-label">Variantes Sólidas</div>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem;">
              <button class="btn btn-primary">Primary</button>
              <button class="btn btn-success">Success</button>
              <button class="btn btn-danger">Danger</button>
              <button class="btn btn-warning">Warning</button>
              <button class="btn btn-gradient-primary">Gradient</button>
              <button class="btn btn-subtle">Subtle</button>
            </div>

            <div class="form-label">Variantes Outline</div>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem;">
              <button class="btn btn-outline-primary">Outline Primary</button>
              <button class="btn btn-outline-secondary">Outline Secondary</button>
              <button class="btn btn-outline-primary btn-sm">Small</button>
              <button class="btn btn-primary btn-lg">Large</button>
            </div>

            <div class="form-label">Botones con Iconos & Disparadores de Toast</div>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
              <button class="btn btn-success btn-sm" onclick="SingApp.toast({title: 'Éxito', message: 'Acción ejecutada correctamente.', type: 'success'})">
                <i class="bi bi-check2"></i> Toast Éxito
              </button>
              <button class="btn btn-danger btn-sm" onclick="SingApp.toast({title: 'Error', message: 'Ocurrió un fallo en la solicitud.', type: 'danger'})">
                <i class="bi bi-x-circle"></i> Toast Error
              </button>
              <button class="btn btn-warning btn-sm" onclick="SingApp.toast({title: 'Atención', message: 'Memoria al 85% de capacidad.', type: 'warning'})">
                <i class="bi bi-exclamation-triangle"></i> Toast Alerta
              </button>
              <button class="btn btn-primary btn-sm" onclick="SingApp.toast({title: 'Información', message: 'Mensaje informativo enviado.', type: 'primary'})">
                <i class="bi bi-info-circle"></i> Toast Info
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Badges & Indicadores -->
      <div class="col-5">
        <div class="sing-card">
          <div class="card-header">
            <h2 class="card-title"><i class="bi bi-tag-fill text-purple"></i> Badges & Estados</h2>
          </div>
          <div class="card-body">
            <div class="form-label">Badges Soft (Recomendados en Sing App)</div>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem;">
              <span class="badge badge-soft-primary">Primary</span>
              <span class="badge badge-soft-success">Success</span>
              <span class="badge badge-soft-warning">Warning</span>
              <span class="badge badge-soft-danger">Danger</span>
              <span class="badge badge-soft-info">Info</span>
              <span class="badge badge-soft-purple">Purple</span>
            </div>

            <div class="form-label">Badges Sólidos & Pills</div>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem;">
              <span class="badge badge-primary badge-pill">Pill Primary</span>
              <span class="badge badge-success badge-pill">Pill Success</span>
              <span class="badge badge-danger badge-pill">Pill Danger</span>
            </div>

            <div class="form-label">Barras de Progreso Dinámicas</div>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
              <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.25rem;">
                  <span>Carga de Procesos</span>
                  <span style="font-weight: 600;">80%</span>
                </div>
                <div style="height: 8px; background: var(--border-color); border-radius: var(--radius-full); overflow: hidden;">
                  <div style="width: 80%; height: 100%; background: linear-gradient(90deg, var(--color-primary), var(--color-purple));"></div>
                </div>
              </div>
              <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.25rem;">
                  <span>Sincronización MySQL</span>
                  <span style="font-weight: 600;">100%</span>
                </div>
                <div style="height: 8px; background: var(--border-color); border-radius: var(--radius-full); overflow: hidden;">
                  <div style="width: 100%; height: 100%; background: var(--color-success);"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Alertas & Tarjetas Interactivas -->
    <div class="grid grid-cols-12">
      <!-- Banners de Alerta -->
      <div class="col-6">
        <div class="sing-card">
          <div class="card-header">
            <h2 class="card-title"><i class="bi bi-bell-fill text-warning"></i> Banners de Alerta</h2>
          </div>
          <div class="card-body">
            <div class="alert alert-primary">
              <i class="bi bi-info-circle-fill" style="font-size: 1.2rem;"></i>
              <div>
                <strong>Mensaje Primario:</strong> Esta es una alerta informativa de estilo suave adaptada al tema activo.
              </div>
              <button type="button" class="alert-dismiss" onclick="this.closest('.alert').remove()"><i class="bi bi-x"></i></button>
            </div>

            <div class="alert alert-success">
              <i class="bi bi-check-circle-fill" style="font-size: 1.2rem;"></i>
              <div>
                <strong>Operación Exitosa:</strong> Los registros se procesaron con 0 incidencias.
              </div>
              <button type="button" class="alert-dismiss" onclick="this.closest('.alert').remove()"><i class="bi bi-x"></i></button>
            </div>

            <div class="alert alert-danger">
              <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.2rem;"></i>
              <div>
                <strong>Atención Crítica:</strong> Verifique las credenciales de base de datos en config.
              </div>
              <button type="button" class="alert-dismiss" onclick="this.closest('.alert').remove()"><i class="bi bi-x"></i></button>
            </div>
          </div>
        </div>
      </div>

      <!-- Tarjeta Interactiva con Toolbar -->
      <div class="col-6">
        <div class="sing-card">
          <div class="card-header">
            <div>
              <h2 class="card-title"><i class="bi bi-window text-info"></i> Widget con Controles</h2>
              <div class="card-subtitle">Prueba los botones superiores para interactuar con esta tarjeta</div>
            </div>
            <div class="card-actions">
              <button class="card-action-btn" data-action="reload" title="Recargar con Spinner"><i class="bi bi-arrow-clockwise"></i></button>
              <button class="card-action-btn" data-action="collapse" title="Colapsar Contenido"><i class="bi bi-chevron-up"></i></button>
              <button class="card-action-btn" data-action="fullscreen" title="Pantalla Completa"><i class="bi bi-fullscreen"></i></button>
              <button class="card-action-btn" data-action="close" title="Cerrar Tarjeta"><i class="bi bi-x-lg"></i></button>
            </div>
          </div>
          <div class="card-body">
            <p style="color: var(--text-secondary); margin-bottom: 1rem;">
              Esta tarjeta implementa todas las funciones de widgets interactivas de <strong>Sing App</strong>:
            </p>
            <ul style="padding-left: 1.25rem; color: var(--text-secondary); line-height: 1.8;">
              <li><strong>Recargar (<i class="bi bi-arrow-clockwise"></i>):</strong> Despliega un overlay animado de carga.</li>
              <li><strong>Colapsar (<i class="bi bi-chevron-up"></i>):</strong> Oculta y muestra el cuerpo de la tarjeta.</li>
              <li><strong>Pantalla Completa (<i class="bi bi-fullscreen"></i>):</strong> Expande el widget ocupando toda la ventana.</li>
              <li><strong>Cerrar (<i class="bi bi-x-lg"></i>):</strong> Elimina la tarjeta con animación suave.</li>
            </ul>
          </div>
          <div class="card-footer">
            <span style="font-size: 0.82rem; color: var(--text-muted);">Sincronizado vía JavaScript</span>
            <button class="btn btn-outline-primary btn-sm" onclick="SingApp.toast({title: 'Footer Card', message: 'Acción ejecutada desde footer', type: 'info'})">
              Acción Rápida
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Demo Modal -->
  <div id="demoModal" style="display: none; position: fixed; inset: 0; z-index: var(--z-modal); background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); width: 100%; max-width: 500px; box-shadow: var(--shadow-xl); overflow: hidden; animation: fadeInDown 0.2s ease-out;">
      <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary); margin: 0;">Ventana Modal Sing App</h3>
        <button type="button" class="alert-dismiss" onclick="closeDemoModal()"><i class="bi bi-x-lg"></i></button>
      </div>
      <div style="padding: 1.5rem; color: var(--text-secondary);">
        <p style="margin-bottom: 1rem;">
          Este cuadro de diálogo se adapta automáticamente al <strong>Modo Oscuro / Claro</strong> utilizando las variables del sistema de diseño.
        </p>
        <div class="alert alert-primary" style="margin-bottom: 0;">
          <i class="bi bi-lightbulb-fill"></i> Puedes insertar formularios, tablas o cualquier contenido PHP dentro de este contenedor.
        </div>
      </div>
      <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); background: var(--bg-surface-hover); display: flex; justify-content: flex-end; gap: 0.5rem;">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="closeDemoModal()">Cerrar</button>
        <button type="button" class="btn btn-primary btn-sm" onclick="SingApp.toast({title: 'Modal', message: 'Cambios guardados en el modal', type: 'success'}); closeDemoModal();">Guardar Cambios</button>
      </div>
    </div>
  </div>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- Modal Functions -->
<script>
  function openDemoModal() {
    document.getElementById('demoModal').style.display = 'flex';
  }
  function closeDemoModal() {
    document.getElementById('demoModal').style.display = 'none';
  }
</script>

<?php require_once __DIR__ . '/includes/scripts.php'; ?>
