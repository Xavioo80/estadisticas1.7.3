@extends('layouts.app')

@section('title', 'Alerta Not - Portal y Herramientas Integradas')

@push('styles')
<style>
  .app-content {
    padding: 0.65rem 1.25rem 0.5rem !important;
    height: calc(100vh - var(--navbar-height) - var(--footer-height)) !important;
    max-height: calc(100vh - var(--navbar-height) - var(--footer-height)) !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
  }

  /* Encabezado Superior */
  .alerta-header-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 6px);
    padding: 0.45rem 0.75rem;
    margin-bottom: 0.45rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow-sm);
    flex-shrink: 0;
  }

  .alerta-header-title {
    display: flex;
    align-items: center;
    gap: 0.65rem;
  }

  .alerta-header-icon {
    width: 34px;
    height: 34px;
    border-radius: 6px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(245, 158, 11, 0.25);
  }

  /* Barra de Pestañas Tipo Navegador */
  .alerta-tabs-container {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 6px) var(--radius-md, 6px) 0 0;
    border-bottom: none;
    padding: 0.35rem 0.65rem 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
    gap: 0.5rem;
  }

  .alerta-tabs-list {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    overflow-x: auto;
    scrollbar-width: none;
    margin: 0;
    padding: 0;
    list-style: none;
  }

  .alerta-tabs-list::-webkit-scrollbar {
    display: none;
  }

  .alerta-tab-item {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.4rem 0.85rem;
    border-radius: 6px 6px 0 0;
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-muted);
    background: var(--bg-body);
    border: 1px solid var(--border-color);
    border-bottom: none;
    text-decoration: none !important;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
    position: relative;
    top: 1px;
  }

  .alerta-tab-item:hover {
    color: var(--text-primary);
    background: var(--bg-surface-hover, rgba(255, 255, 255, 0.05));
  }

  .alerta-tab-item.active {
    color: var(--color-primary, #3b82f6);
    background: var(--bg-surface);
    border-color: var(--border-color);
    border-top: 2px solid var(--color-primary, #3b82f6);
    z-index: 2;
  }

  .alerta-tab-item.active i {
    color: var(--color-primary, #3b82f6);
  }

  .alerta-tab-add-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 4px;
    background: transparent;
    border: 1px dashed var(--border-color);
    color: var(--text-muted);
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.15s ease;
    margin-left: 0.25rem;
    position: relative;
    top: -1px;
  }

  .alerta-tab-add-btn:hover {
    color: #10b981;
    border-color: #10b981;
    background: rgba(16, 185, 129, 0.12);
    transform: scale(1.05);
  }

  /* Contenedor del Visor Embebido (Iframe) */
  .alerta-browser-frame {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 0 0 var(--radius-md, 6px) var(--radius-md, 6px);
    box-shadow: var(--shadow-sm);
    flex: 1 1 0%;
    min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
  }

  .alerta-iframe-container {
    flex: 1 1 0%;
    min-height: 0;
    width: 100%;
    height: 100%;
    position: relative;
    background: #ffffff;
  }

  .alerta-iframe {
    width: 100%;
    height: 100%;
    border: none;
    display: block;
    background: #ffffff;
  }

  /* Spinner de Carga */
  .alerta-loader-overlay {
    position: absolute;
    inset: 0;
    background: var(--bg-surface);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.65rem;
    z-index: 10;
    transition: opacity 0.3s ease;
  }

  /* Modal Admin Styles */
  .modal-dark-surface .modal-content {
    background-color: var(--bg-surface, #1e293b);
    color: var(--text-primary, #f8fafc);
    border: 1px solid var(--border-color, #334155);
  }

  .modal-dark-surface .modal-header,
  .modal-dark-surface .modal-footer {
    border-color: var(--border-color, #334155);
  }

  .modal-dark-surface .form-control {
    background-color: var(--bg-body, #0f172a);
    color: var(--text-primary, #f8fafc);
    border: 1px solid var(--border-color, #334155);
  }
</style>
@endpush

@section('content')
<!-- 1. Encabezado de Alerta Not -->
<div class="alerta-header-card no-print">
    <div class="alerta-header-title">
        <div class="alerta-header-icon">
            <i class="bi bi-bell-fill"></i>
        </div>
        <div>
            <h5 class="mb-0 font-weight-bold" style="font-size: 0.95rem; color: var(--text-primary);">
                Alerta Not &bull; Portal y Herramientas Integradas
            </h5>
            <small style="color: var(--text-muted); font-size: 0.72rem; font-weight: 500;">
                Hojas de cálculo y sistemas de vigilancia epidemiológica en tiempo real
            </small>
        </div>
    </div>
    
    <div class="d-flex align-items-center gap-2">
        @if($selectedEnlace)
            <a id="btnPopoutExterno" href="{{ $selectedEnlace->url }}" target="_blank" rel="noopener noreferrer" 
               class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 font-weight-bold px-3" 
               style="height: 32px; font-size: 0.8rem;" title="Abrir en una pestaña externa del navegador">
                <i class="bi bi-box-arrow-up-right"></i> Abrir en Pestaña Externa
            </a>
        @endif

        <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 font-weight-bold px-2.5" 
                onclick="recargarIframe()" style="height: 32px; font-size: 0.8rem; border-color: var(--border-color); color: var(--text-primary);" 
                title="Recargar marco actual">
            <i class="bi bi-arrow-clockwise"></i> Recargar
        </button>

        <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 font-weight-bold px-2.5" 
                onclick="pantallaCompleta()" style="height: 32px; font-size: 0.8rem; border-color: var(--border-color); color: var(--text-primary);" 
                title="Pantalla completa">
            <i class="bi bi-arrows-fullscreen"></i>
        </button>

        <!-- Botón Administrar Pestañas con Clave Distica2026 -->
        <button type="button" class="btn btn-sm btn-warning font-weight-bold text-dark d-inline-flex align-items-center gap-1 px-3" 
                onclick="solicitarAccesoAdmin()" style="height: 32px; font-size: 0.8rem;" 
                title="Configurar y gestionar pestañas permanentes (Clave requerida)">
            <i class="bi bi-gear-fill"></i> Administrar Pestañas
        </button>
    </div>
</div>

<!-- 2. Barra de Pestañas de Navegación -->
<div class="alerta-tabs-container no-print">
    <div class="d-flex align-items-center gap-1" style="overflow-x: auto; max-width: 85%;">
        <ul class="alerta-tabs-list" id="listaPestanasAlerta">
            @forelse($enlaces as $enl)
                <li>
                    <button type="button" 
                            class="alerta-tab-item {{ ($selectedEnlace && $selectedEnlace->id == $enl->id) ? 'active' : '' }}" 
                            onclick="cambiarPestana('{{ $enl->id }}', '{{ addslashes($enl->url) }}', '{{ addslashes($enl->titulo) }}', this)">
                        <i class="{{ $enl->icono ?: 'bi bi-globe' }}"></i>
                        <span>{{ $enl->titulo }}</span>
                    </button>
                </li>
            @empty
                <li class="text-muted p-1 font-size-12">No hay pestañas configuradas.</li>
            @endforelse

            <!-- Botón + Ubicado a la par horizontal directa de las pestañas -->
            <li>
                <button type="button" class="alerta-tab-add-btn" onclick="solicitarCrearPestana()" title="Agregar nueva pestaña permanente (+)">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </li>
        </ul>
    </div>
    
    <div class="d-flex align-items-center pb-1">
        <span id="badgeSecurityState" class="badge badge-secondary px-2 py-1" style="font-size: 0.7rem;">
            <i class="bi bi-shield-lock-fill text-warning mr-1"></i> Modo Seguro
        </span>
    </div>
</div>

<!-- 3. Visor Embebido (Browser Frame) a Pantalla Completa -->
<div class="alerta-browser-frame">
    <!-- Contenedor con Iframe -->
    <div class="alerta-iframe-container" id="iframeContainerBox">
        <div class="alerta-loader-overlay" id="iframeLoader">
            <div class="spinner-border text-warning" role="status" style="width: 2rem; height: 2rem;"></div>
            <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-primary);">Cargando servicio integrado...</span>
        </div>

        <!-- Iframe Embebido para Páginas Web y Google Sheets -->
        @if($selectedEnlace)
            <iframe id="mainAlertaIframe" 
                    src="{{ $selectedEnlace->url }}" 
                    class="alerta-iframe" 
                    allow="camera; microphone; geolocation; clipboard-read; clipboard-write; fullscreen; encrypted-media; autoplay"
                    sandbox="allow-forms allow-modals allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts allow-downloads allow-storage-access-by-user-activation"
                    onload="ocultarCargando()">
            </iframe>
        @else
            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                <i class="bi bi-browser-chrome fa-3x mb-2"></i>
                <p class="font-weight-bold">No hay ninguna página seleccionada</p>
            </div>
        @endif
    </div>
</div>

<!-- 4. Modal de Administración de Pestañas (Protegido por Clave Distica2026) -->
<div class="modal fade modal-dark-surface" id="modalGestionarEnlaces" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">
                    <i class="bi bi-gear-fill text-warning mr-2"></i> Administrador de Pestañas & Enlaces Alerta Not
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body">
                <!-- Formulario para Agregar Nuevo Enlace -->
                <div class="card mb-3" style="background: var(--bg-body); border: 1px solid var(--border-color);">
                    <div class="card-header py-2 font-weight-bold" style="font-size: 0.85rem; background: var(--bg-surface);">
                        <i class="bi bi-plus-circle text-success mr-1"></i> Agregar Nueva Pestaña
                    </div>
                    <div class="card-body p-3">
                        <form id="formCrearEnlace" onsubmit="guardarNuevaPestanaAjax(event)">
                            @csrf
                            <input type="hidden" name="clave" id="modalClaveSeguridad" value="Distica2026">
                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label font-size-12 font-weight-bold">Título de la Pestaña *</label>
                                    <input type="text" id="modalNuevoTitulo" name="titulo" class="form-control form-control-sm" placeholder="Ej: Planilla SNVS" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label font-size-12 font-weight-bold">Icono Bootstrap *</label>
                                    <input type="text" name="icono" id="modalNuevoIcono" class="form-control form-control-sm font-monospace" value="bi bi-file-earmark-spreadsheet-fill" placeholder="bi bi-file-earmark-spreadsheet-fill">
                                </div>
                                <div class="col-md-12 mb-2">
                                    <label class="form-label font-size-12 font-weight-bold">URL de la Página / Documento *</label>
                                    <input type="url" id="modalNuevaUrl" name="url" class="form-control form-control-sm font-monospace" placeholder="https://docs.google.com/spreadsheets/..." required>
                                </div>
                                <div class="col-md-8 mb-2">
                                    <label class="form-label font-size-12 font-weight-bold">Descripción (Opcional)</label>
                                    <input type="text" name="descripcion" id="modalNuevaDesc" class="form-control form-control-sm" placeholder="Breve referencia del servicio">
                                </div>
                                <div class="col-md-4 mb-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-sm btn-success w-100 font-weight-bold" style="height: 32px;">
                                        <i class="bi bi-plus-lg"></i> Guardar Pestaña
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de Pestañas Existentes -->
                <h6 class="font-weight-bold mt-3 mb-2" style="font-size: 0.85rem;">Pestañas Configuradas</h6>
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-sm table-bordered text-left" style="font-size: 0.78rem; border-color: var(--border-color);">
                        <thead style="background: var(--bg-surface);">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th>Título</th>
                                <th>URL</th>
                                <th style="width: 80px;" class="text-center">Estado</th>
                                <th style="width: 90px;" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enlaces as $e)
                                <tr>
                                    <td class="text-center font-weight-bold">{{ $e->orden }}</td>
                                    <td>
                                        <i class="{{ $e->icono }} mr-1"></i>
                                        <strong>{{ $e->titulo }}</strong>
                                    </td>
                                    <td class="font-monospace text-truncate" style="max-width: 250px;" title="{{ $e->url }}">
                                        {{ $e->url }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $e->is_active ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $e->is_active ? 'Activo' : 'Oculto' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-outline-danger p-1" onclick="eliminarPestana('{{ $e->id }}', '{{ addslashes($e->titulo) }}')" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var claveAutenticada = false;

    function formatearUrlEmbebible(url) {
        if (!url) return url;
        // Si es Google Sheets y tiene /edit, asegurar que lleve /edit?embedded=true&rm=minimal
        if (url.includes('docs.google.com/spreadsheets') && url.includes('/edit') && !url.includes('embedded=true')) {
            if (url.includes('?')) {
                return url + '&embedded=true&rm=minimal';
            } else {
                return url + '?embedded=true&rm=minimal';
            }
        }
        return url;
    }

    function cambiarPestana(id, url, titulo, btnEl) {
        $('.alerta-tab-item').removeClass('active');
        if (btnEl) {
            $(btnEl).addClass('active');
        }

        let embedUrl = formatearUrlEmbebible(url);

        $('#btnPopoutExterno').attr('href', url);

        $('#iframeLoader').css('display', 'flex').css('opacity', '1');
        let iframe = document.getElementById('mainAlertaIframe');
        if (iframe) {
            iframe.src = embedUrl;
        }

        if (id) {
            let newUrl = window.location.pathname + '?tab=' + id;
            window.history.replaceState({ path: newUrl }, '', newUrl);
        }
    }

    function verificarClaveDistica(callback) {
        if (claveAutenticada) {
            callback();
            return;
        }

        Swal.fire({
            title: 'Clave de Seguridad Requerida',
            text: 'Ingrese la clave para gestionar pestañas de Alerta Not:',
            input: 'password',
            inputPlaceholder: 'Ingrese clave de acceso...',
            showCancelButton: true,
            confirmButtonText: 'Autorizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#f59e0b',
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes ingresar una clave';
                }
                if (value !== 'Distica2026') {
                    return 'Clave incorrecta';
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value === 'Distica2026') {
                claveAutenticada = true;
                $('#modalClaveSeguridad').val('Distica2026');
                callback();
            }
        });
    }

    function solicitarCrearPestana() {
        verificarClaveDistica(() => {
            $('#modalNuevaUrl').val('https://');
            $('#modalNuevoTitulo').val('');
            $('#modalGestionarEnlaces').modal('show');
        });
    }

    function solicitarAccesoAdmin() {
        verificarClaveDistica(() => {
            $('#modalGestionarEnlaces').modal('show');
        });
    }

    function guardarNuevaPestanaAjax(e) {
        e.preventDefault();
        let titulo = $('#modalNuevoTitulo').val().trim();
        let url = $('#modalNuevaUrl').val().trim();
        let icono = $('#modalNuevoIcono').val().trim() || 'bi bi-globe';
        let desc = $('#modalNuevaDesc').val().trim();

        if (!titulo || !url) {
            Swal.fire('Error', 'El título y la URL son requeridos.', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('alertas.store') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                clave: 'Distica2026',
                titulo: titulo,
                url: url,
                icono: icono,
                descripcion: desc
            },
            success: function(res) {
                Swal.fire('¡Éxito!', 'Pestaña agregada correctamente.', 'success').then(() => {
                    window.location.reload();
                });
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Error al guardar la pestaña.';
                Swal.fire('Error', msg, 'error');
            }
        });
    }

    function ocultarCargando() {
        $('#iframeLoader').css('opacity', '0');
        setTimeout(() => {
            $('#iframeLoader').css('display', 'none');
        }, 300);
    }

    function recargarIframe() {
        $('#iframeLoader').css('display', 'flex').css('opacity', '1');
        let iframe = document.getElementById('mainAlertaIframe');
        if (iframe) {
            iframe.src = iframe.src;
        }
    }

    function pantallaCompleta() {
        let elem = document.getElementById('iframeContainerBox');
        if (!document.fullscreenElement) {
            elem.requestFullscreen().catch(err => {
                alert(`Error al activar pantalla completa: ${err.message}`);
            });
        } else {
            document.exitFullscreen();
        }
    }

    function eliminarPestana(id, titulo) {
        verificarClaveDistica(() => {
            Swal.fire({
                title: '¿Eliminar Pestaña?',
                text: `¿Seguro que deseas eliminar "${titulo}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('alerta-not') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}",
                            clave: 'Distica2026'
                        },
                        success: function (res) {
                            Swal.fire('Eliminado', res.message, 'success').then(() => {
                                window.location.reload();
                            });
                        },
                        error: function () {
                            Swal.fire('Error', 'No se pudo eliminar la pestaña.', 'error');
                        }
                    });
                }
            });
        });
    }
</script>
@endpush
