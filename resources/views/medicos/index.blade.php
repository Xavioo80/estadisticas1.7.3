@extends('layouts.app')

@section('title', 'Directorio de Médicos - Estadísticas 1.7')

@push('styles')
<style>
    .modal-backdrop-custom {
        position: fixed;
        inset: 0;
        z-index: 1050;
        background-color: rgba(11, 17, 32, 0.78);
        backdrop-filter: blur(5px);
        display: none !important;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .modal-backdrop-custom.active {
        display: flex !important;
    }
    .modal-dialog-custom {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg, 14px);
        width: 100%;
        max-width: 820px;
        max-height: 92vh;
        box-shadow: 0 24px 50px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        animation: modalFadeIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.97) translateY(-6px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .modal-header-custom {
        background: linear-gradient(135deg, var(--color-primary, #3b82f6), #1d4ed8);
        color: #ffffff;
        padding: 0.85rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .modal-form-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 12px;
        padding: 1.2rem 1.35rem;
        overflow-y: auto;
    }
    .modal-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .modal-field label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-secondary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 4px;
        letter-spacing: 0.2px;
    }
    .modal-field input,
    .modal-field select,
    .modal-field textarea {
        background-color: var(--input-bg);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm, 6px);
        padding: 0.42rem 0.65rem;
        font-size: 0.83rem;
        height: 36px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .modal-field textarea {
        height: auto;
    }
    .modal-field input:focus,
    .modal-field select:focus,
    .modal-field textarea:focus {
        border-color: var(--color-primary, #3b82f6);
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
    }
    .grid-col-12 { grid-column: span 12; }
    .grid-col-9  { grid-column: span 9; }
    .grid-col-8  { grid-column: span 8; }
    .grid-col-7  { grid-column: span 7; }
    .grid-col-6  { grid-column: span 6; }
    .grid-col-5  { grid-column: span 5; }
    .grid-col-4  { grid-column: span 4; }
    .grid-col-3  { grid-column: span 3; }
    .grid-col-2  { grid-column: span 2; }

    @media (max-width: 768px) {
        .grid-col-9, .grid-col-8, .grid-col-7, .grid-col-6, .grid-col-5, .grid-col-4, .grid-col-3, .grid-col-2 {
            grid-column: span 12;
        }
    }

    .medico-info-label {
        font-size: 0.70rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 2px;
    }
    .medico-info-val {
        font-size: 0.86rem;
        font-weight: 600;
        color: var(--text-primary);
    }
</style>
@endpush

@section('content')
<div class="informe-page-wrapper">
    <!-- Header y Barra de Herramientas Unificada en 1 Sola Fila -->
    <div class="informe-header" style="display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 12px !important; padding: 0.6rem 1rem !important; min-height: 48px !important; background: var(--bg-surface) !important; border-bottom: 1px solid var(--border-color) !important;">
        <!-- Título y Contador -->
        <div style="display: inline-flex !important; align-items: center !important; gap: 8px !important; flex-shrink: 0 !important; white-space: nowrap !important;">
            <h2 style="margin: 0 !important; font-size: 1.05rem !important; font-weight: 800 !important; display: inline-flex !important; align-items: center !important; gap: 8px !important; white-space: nowrap !important; color: var(--text-primary) !important;">
                <i class="bi bi-person-badge-fill text-primary" style="font-size: 1.25rem;"></i> Directorio del Personal Médico
                <span id="medicoCountBadge" class="badge badge-subtle-primary" style="font-size: 0.78rem; font-weight: 700; padding: 4px 8px; border-radius: 6px; margin-left: 6px;">
                    <span id="totalMedicos">...</span> Médicos
                </span>
            </h2>
        </div>

        <!-- Filtros y Acciones en la Misma Fila -->
        <div style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 10px !important; flex-shrink: 0 !important;">
            <!-- Buscar médico -->
            <div style="position: relative; width: 280px; display: inline-block;">
                <i class="bi bi-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.8rem; pointer-events: none; z-index: 2;"></i>
                <input type="search" id="buscadorMedico" class="form-control form-control-sm"
                    style="padding-left: 2rem !important; height: 32px !important; font-size: 0.82rem !important; background-color: var(--input-bg) !important; color: var(--text-primary) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-sm) !important;"
                    placeholder="Buscar por nombre, código...">
            </div>

            <!-- Filtro de estado -->
            <div style="width: 175px; display: inline-block;">
                <select id="filtroEstado" class="form-control form-control-sm font-weight-semibold"
                    style="height: 32px !important; font-size: 0.82rem !important; background-color: var(--input-bg) !important; color: var(--text-primary) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-sm) !important;"
                    onchange="applyFilters()">
                    <option value="">TODOS LOS ESTADOS</option>
                    <option value="activo">Solo Activos</option>
                    <option value="inactivo">Solo Inactivos</option>
                </select>
            </div>

            <!-- Botón Nuevo Médico (Abre Modal) -->
            <button type="button" onclick="openCreateModal()" class="btn btn-primary btn-sm"
                style="height: 32px !important; display: inline-flex !important; align-items: center !important; gap: 6px !important; font-weight: 700 !important; font-size: 0.82rem !important; white-space: nowrap !important; border-radius: var(--radius-sm) !important; padding: 0 14px !important;">
                <i class="bi bi-person-plus-fill"></i> Nuevo Médico
            </button>
        </div>
    </div>

    <!-- Contenedor Principal de la Tabla -->
    <div class="table-responsive flex-grow-1" style="border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-surface); overflow-y: auto; max-height: calc(100vh - 170px);">
        <table id="medicos-table" class="table table-hover table-sing mb-0" style="font-size: 0.83rem; border-collapse: separate; border-spacing: 0;">
            <thead style="position: sticky; top: 0; z-index: 10; background-color: var(--bg-subtle); border-bottom: 2px solid var(--border-color);">
                <tr>
                    <th class="py-2 px-3 text-center" style="width: 50px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">N°</th>
                    <th class="py-2 px-3 text-left" style="min-width: 240px; text-align: left !important; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Médico</th>
                    <th class="py-2 px-3 text-left" style="width: 130px; text-align: left !important; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Jornada</th>
                    <th class="py-2 px-3 text-left" style="width: 160px; text-align: left !important; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Especialidad</th>
                    <th class="py-2 px-3 text-left" style="width: 130px; text-align: left !important; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Modalidad</th>
                    <th class="py-2 px-3 text-left" style="min-width: 220px; text-align: left !important; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Observaciones</th>
                    <th class="py-2 px-3 text-center" style="width: 95px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Estado</th>
                    <th class="py-2 px-3 text-center" style="width: 115px; color: var(--text-muted); font-weight: 700;">Acciones</th>
                </tr>
            </thead>
            <tbody id="medicosTableBody" style="color: var(--text-primary);">
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div>
                        Cargando personal médico...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pie de tabla / Paginación -->
    <div class="mt-2.5 d-flex flex-column flex-md-row align-items-center justify-content-between p-2 rounded" style="background-color: var(--bg-surface); border: 1px solid var(--border-color); font-size: 0.82rem;">
        <span id="tableInfo" style="color: var(--text-secondary); font-weight: 500;">Cargando...</span>
        <div class="d-flex align-items-center gap-2">
            <button id="prevPage" onclick="changePage(-1)" class="btn btn-subtle btn-sm px-2 py-1" style="font-weight: 600;">
                <i class="bi bi-chevron-left"></i> Anterior
            </button>
            <span id="pageIndicator" class="badge badge-subtle-primary font-weight-bold px-2 py-1">1</span>
            <button id="nextPage" onclick="changePage(1)" class="btn btn-subtle btn-sm px-2 py-1" style="font-weight: 600;">
                Siguiente <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL: CREAR NUEVO MÉDICO
     ========================================== -->
<div id="createMedicoModal" class="modal-backdrop-custom">
    <div class="modal-dialog-custom">
        <!-- Header -->
        <div class="modal-header-custom">
            <div class="d-flex align-items-center gap-2.5">
                <div class="d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 9px; background: rgba(255,255,255,0.2); color: #fff;">
                    <i class="bi bi-person-plus-fill" style="font-size: 1.2rem;"></i>
                </div>
                <div>
                    <h5 class="mb-0 font-weight-bold" style="font-size: 0.96rem; color: #fff;">Registrar Nuevo Personal Médico</h5>
                    <small style="opacity: 0.9; font-size: 0.75rem; color: rgba(255,255,255,0.9);">Alta de facultativo y asignación de parámetros</small>
                </div>
            </div>
            <button type="button" onclick="closeCreateModal()" class="btn btn-icon btn-sm" style="color: #fff; background: rgba(255,255,255,0.18); width: 30px; height: 30px; border-radius: 8px; border: none; display: flex; align-items: center; justify-content: center;" title="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Form Body con CSS Grid Optimizado -->
        <form id="createMedicoForm" onsubmit="saveCreateMedico(event)">
            @csrf
            <div class="modal-form-grid">
                <!-- Fila 1: Código (Compacto) + Nombre Completo -->
                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-hash text-primary"></i> Código <span class="text-danger">*</span></label>
                    <input type="text" id="create_COD_MED" name="COD_MED" required
                        class="font-monospace font-weight-bold text-center"
                        style="background-color: var(--input-bg); color: var(--color-primary); font-size: 0.95rem;">
                </div>

                <div class="modal-field grid-col-9">
                    <label><i class="bi bi-person-fill text-primary"></i> Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" id="create_NOM_MED" name="NOM_MED" required oninput="checkMssAutoDetectCreate(this.value)"
                        placeholder="Ej: DRA. MARIA LOPEZ"
                        class="text-uppercase font-weight-semibold">
                </div>

                <!-- Fila 2: Jornada + Especialidad + Nómina + Modalidad (4 Columnas) -->
                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-clock-fill text-primary"></i> Jornada <span class="text-danger">*</span></label>
                    <select id="create_JORNADA" name="JORNADA" required class="text-uppercase font-weight-semibold">
                        <option value="MATUTINA">MATUTINA</option>
                        <option value="VESPERTINA">VESPERTINA</option>
                        <option value="FIN DE SEMANA">FIN DE SEMANA</option>
                        <option value="NOCTURNA">NOCTURNA</option>
                        <option value="JORNADA COMPLETA">JORNADA COMPLETA</option>
                    </select>
                </div>

                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-award-fill text-primary"></i> Especialidad</label>
                    <input type="text" id="create_ESPECIALIDAD" name="ESPECIALIDAD" value="MEDICO GENERAL" class="text-uppercase">
                </div>

                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-briefcase-fill text-primary"></i> Nómina</label>
                    <select id="create_NOMINA" name="NOMINA" class="text-uppercase">
                        <option value="MEDICO ASISTENCIAL">MEDICO ASISTENCIAL</option>
                        <option value="ESPECIALISTA">ESPECIALISTA</option>
                        <option value="LICENCIADA EN ENFERMERIA">LICENCIADA EN ENFERMERIA</option>
                        <option value="ENFERMERA AUXILIAR">ENFERMERA AUXILIAR</option>
                        <option value="TRABAJADOR SOCIAL">TRABAJADOR SOCIAL</option>
                        <option value="ABOGADO">ABOGADO</option>
                        <option value="ONG">ONG</option>
                        <option value="OTROS">OTROS</option>
                    </select>
                </div>

                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-file-earmark-text-fill text-primary"></i> Modalidad</label>
                    <select id="create_MODALIDAD" name="MODALIDAD" class="text-uppercase">
                        <option value="PERMANENTE">PERMANENTE</option>
                        <option value="CONTRATO">CONTRATO</option>
                        <option value="SERVICIO SOCIAL">SERVICIO SOCIAL</option>
                        <option value="INTERINATO">INTERINATO</option>
                    </select>
                </div>

                <!-- Fila 3: Estado + Fecha Ingreso + Horas + Teléfono (4 Columnas) -->
                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-toggle-on text-primary"></i> Estado</label>
                    <select id="create_estado" name="estado" class="text-uppercase font-weight-bold">
                        <option value="activo">ACTIVO</option>
                        <option value="inactivo">INACTIVO</option>
                    </select>
                </div>

                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-calendar-event text-primary"></i> Fecha Ingreso</label>
                    <input type="date" id="create_FECHA_INGRESO" name="FECHA_INGRESO" value="{{ date('Y-m-d') }}">
                </div>

                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-hourglass-split text-primary"></i> Horas Contratadas</label>
                    <input type="number" id="create_HORAS_CONTRATADAS" name="HORAS_CONTRATADAS" value="6" min="1" max="24">
                </div>

                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-telephone-fill text-primary"></i> Teléfono</label>
                    <input type="text" id="create_TELEFONO" name="TELEFONO" placeholder="Ej: 9999-9999">
                </div>

                <!-- Fila 4: Correo + Observaciones -->
                <div class="modal-field grid-col-5">
                    <label><i class="bi bi-envelope-fill text-primary"></i> Correo Electrónico</label>
                    <input type="email" id="create_CORREO" name="CORREO" placeholder="doctor@salud.gob.hn">
                </div>

                <div class="modal-field grid-col-7">
                    <label><i class="bi bi-card-text text-primary"></i> Observaciones</label>
                    <input type="text" id="create_observaciones" name="observaciones" placeholder="Detalles o notas del médico...">
                </div>

                <!-- Fila 5: Checkbox de Director -->
                <div class="grid-col-12">
                    <label class="d-flex align-items-center gap-2.5 p-2.5 rounded m-0" style="background: var(--bg-subtle); border: 1px solid var(--border-color); cursor: pointer;">
                        <input type="checkbox" id="create_es_director" name="es_director" value="1" class="form-check-input mt-0" style="width: 18px; height: 18px;">
                        <span style="font-size: 0.83rem; font-weight: 700; color: var(--text-primary);">
                            <i class="bi bi-star-fill text-warning mr-1"></i> Asignar como Director / Firma Principal del Mes
                        </span>
                    </label>
                </div>
            </div>

            <!-- Footer -->
            <div class="d-flex align-items-center justify-content-end gap-2 px-3.5 py-2.5 border-top" style="background-color: var(--bg-subtle); border-color: var(--border-color) !important;">
                <button type="button" onclick="closeCreateModal()" class="btn btn-secondary btn-sm" style="font-weight: 600;">
                    Cancelar
                </button>
                <button type="submit" id="btnSaveCreateMedico" class="btn btn-primary btn-sm" style="font-weight: 600; padding: 0 16px;">
                    <i class="bi bi-check-circle-fill mr-1"></i> Guardar Médico
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     MODAL: EDITAR MÉDICO
     ========================================== -->
<div id="editMedicoModal" class="modal-backdrop-custom">
    <div class="modal-dialog-custom">
        <!-- Header -->
        <div class="modal-header-custom" style="background: linear-gradient(135deg, #0284c7, #0369a1);">
            <div class="d-flex align-items-center gap-2.5">
                <div class="d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 9px; background: rgba(255,255,255,0.2); color: #fff;">
                    <i class="bi bi-pencil-square" style="font-size: 1.2rem;"></i>
                </div>
                <div>
                    <h5 class="mb-0 font-weight-bold" style="font-size: 0.96rem; color: #fff;">Editar Información del Médico</h5>
                    <small id="editModalSubhead" style="opacity: 0.9; font-size: 0.75rem; color: rgba(255,255,255,0.9);">Modificar datos del profesional</small>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="btn btn-icon btn-sm" style="color: #fff; background: rgba(255,255,255,0.18); width: 30px; height: 30px; border-radius: 8px; border: none; display: flex; align-items: center; justify-content: center;" title="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Form Body con CSS Grid Optimizado -->
        <form id="editMedicoForm" onsubmit="saveEditMedico(event)">
            @csrf
            <input type="hidden" id="edit_medico_id" name="id">

            <div class="modal-form-grid">
                <!-- Fila 1: Código + Nombre Completo -->
                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-hash text-primary"></i> Código <span class="text-danger">*</span></label>
                    <input type="text" id="edit_COD_MED" name="COD_MED" required
                        class="font-monospace font-weight-bold text-center"
                        style="background-color: var(--input-bg); color: var(--color-primary); font-size: 0.95rem;">
                </div>

                <div class="modal-field grid-col-9">
                    <label><i class="bi bi-person-fill text-primary"></i> Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" id="edit_NOM_MED" name="NOM_MED" required oninput="checkMssAutoDetect(this.value)"
                        class="text-uppercase font-weight-semibold">
                </div>

                <!-- Fila 2: Jornada + Especialidad + Nómina + Modalidad (4 Columnas) -->
                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-clock-fill text-primary"></i> Jornada <span class="text-danger">*</span></label>
                    <select id="edit_JORNADA" name="JORNADA" class="text-uppercase font-weight-semibold">
                        <option value="MATUTINA">MATUTINA</option>
                        <option value="VESPERTINA">VESPERTINA</option>
                        <option value="FIN DE SEMANA">FIN DE SEMANA</option>
                        <option value="NOCTURNA">NOCTURNA</option>
                        <option value="JORNADA COMPLETA">JORNADA COMPLETA</option>
                    </select>
                </div>

                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-award-fill text-primary"></i> Especialidad</label>
                    <input type="text" id="edit_ESPECIALIDAD" name="ESPECIALIDAD" class="text-uppercase">
                </div>

                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-briefcase-fill text-primary"></i> Nómina</label>
                    <select id="edit_NOMINA" name="NOMINA" class="text-uppercase">
                        <option value="MEDICO ASISTENCIAL">MEDICO ASISTENCIAL</option>
                        <option value="ESPECIALISTA">ESPECIALISTA</option>
                        <option value="LICENCIADA EN ENFERMERIA">LICENCIADA EN ENFERMERIA</option>
                        <option value="ENFERMERA AUXILIAR">ENFERMERA AUXILIAR</option>
                        <option value="TRABAJADOR SOCIAL">TRABAJADOR SOCIAL</option>
                        <option value="ABOGADO">ABOGADO</option>
                        <option value="ONG">ONG</option>
                        <option value="OTROS">OTROS</option>
                    </select>
                </div>

                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-file-earmark-text-fill text-primary"></i> Modalidad</label>
                    <select id="edit_MODALIDAD" name="MODALIDAD" class="text-uppercase">
                        <option value="PERMANENTE">PERMANENTE</option>
                        <option value="CONTRATO">CONTRATO</option>
                        <option value="SERVICIO SOCIAL">SERVICIO SOCIAL</option>
                        <option value="INTERINATO">INTERINATO</option>
                    </select>
                </div>

                <!-- Fila 3: Estado + Fecha Ingreso + Horas + Teléfono -->
                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-toggle-on text-primary"></i> Estado</label>
                    <select id="edit_estado" name="estado" class="text-uppercase font-weight-bold">
                        <option value="activo">ACTIVO</option>
                        <option value="inactivo">INACTIVO</option>
                    </select>
                </div>

                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-calendar-event text-primary"></i> Fecha Ingreso</label>
                    <input type="date" id="edit_FECHA_INGRESO" name="FECHA_INGRESO">
                </div>

                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-hourglass-split text-primary"></i> Horas Contratadas</label>
                    <input type="number" id="edit_HORAS_CONTRATADAS" name="HORAS_CONTRATADAS" min="1" max="24">
                </div>

                <div class="modal-field grid-col-3">
                    <label><i class="bi bi-telephone-fill text-primary"></i> Teléfono</label>
                    <input type="text" id="edit_TELEFONO" name="TELEFONO" placeholder="Ej: 9999-9999">
                </div>

                <!-- Fila 4: Correo + Observaciones -->
                <div class="modal-field grid-col-5">
                    <label><i class="bi bi-envelope-fill text-primary"></i> Correo Electrónico</label>
                    <input type="email" id="edit_CORREO" name="CORREO" placeholder="doctor@salud.gob.hn">
                </div>

                <div class="modal-field grid-col-7">
                    <label><i class="bi bi-card-text text-primary"></i> Observaciones</label>
                    <input type="text" id="edit_observaciones" name="observaciones" placeholder="Detalles o notas del médico...">
                </div>

                <!-- Fila 5: Checkbox de Director -->
                <div class="grid-col-12">
                    <label class="d-flex align-items-center gap-2.5 p-2.5 rounded m-0" style="background: var(--bg-subtle); border: 1px solid var(--border-color); cursor: pointer;">
                        <input type="checkbox" id="edit_es_director" name="es_director" value="1" class="form-check-input mt-0" style="width: 18px; height: 18px;">
                        <span style="font-size: 0.83rem; font-weight: 700; color: var(--text-primary);">
                            <i class="bi bi-star-fill text-warning mr-1"></i> Asignar como Director / Firma Principal del Mes
                        </span>
                    </label>
                </div>
            </div>

            <!-- Footer -->
            <div class="d-flex align-items-center justify-content-end gap-2 px-3.5 py-2.5 border-top" style="background-color: var(--bg-subtle); border-color: var(--border-color) !important;">
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary btn-sm" style="font-weight: 600;">
                    Cancelar
                </button>
                <button type="submit" id="btnSaveEditMedico" class="btn btn-primary btn-sm" style="font-weight: 600; padding: 0 16px;">
                    <i class="bi bi-check-circle-fill mr-1"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     MODAL: VER DETALLES DEL MÉDICO (SHOW)
     ========================================== -->
<div id="showMedicoModal" class="modal-backdrop-custom">
    <div class="modal-dialog-custom" style="max-width: 680px;">
        <!-- Header -->
        <div class="modal-header-custom" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
            <div class="d-flex align-items-center gap-2.5">
                <div class="d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 9px; background: rgba(255,255,255,0.2); color: #fff;">
                    <i class="bi bi-person-vcard-fill" style="font-size: 1.25rem;"></i>
                </div>
                <div>
                    <h5 class="mb-0 font-weight-bold" style="font-size: 0.96rem; color: #fff;" id="show_modal_title">Perfil del Personal Médico</h5>
                    <small style="opacity: 0.9; font-size: 0.75rem; color: rgba(255,255,255,0.9);">Ficha institucional completa</small>
                </div>
            </div>
            <button type="button" onclick="closeShowModal()" class="btn btn-icon btn-sm" style="color: #fff; background: rgba(255,255,255,0.18); width: 30px; height: 30px; border-radius: 8px; border: none; display: flex; align-items: center; justify-content: center;" title="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-3.5 overflow-auto" style="max-height: calc(85vh - 120px);">
            <!-- Tarjeta Principal del Médico -->
            <div class="d-flex align-items-center gap-3 p-3 mb-3 rounded" style="background-color: var(--bg-subtle); border: 1px solid var(--border-color);">
                <div class="d-inline-flex align-items-center justify-content-center shrink-0" style="width: 48px; height: 48px; border-radius: var(--radius-md); background: rgba(77, 124, 254, 0.15); color: var(--color-primary);">
                    <i class="bi bi-person-fill" style="font-size: 1.65rem;"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                        <h4 class="mb-0 font-weight-bold text-uppercase" id="show_NOM_MED" style="font-size: 1.05rem; color: var(--text-primary);"></h4>
                        <span id="show_estado_badge"></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="badge badge-subtle-primary font-monospace font-weight-bold" id="show_COD_MED"></span>
                        <span id="show_director_badge"></span>
                    </div>
                </div>
            </div>

            <!-- Grid de Información en 4 Columnas -->
            <div class="row">
                <div class="col-sm-3 col-6 mb-2.5">
                    <div class="p-2 rounded" style="background-color: var(--bg-subtle); border: 1px solid var(--border-color);">
                        <div class="medico-info-label"><i class="bi bi-clock-fill text-primary mr-0.5"></i> Jornada</div>
                        <div class="medico-info-val text-uppercase" id="show_JORNADA">—</div>
                    </div>
                </div>
                <div class="col-sm-3 col-6 mb-2.5">
                    <div class="p-2 rounded" style="background-color: var(--bg-subtle); border: 1px solid var(--border-color);">
                        <div class="medico-info-label"><i class="bi bi-award-fill text-primary mr-0.5"></i> Especialidad</div>
                        <div class="medico-info-val text-uppercase" id="show_ESPECIALIDAD">—</div>
                    </div>
                </div>
                <div class="col-sm-3 col-6 mb-2.5">
                    <div class="p-2 rounded" style="background-color: var(--bg-subtle); border: 1px solid var(--border-color);">
                        <div class="medico-info-label"><i class="bi bi-briefcase-fill text-primary mr-0.5"></i> Nómina</div>
                        <div class="medico-info-val text-uppercase" id="show_NOMINA">—</div>
                    </div>
                </div>
                <div class="col-sm-3 col-6 mb-2.5">
                    <div class="p-2 rounded" style="background-color: var(--bg-subtle); border: 1px solid var(--border-color);">
                        <div class="medico-info-label"><i class="bi bi-file-earmark-text-fill text-primary mr-0.5"></i> Modalidad</div>
                        <div class="medico-info-val text-uppercase" id="show_MODALIDAD">—</div>
                    </div>
                </div>

                <div class="col-sm-3 col-6 mb-2.5">
                    <div class="p-2 rounded" style="background-color: var(--bg-subtle); border: 1px solid var(--border-color);">
                        <div class="medico-info-label"><i class="bi bi-calendar-event text-primary mr-0.5"></i> Fecha Ingreso</div>
                        <div class="medico-info-val" id="show_FECHA_INGRESO">—</div>
                    </div>
                </div>
                <div class="col-sm-3 col-6 mb-2.5">
                    <div class="p-2 rounded" style="background-color: var(--bg-subtle); border: 1px solid var(--border-color);">
                        <div class="medico-info-label"><i class="bi bi-hourglass-split text-primary mr-0.5"></i> Horas/Día</div>
                        <div class="medico-info-val" id="show_HORAS_CONTRATADAS">—</div>
                    </div>
                </div>
                <div class="col-sm-3 col-6 mb-2.5">
                    <div class="p-2 rounded" style="background-color: var(--bg-subtle); border: 1px solid var(--border-color);">
                        <div class="medico-info-label"><i class="bi bi-telephone-fill text-primary mr-0.5"></i> Teléfono</div>
                        <div class="medico-info-val" id="show_TELEFONO">—</div>
                    </div>
                </div>
                <div class="col-sm-3 col-6 mb-2.5">
                    <div class="p-2 rounded" style="background-color: var(--bg-subtle); border: 1px solid var(--border-color);">
                        <div class="medico-info-label"><i class="bi bi-envelope-fill text-primary mr-0.5"></i> Correo</div>
                        <div class="medico-info-val text-truncate" id="show_CORREO" style="font-size: 0.8rem;" title="">—</div>
                    </div>
                </div>

                <div class="col-12 mt-1">
                    <div class="p-2.5 rounded" style="background-color: var(--bg-subtle); border: 1px solid var(--border-color);">
                        <div class="medico-info-label"><i class="bi bi-card-text text-primary mr-0.5"></i> Observaciones</div>
                        <div class="medico-info-val text-muted" id="show_observaciones" style="font-size: 0.82rem; font-weight: 500; white-space: pre-wrap;">—</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="d-flex align-items-center justify-content-between px-3.5 py-2.5 border-top" style="background-color: var(--bg-subtle); border-color: var(--border-color) !important;">
            <button type="button" onclick="closeShowModal()" class="btn btn-secondary btn-sm" style="font-weight: 600;">
                Cerrar
            </button>
            <button type="button" id="btnShowToEdit" onclick="" class="btn btn-primary btn-sm" style="font-weight: 600; padding: 0 16px;">
                <i class="bi bi-pencil-square mr-1"></i> Editar Médico
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let allMedicos = [];
let filtered = [];
let page = 1;
const perPage = 50;

function loadMedicos() {
    $.ajax({
        url: '{{ route('medicos.index') }}',
        type: 'GET',
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(json) {
            allMedicos = json.data || [];
            applyFilters();
        },
        error: function(xhr, status, error) {
            $('#medicosTableBody').html(`<tr><td colspan="8" class="text-center py-5 text-danger">Error al cargar datos: ${error || 'Error de conexión'}</td></tr>`);
        }
    });
}

function applyFilters() {
    const q = ($('#buscadorMedico').val() || '').toLowerCase();
    const est = $('#filtroEstado').val();

    filtered = allMedicos.filter(m => {
        const matchQ = !q || (m.NOM_MED||'').toLowerCase().includes(q) || (m.COD_MED||'').toLowerCase().includes(q) || (m.ESPECIALIDAD||'').toLowerCase().includes(q) || (m.observaciones||'').toLowerCase().includes(q);
        const matchEst = !est || m.estado === est;
        return matchQ && matchEst;
    });

    page = 1;
    render();
}

function render() {
    const tbody = document.getElementById('medicosTableBody');
    if (!tbody) return;

    const start = (page - 1) * perPage;
    const pageData = filtered.slice(start, start + perPage);

    if (pageData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted">No se encontraron médicos</td></tr>`;
    } else {
        tbody.innerHTML = pageData.map((m, i) => {
            const rowNum = start + i + 1;
            const obs = m.observaciones || '—';
            const estadoBadge = m.estado === 'activo'
                ? '<span class="badge badge-subtle-success font-weight-bold" style="font-size: 0.72rem;">ACTIVO</span>'
                : '<span class="badge badge-subtle-danger font-weight-bold" style="font-size: 0.72rem;">INACTIVO</span>';

            const directorBadge = m.es_director
                ? '<span class="badge badge-subtle-warning ml-1 font-weight-bold" style="font-size: 0.70rem;"><i class="bi bi-star-fill mr-1"></i>DIRECTOR</span>'
                : '';

            const safeNom = (m.NOM_MED || '').replace(/'/g, "\\'");

            return `<tr style="border-bottom: 1px solid var(--border-color); min-height: 38px;">
                <td class="py-1 px-3 text-center font-monospace" style="color: var(--text-muted); border-right: 1px solid var(--border-color); vertical-align: middle;">${rowNum}</td>
                <td class="py-1 px-3 text-left font-weight-semibold" style="border-right: 1px solid var(--border-color); vertical-align: middle; white-space: nowrap; text-align: left !important;">
                    <div class="d-inline-flex align-items-center gap-1.5 justify-content-start" style="text-align: left !important;">
                        <span class="badge badge-subtle-primary font-monospace font-weight-bold px-1.5 py-0.5" style="font-size: 0.72rem;">#${m.COD_MED || '—'}</span>
                        <span style="color: var(--text-primary); font-weight: 600;">${m.NOM_MED || ''}</span>
                        ${directorBadge}
                    </div>
                </td>
                <td class="py-1 px-3 text-left text-uppercase font-weight-medium" style="color: var(--text-secondary); border-right: 1px solid var(--border-color); vertical-align: middle; white-space: nowrap; text-align: left !important;">${m.JORNADA || '—'}</td>
                <td class="py-1 px-3 text-left text-uppercase font-weight-medium" style="color: var(--text-secondary); border-right: 1px solid var(--border-color); vertical-align: middle; white-space: nowrap; text-align: left !important;">${m.ESPECIALIDAD || '—'}</td>
                <td class="py-1 px-3 text-left text-uppercase font-weight-medium" style="color: var(--text-secondary); border-right: 1px solid var(--border-color); vertical-align: middle; white-space: nowrap; text-align: left !important;">${m.MODALIDAD || '—'}</td>
                <td class="py-1.5 px-3 text-left" style="min-width: 200px; max-width: 320px; color: var(--text-secondary); font-size: 0.72rem !important; line-height: 1.3 !important; border-right: 1px solid var(--border-color); vertical-align: middle; white-space: normal !important; word-break: break-word !important; overflow-wrap: anywhere !important; text-align: left !important;" title="${obs}">${obs}</td>
                <td class="py-1 px-3 text-center" style="border-right: 1px solid var(--border-color); vertical-align: middle; white-space: nowrap;">${estadoBadge}</td>
                <td class="py-1 px-2 text-center" style="vertical-align: middle; white-space: nowrap;">
                    <div class="d-inline-flex align-items-center justify-content-center gap-1">
                        <button onclick="openShowModal('${m.COD_MED}')" class="btn btn-icon btn-sm btn-subtle-primary" title="Ver Detalles" style="width: 28px; height: 28px; border-radius: var(--radius-sm);">
                            <i class="bi bi-eye-fill" style="font-size: 0.85rem;"></i>
                        </button>
                        <button onclick="openEditModalByCod('${m.COD_MED}')" class="btn btn-icon btn-sm btn-subtle-warning" title="Editar" style="width: 28px; height: 28px; border-radius: var(--radius-sm);">
                            <i class="bi bi-pencil-square" style="font-size: 0.85rem;"></i>
                        </button>
                        <button onclick="confirmDelete('${m.COD_MED}', '${safeNom}')" class="btn btn-icon btn-sm btn-subtle-danger" title="Eliminar" style="width: 28px; height: 28px; border-radius: var(--radius-sm);">
                            <i class="bi bi-trash3-fill" style="font-size: 0.85rem;"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    const totalEl = document.getElementById('totalMedicos');
    if (totalEl) totalEl.textContent = filtered.length;

    const totalPages = Math.ceil(filtered.length / perPage) || 1;
    const infoEl = document.getElementById('tableInfo');
    if (infoEl) infoEl.textContent = `Mostrando ${Math.min(start + 1, filtered.length)}–${Math.min(start + perPage, filtered.length)} de ${filtered.length} médicos`;

    const pageEl = document.getElementById('pageIndicator');
    if (pageEl) pageEl.textContent = `${page} / ${totalPages}`;
}

function changePage(delta) {
    const totalPages = Math.ceil(filtered.length / perPage) || 1;
    const newPage = page + delta;
    if (newPage >= 1 && newPage <= totalPages) {
        page = newPage;
        render();
    }
}

// ──────────────────────────────────────────
// MODAL: CREATE MÉDICO
// ──────────────────────────────────────────
function openCreateModal() {
    $('#createMedicoForm')[0].reset();
    $('#create_FECHA_INGRESO').val(new Date().toISOString().split('T')[0]);
    $('#create_HORAS_CONTRATADAS').val(6);
    $('#create_estado').val('activo');
    $('#create_ESPECIALIDAD').val('MEDICO GENERAL');
    $('#create_NOMINA').val('MEDICO ASISTENCIAL');
    $('#create_MODALIDAD').val('PERMANENTE');

    // Calcular código sugerido basado en el máximo actual numérico
    let maxCode = 0;
    allMedicos.forEach(m => {
        const num = parseInt(m.COD_MED, 10);
        if (!isNaN(num) && num > maxCode) maxCode = num;
    });
    $('#create_COD_MED').val(maxCode + 1);

    $('#createMedicoModal').addClass('active');
    setTimeout(() => $('#create_NOM_MED').focus(), 100);
}

function closeCreateModal() {
    $('#createMedicoModal').removeClass('active');
}

function checkMssAutoDetectCreate(val) {
    if ((val || '').trim().toUpperCase().startsWith('MSS.')) {
        $('#create_MODALIDAD').val('SERVICIO SOCIAL');
        $('#create_ESPECIALIDAD').val('SERVICIO SOCIAL');
        $('#create_NOMINA').val('SERVICIO SOCIAL');
    }
}

function saveCreateMedico(e) {
    e.preventDefault();
    const formData = $('#createMedicoForm').serialize();
    $('#btnSaveCreateMedico').prop('disabled', true);

    $.ajax({
        url: '{{ route('medicos.store') }}',
        type: 'POST',
        data: formData,
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
            closeCreateModal();
            $('#btnSaveCreateMedico').prop('disabled', false);
            Swal.fire({
                icon: 'success',
                title: '¡Registrado!',
                text: 'Médico agregado correctamente al directorio.',
                timer: 1600,
                showConfirmButton: false
            });
            loadMedicos();
        },
        error: function(xhr) {
            $('#btnSaveCreateMedico').prop('disabled', false);
            Swal.fire({
                icon: 'error',
                title: 'Error al registrar',
                text: xhr.responseJSON?.message || 'Error al guardar el médico. Verifique los datos.'
            });
        }
    });
}

// ──────────────────────────────────────────
// MODAL: EDIT MÉDICO
// ──────────────────────────────────────────
function checkMssAutoDetect(val) {
    if ((val || '').trim().toUpperCase().startsWith('MSS.')) {
        $('#edit_MODALIDAD').val('SERVICIO SOCIAL');
        $('#edit_ESPECIALIDAD').val('SERVICIO SOCIAL');
        $('#edit_NOMINA').val('SERVICIO SOCIAL');
    }
}

function openEditModalByCod(cod) {
    const m = allMedicos.find(item => String(item.COD_MED) === String(cod) || item.id == cod);
    if (!m) return;

    $('#edit_medico_id').val(m.id);
    $('#edit_COD_MED').val(m.COD_MED);
    $('#edit_NOM_MED').val(m.NOM_MED);
    $('#edit_JORNADA').val(m.JORNADA || 'MATUTINA');
    $('#edit_ESPECIALIDAD').val(m.ESPECIALIDAD || 'MEDICO GENERAL');
    $('#edit_NOMINA').val(m.NOMINA || 'MEDICO ASISTENCIAL');
    $('#edit_MODALIDAD').val(m.MODALIDAD || 'PERMANENTE');
    $('#edit_estado').val(m.estado || 'activo');
    $('#edit_observaciones').val(m.observaciones || '');
    $('#edit_es_director').prop('checked', m.es_director == 1 || m.es_director === true);
    $('#edit_FECHA_INGRESO').val(m.FECHA_INGRESO || new Date().toISOString().split('T')[0]);
    $('#edit_HORAS_CONTRATADAS').val(m.HORAS_CONTRATADAS || 6);
    $('#edit_TELEFONO').val(m.TELEFONO || '');
    $('#edit_CORREO').val(m.CORREO || '');

    $('#editModalSubhead').text(m.NOM_MED);
    closeShowModal();
    $('#editMedicoModal').addClass('active');
}

function closeEditModal() {
    $('#editMedicoModal').removeClass('active');
}

function saveEditMedico(e) {
    e.preventDefault();
    const id = $('#edit_medico_id').val();
    const formData = $('#editMedicoForm').serialize();

    $('#btnSaveEditMedico').prop('disabled', true);

    $.ajax({
        url: '/medicos/' + id,
        type: 'POST',
        data: formData + '&_method=PUT',
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
            closeEditModal();
            $('#btnSaveEditMedico').prop('disabled', false);
            Swal.fire({
                icon: 'success',
                title: '¡Guardado!',
                text: 'Información médica actualizada con éxito.',
                timer: 1500,
                showConfirmButton: false
            });
            loadMedicos();
        },
        error: function(xhr) {
            $('#btnSaveEditMedico').prop('disabled', false);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: xhr.responseJSON?.message || 'Error al guardar los cambios'
            });
        }
    });
}

// ──────────────────────────────────────────
// MODAL: SHOW / DETALLES DEL MÉDICO
// ──────────────────────────────────────────
function openShowModal(cod) {
    const m = allMedicos.find(item => String(item.COD_MED) === String(cod) || item.id == cod);
    if (!m) return;

    $('#show_NOM_MED').text(m.NOM_MED || '—');
    $('#show_COD_MED').text('CÓD: ' + (m.COD_MED || '—'));
    $('#show_JORNADA').text(m.JORNADA || '—');
    $('#show_ESPECIALIDAD').text(m.ESPECIALIDAD || '—');
    $('#show_NOMINA').text(m.NOMINA || '—');
    $('#show_MODALIDAD').text(m.MODALIDAD || '—');
    $('#show_FECHA_INGRESO').text(m.FECHA_INGRESO || '—');
    $('#show_HORAS_CONTRATADAS').text(m.HORAS_CONTRATADAS ? `${m.HORAS_CONTRATADAS} Horas/Día` : '—');
    $('#show_TELEFONO').text(m.TELEFONO || 'No registrado');
    $('#show_CORREO').text(m.CORREO || 'No registrado').attr('title', m.CORREO || '');
    $('#show_observaciones').text(m.observaciones || 'Sin observaciones');

    const estadoHtml = m.estado === 'activo'
        ? '<span class="badge badge-subtle-success font-weight-bold px-2 py-1">ACTIVO</span>'
        : '<span class="badge badge-subtle-danger font-weight-bold px-2 py-1">INACTIVO</span>';
    $('#show_estado_badge').html(estadoHtml);

    const dirHtml = m.es_director
        ? '<span class="badge badge-subtle-warning font-weight-bold px-2 py-1"><i class="bi bi-star-fill mr-1"></i>DIRECTOR DEL MES</span>'
        : '';
    $('#show_director_badge').html(dirHtml);

    $('#btnShowToEdit').attr('onclick', `openEditModalByCod('${m.COD_MED}')`);
    $('#showMedicoModal').addClass('active');
}

function closeShowModal() {
    $('#showMedicoModal').removeClass('active');
}

// ──────────────────────────────────────────
// ELIMINAR MÉDICO
// ──────────────────────────────────────────
function confirmDelete(cod, nom) {
    Swal.fire({
        title: '¿Eliminar Médico?',
        text: `¿Estás seguro de eliminar a ${nom} (${cod})?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const m = allMedicos.find(item => String(item.COD_MED) === String(cod) || item.id == cod);
            if (!m) return;

            $.ajax({
                url: '/medicos/' + m.id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                dataType: 'json',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: 'Médico eliminado del directorio.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    loadMedicos();
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar el médico.'
                    });
                }
            });
        }
    });
}

$(document).ready(function() {
    loadMedicos();

    let searchTimer;
    $('#buscadorMedico').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilters, 250);
    });

    // Cerrar modales con tecla Escape
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
            closeShowModal();
        }
    });
});
</script>
@endpush
