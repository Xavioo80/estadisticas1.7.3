<?php
$pageTitle = 'Formularios & Controles';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="app-main">
  <?php require_once __DIR__ . '/includes/navbar.php'; ?>

  <main class="app-content">
    <!-- Page Header & Breadcrumbs -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Formularios & Controles</h1>
        <?php render_breadcrumb(['Componentes' => '#', 'Formularios' => '']); ?>
      </div>
      <div class="page-actions">
        <button class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('sampleForm').reset(); SingApp.toast({title: 'Formulario', message: 'Campos restablecidos', type: 'info'})">
          <i class="bi bi-arrow-counterclockwise"></i> Limpiar
        </button>
      </div>
    </div>

    <!-- Forms Grid -->
    <div class="grid grid-cols-12">
      <!-- 1. Formulario Principal de Configuración -->
      <div class="col-7">
        <div class="sing-card">
          <div class="card-header">
            <div>
              <h2 class="card-title"><i class="bi bi-pencil-square text-primary"></i> Datos Generales & Perfil</h2>
              <div class="card-subtitle">Formularios modernos con soporte nativo de tema oscuro</div>
            </div>
            <div class="card-actions">
              <button class="card-action-btn" data-action="collapse"><i class="bi bi-chevron-up"></i></button>
            </div>
          </div>
          <div class="card-body">
            <form id="sampleForm" onsubmit="event.preventDefault(); SingApp.toast({title: 'Guardado', message: 'Los datos del formulario se han guardado con éxito.', type: 'success'});">
              <!-- Grid Row: Nombre y Apellido -->
              <div class="grid grid-cols-12" style="gap: 1rem;">
                <div class="col-6">
                  <div class="form-group">
                    <label class="form-label" for="firstName">Nombre *</label>
                    <input type="text" id="firstName" class="form-control" placeholder="Ej. Alejandro" value="Alexandre" required>
                  </div>
                </div>
                <div class="col-6">
                  <div class="form-group">
                    <label class="form-label" for="lastName">Apellido *</label>
                    <input type="text" id="lastName" class="form-control" placeholder="Ej. Rivera" value="Rivera" required>
                  </div>
                </div>
              </div>

              <!-- Correo electrónico con grupo de iconos -->
              <div class="form-group">
                <label class="form-label" for="userEmail">Correo Electrónico *</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                  <input type="email" id="userEmail" class="form-control" placeholder="correo@dominio.com" value="alex.rivera@singapp.io" required>
                </div>
              </div>

              <!-- Selector y Campo de Rol -->
              <div class="grid grid-cols-12" style="gap: 1rem;">
                <div class="col-6">
                  <div class="form-group">
                    <label class="form-label" for="userRole">Rol en el Sistema</label>
                    <select id="userRole" class="form-select">
                      <option value="admin" selected>Administrador</option>
                      <option value="editor">Editor de Contenido</option>
                      <option value="viewer">Visualizador / Auditor</option>
                    </select>
                  </div>
                </div>
                <div class="col-6">
                  <div class="form-group">
                    <label class="form-label" for="userPhone">Teléfono de Contacto</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                      <input type="tel" id="userPhone" class="form-control" placeholder="+1 (555) 000-0000" value="+1 (555) 349-9201">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Biografía / Notas -->
              <div class="form-group">
                <label class="form-label" for="userBio">Biografía / Observaciones</label>
                <textarea id="userBio" class="form-control" rows="3" placeholder="Escriba una breve descripción...">Desarrollador fullstack a cargo de la integración de Sing App PHP con modo oscuro adaptado.</textarea>
              </div>

              <!-- Switches de Preferencias -->
              <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <div class="form-label" style="margin-bottom: 0.75rem;">Preferencias de Notificaciones</div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                  <label class="form-switch">
                    <input type="checkbox" checked>
                    <span class="switch-slider"></span>
                    <span style="font-size: 0.88rem; color: var(--text-primary);">Notificaciones de inicio de sesión por correo</span>
                  </label>

                  <label class="form-switch">
                    <input type="checkbox" checked>
                    <span class="switch-slider"></span>
                    <span style="font-size: 0.88rem; color: var(--text-primary);">Alertas de rendimiento de servidor en tiempo real</span>
                  </label>

                  <label class="form-switch">
                    <input type="checkbox">
                    <span class="switch-slider"></span>
                    <span style="font-size: 0.88rem; color: var(--text-primary);">Recibir boletín de novedades técnicas</span>
                  </label>
                </div>
              </div>

              <!-- Botones de Acción -->
              <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-check-circle"></i> Guardar Cambios
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="SingApp.toast({title: 'Cancelado', message: 'No se realizaron cambios.', type: 'warning'})">
                  Cancelar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- 2. Switches, Checkboxes, Radios & Validaciones -->
      <div class="col-5">
        <!-- Controles de Selección -->
        <div class="sing-card" style="margin-bottom: 1.5rem;">
          <div class="card-header">
            <h2 class="card-title"><i class="bi bi-ui-checks text-success"></i> Checkboxes & Radios</h2>
          </div>
          <div class="card-body">
            <div class="form-label" style="margin-bottom: 0.75rem;">Selección Múltiple</div>
            <label class="form-check">
              <input type="checkbox" class="form-check-input" checked>
              <span style="color: var(--text-primary); font-size: 0.88rem;">Habilitar autenticación de 2 pasos (2FA)</span>
            </label>
            <label class="form-check">
              <input type="checkbox" class="form-check-input" checked>
              <span style="color: var(--text-primary); font-size: 0.88rem;">Registro de auditoría en base de datos</span>
            </label>
            <label class="form-check">
              <input type="checkbox" class="form-check-input">
              <span style="color: var(--text-primary); font-size: 0.88rem;">Modo de mantenimiento forzado</span>
            </label>

            <div class="form-label" style="margin-top: 1.25rem; margin-bottom: 0.75rem;">Frecuencia de Backup</div>
            <label class="form-check">
              <input type="radio" name="backupFreq" class="form-check-input" checked>
              <span style="color: var(--text-primary); font-size: 0.88rem;">Diario a las 03:00 AM</span>
            </label>
            <label class="form-check">
              <input type="radio" name="backupFreq" class="form-check-input">
              <span style="color: var(--text-primary); font-size: 0.88rem;">Semanal (Domingos)</span>
            </label>
            <label class="form-check">
              <input type="radio" name="backupFreq" class="form-check-input">
              <span style="color: var(--text-primary); font-size: 0.88rem;">Manual bajo demanda</span>
            </label>
          </div>
        </div>

        <!-- Estados de Validación -->
        <div class="sing-card">
          <div class="card-header">
            <h2 class="card-title"><i class="bi bi-shield-lock text-warning"></i> Estados de Validación</h2>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label class="form-label">Campo Válido</label>
              <input type="text" class="form-control" style="border-color: var(--color-success); box-shadow: 0 0 0 2px var(--color-success-light);" value="usuario_aprobado">
              <div style="font-size: 0.78rem; color: var(--color-success); margin-top: 0.3rem;">
                <i class="bi bi-check-circle"></i> Nombre de usuario disponible.
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Campo con Error</label>
              <input type="text" class="form-control" style="border-color: var(--color-danger); box-shadow: 0 0 0 2px var(--color-danger-light);" value="correo_invalido@">
              <div style="font-size: 0.78rem; color: var(--color-danger); margin-top: 0.3rem;">
                <i class="bi bi-exclamation-triangle"></i> Formato de correo electrónico no válido.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php require_once __DIR__ . '/includes/scripts.php'; ?>
