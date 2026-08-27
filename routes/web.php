<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RegistroGlobalController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\IngresoController;

/*
|--------------------------------------------------------------------------
| Web Routes - Estadísticas 1.7
|--------------------------------------------------------------------------
*/

// Dashboard Routes
Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/visits', [DashboardController::class, 'visits'])->name('visits');
Route::get('/informes/dashboard-epi', [DashboardController::class, 'visits'])->name('dashboard-epi');
Route::get('/charts', [DashboardController::class, 'charts'])->name('charts');
Route::get('/informes/dashboard2', [DashboardController::class, 'charts'])->name('dashboard2');

// Report & Output Routes (Registros AT1 & Informes AT1)
Route::get('/registrosat1', [RegistroGlobalController::class, 'index'])->name('registrosat1');
Route::get('/informesat1', [RegistroGlobalController::class, 'informesAt1'])->name('informesat1');
Route::get('/tables', [RegistroGlobalController::class, 'index'])->name('tables');
Route::get('/registros', [RegistroGlobalController::class, 'index'])->name('registros');
Route::get('/informes', [RegistroGlobalController::class, 'informesAt1'])->name('informes');

// Módulo: Ingresos (Data Entry & Batch Management)
Route::prefix('ingresos')->name('ingresos.')->group(function () {
    Route::get('/', [IngresoController::class, 'index'])->name('index');
    Route::get('/create', [IngresoController::class, 'create'])->name('create');
    Route::post('/', [IngresoController::class, 'store'])->name('store');
    Route::post('/store-massive', [IngresoController::class, 'storeMassive'])->name('storeMassive');
    Route::get('/profesiones', [IngresoController::class, 'profesionesPorFecha'])->name('profesiones-por-fecha');
    Route::get('/medicos', [IngresoController::class, 'medicosPorProfesion'])->name('medicos-por-profesion');
    Route::get('/medicos-fecha', [IngresoController::class, 'medicosPorFecha'])->name('medicos-por-fecha');
    Route::get('/jornadas-medico', [IngresoController::class, 'jornadasPorMedico'])->name('jornadas-por-medico');
    Route::post('/eliminar-grupo', [IngresoController::class, 'eliminarGrupo'])->name('eliminar-grupo');
    Route::get('/detalles-medico/{fecha}/{medico}', [IngresoController::class, 'detallesMedico'])->name('detalles-medico');
    Route::get('/datatable', [IngresoController::class, 'datatable'])->name('datatable');
    Route::put('/{ingreso}', [IngresoController::class, 'update'])->name('update');
    Route::delete('/{ingreso}', [IngresoController::class, 'destroy'])->name('destroy');
    Route::get('/detalles-fecha/{fecha}', [IngresoController::class, 'detallesFecha'])->name('detalles-fecha');
    Route::post('/update-batch', [IngresoController::class, 'batchUpdate'])->name('update-batch');
});
Route::get('/forms', [IngresoController::class, 'index'])->name('forms');

// Admin & UI Routes
use App\Http\Controllers\AdminController;
Route::get('/typography', [AdminController::class, 'typography'])->name('typography');
Route::get('/customization', [AdminController::class, 'typography'])->name('customization');
Route::get('/ui-elements', [AdminController::class, 'uiElements'])->name('ui-elements');
Route::get('/components', [AdminController::class, 'uiElements'])->name('components');

// ── Módulo: Informes (Migrado) ─────────────────────────────────────────────
use App\Http\Controllers\Informes\At1Controller;
use App\Http\Controllers\Informes\AtencionesController;
use App\Http\Controllers\Informes\Tb9Controller;
use App\Http\Controllers\Informes\ImplantesController;
use App\Http\Controllers\Informes\At2Controller;
use App\Http\Controllers\Informes\At2rController;
use App\Http\Controllers\Informes\At2rNController;
use App\Http\Controllers\Informes\At2rRsmController;
use App\Http\Controllers\Informes\MorbilidadController;
use App\Http\Controllers\Informes\ItsController;
use App\Http\Controllers\Informes\Sm107Controller;
use App\Http\Controllers\Informes\Sm2Controller;
use App\Http\Controllers\Informes\Sm307Controller;
use App\Http\Controllers\AlertaSemanalController;
use App\Http\Controllers\Trans2Controller;
use App\Http\Controllers\HoraMedicoController;
use App\Http\Controllers\CalendarioEpiController;
use App\Http\Controllers\NotificacionSvsController;

Route::prefix('informes')->name('informes.')->group(function () {
    Route::get('/', [At1Controller::class, 'index'])->name('index');
    Route::get('/at1', [At1Controller::class, 'informesAt1'])->name('at1');

    Route::get('/atenciones', [AtencionesController::class, 'index'])->name('atenciones');
    Route::get('/atenciones/export', [AtencionesController::class, 'export'])->name('atenciones.export');

    Route::get('/tb9', [Tb9Controller::class, 'index'])->name('tb9');
    Route::get('/tb9/export', [Tb9Controller::class, 'export'])->name('tb9.export');

    Route::get('/implantes', [ImplantesController::class, 'index'])->name('implantes');
    Route::get('/implantes/export', [ImplantesController::class, 'export'])->name('implantes.export');

    Route::get('/at2', [At2Controller::class, 'index'])->name('at2');
    Route::post('/at2/save-manual', [At2Controller::class, 'saveManual'])->name('at2.save-manual');

    Route::get('/at2r', [At2rController::class, 'index'])->name('at2r');
    Route::get('/at2r/export', [At2rController::class, 'export'])->name('at2r.export');

    Route::get('/at2r-n', [At2rNController::class, 'index'])->name('at2r-n');
    Route::get('/at2r-n/audit', [At2rNController::class, 'audit'])->name('at2r-n.audit');
    Route::get('/at2r-n/cell-details', [At2rNController::class, 'cellDetails'])->name('at2r-n.cell-details');
    Route::get('/at2r-n/morbilidad-audit', [At2rNController::class, 'morbilidadAudit'])->name('at2r-n.morbilidad-audit');
    Route::get('/at2r-n/export', [At2rNController::class, 'export'])->name('at2r-n.export');
    Route::post('/at2r-n/save-manual', [At2rNController::class, 'saveManual'])->name('at2r-n.save-manual');

    Route::get('/at2r-rsm', [At2rRsmController::class, 'index'])->name('at2r-rsm');
    Route::get('/at2r-rsm/export', [At2rRsmController::class, 'export'])->name('at2r-rsm.export');

    Route::get('/morbilidad', [MorbilidadController::class, 'index'])->name('morbilidad');
    Route::get('/morbilidad/export', [MorbilidadController::class, 'export'])->name('morbilidad.export');

    Route::get('/its', [ItsController::class, 'index'])->name('its');
    Route::get('/its/details', [ItsController::class, 'details'])->name('its.details');
    Route::get('/its/export', [ItsController::class, 'export'])->name('its.export');

    Route::get('/alerta-semanal', [AlertaSemanalController::class, 'index'])->name('alerta-semanal');
    Route::get('/alerta-semanal/details', [AlertaSemanalController::class, 'getDetails'])->name('alerta-semanal.details');

    Route::get('/trans2', [Trans2Controller::class, 'index'])->name('trans2');
    Route::get('/trans2/details', [Trans2Controller::class, 'getDetails'])->name('trans2.details');

    Route::get('/sm107', [Sm107Controller::class, 'index'])->name('sm107');
    Route::get('/sm107/details', [Sm107Controller::class, 'details'])->name('sm107.details');
    Route::get('/sm107/export', [Sm107Controller::class, 'export'])->name('sm107.export');

    Route::get('/sm2', [Sm2Controller::class, 'index'])->name('sm2');
    Route::get('/sm2/export', [Sm2Controller::class, 'export'])->name('sm2.export');

    Route::get('/sm307', [Sm307Controller::class, 'index'])->name('sm307');
    Route::get('/sm307/cell-details', [Sm307Controller::class, 'cellDetails'])->name('sm307.cell-details');
    Route::get('/sm307/export', [Sm307Controller::class, 'export'])->name('sm307.export');

    Route::get('/hora-medico', [HoraMedicoController::class, 'index'])->name('hora-medico');
    Route::get('/hora-medico/servicio-social', [HoraMedicoController::class, 'servicioSocial'])->name('hora-medico.servicio-social');
    Route::get('/hora-medico/imprimir', [HoraMedicoController::class, 'imprimir'])->name('hora-medico.imprimir');
    Route::post('/hora-medico/upload-logo', [HoraMedicoController::class, 'uploadLogo'])->name('hora-medico.upload-logo');
    Route::post('/hora-medico/save-setting', [HoraMedicoController::class, 'saveSetting'])->name('hora-medico.save-setting');
    Route::post('/hora-medico/save-director-mensual', [HoraMedicoController::class, 'saveDirectorMensual'])->name('hora-medico.save-director-mensual');
    Route::post('/hora-medico/save-observacion', [HoraMedicoController::class, 'saveObservacion'])->name('hora-medico.save-observacion');
    Route::get('/hora-medico/consolidado', [HoraMedicoController::class, 'consolidado'])->name('hora-medico.consolidado');
    Route::get('/hora-medico/consolidado/imprimir', [HoraMedicoController::class, 'imprimirConsolidado'])->name('hora-medico.consolidado.imprimir');
    Route::get('/hora-medico/consolidado/export', [HoraMedicoController::class, 'exportConsolidado'])->name('hora-medico.consolidado.export');
    Route::get('/hora-medico/hsc', [HoraMedicoController::class, 'getHSC'])->name('hora-medico.get-hsc');
    Route::post('/hora-medico/save-hsc', [HoraMedicoController::class, 'saveHSC'])->name('hora-medico.save-hsc');
    Route::get('/hora-medico/export-excel', [HoraMedicoController::class, 'exportExcel'])->name('hora-medico.export-excel');
    Route::post('/hora-medico/agregar-medico-hsc', [HoraMedicoController::class, 'agregarMedicoHSC'])->name('hora-medico.add-medico-hsc');
});

// Notificación SVS & Calendario Epi
Route::get('/notificacion-svs', [NotificacionSvsController::class, 'index'])->name('informes.notificacion_svs');
Route::post('/notificacion-svs/update-disease', [NotificacionSvsController::class, 'updateDisease'])->name('informes.notificacion_svs.update_disease');
Route::match(['GET', 'POST'], '/notificacion-svs/buscar-paciente', [NotificacionSvsController::class, 'buscarPaciente'])->name('informes.notificacion_svs.buscar_paciente');
Route::post('/notificacion-svs/save-form', [NotificacionSvsController::class, 'saveFullForm'])->name('informes.notificacion_svs.save_form');
Route::post('/notificacion-svs/toggle-notificado', [NotificacionSvsController::class, 'toggleNotificado'])->name('informes.notificacion_svs.toggle_notificado');
Route::post('/notificacion-svs/update-telefono', [NotificacionSvsController::class, 'updateTelefono'])->name('informes.notificacion_svs.update_telefono');

Route::get('/calendario-epi', [CalendarioEpiController::class, 'index'])->name('calendario_epi');
Route::post('/calendario-epi/upload', [CalendarioEpiController::class, 'upload'])->name('calendario_epi.upload');
Route::get('/calendario-epi/download', [CalendarioEpiController::class, 'download'])->name('calendario_epi.download');

// ============================================================================
// GESTIÓN OTRAS BASES & ADMINISTRACIÓN
// ============================================================================

// Consulta de Pacientes en SESAL
use App\Http\Controllers\PruebaConsultaController;
Route::get('/prueba-consulta', [PruebaConsultaController::class, 'index'])->name('prueba.consulta');
Route::match(['GET', 'POST'], '/prueba-consulta/buscar', [PruebaConsultaController::class, 'buscar'])->name('prueba.consulta.buscar');

// Pacientes BD

use App\Http\Controllers\PacienteController;
Route::prefix('pacientes')->name('pacientes.')->group(function () {
    Route::get('/', [PacienteController::class, 'index'])->name('index');
    Route::get('/buscar-modal', [PacienteController::class, 'buscarModal'])->name('buscar_modal');
    Route::patch('/{id}/update-field', [PacienteController::class, 'updateField'])->name('update_field');
    Route::post('/{id}/resync', [PacienteController::class, 'resync'])->name('resync');
    Route::post('/buscar-nuevo', [PacienteController::class, 'buscarYAgregar'])->name('buscar_nuevo');
    Route::post('/resync-masivo', [PacienteController::class, 'resyncMasivo'])->name('resync_masivo');
    Route::post('/recalcular-edades', [PacienteController::class, 'recalcularEdades'])->name('recalcular_edades');
    Route::post('/{id}/recalcular-edad', [PacienteController::class, 'recalcularEdadIndividual'])->name('recalcular_edad');
});

// Adolescentes
use App\Http\Controllers\AdolescenteController;
Route::prefix('adolescentes')->name('adolescentes.')->group(function () {
    Route::get('/', [AdolescenteController::class, 'index'])->name('index');
    Route::get('/create', [AdolescenteController::class, 'create'])->name('create');
    Route::post('/', [AdolescenteController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [AdolescenteController::class, 'edit'])->name('edit');
    Route::put('/{id}', [AdolescenteController::class, 'update'])->name('update');
    Route::patch('/{id}/ajax', [AdolescenteController::class, 'ajaxUpdate'])->name('ajax-update');
    Route::delete('/{id}', [AdolescenteController::class, 'destroy'])->name('destroy');
    Route::get('/buscar', [AdolescenteController::class, 'checkIdentity'])->name('buscar');
    Route::post('/guardar', [AdolescenteController::class, 'storeBatch'])->name('guardar');
    Route::get('/seguimientos', [AdolescenteController::class, 'seguimientos'])->name('seguimientos');
    Route::get('/depurados', [AdolescenteController::class, 'depurados'])->name('depurados');
    Route::get('/historial/{no_expediente}', [AdolescenteController::class, 'historial'])->name('historial');
    Route::get('/check-dni', [AdolescenteController::class, 'checkDni'])->name('check-dni');
    Route::get('/check-expediente', [AdolescenteController::class, 'checkExpediente'])->name('check-expediente');
    Route::get('/seguimiento/create/{no_expediente}', [AdolescenteController::class, 'seguimientoCreate'])->name('seguimiento.create');
    Route::post('/seguimiento/store', [AdolescenteController::class, 'seguimientoStore'])->name('seguimiento.store');
    Route::get('/seguimiento/{id}/edit', [AdolescenteController::class, 'seguimientoEdit'])->name('seguimiento.edit');
    Route::put('/seguimiento/{id}', [AdolescenteController::class, 'seguimientoUpdate'])->name('seguimiento.update');
    Route::delete('/seguimiento/{id}', [AdolescenteController::class, 'seguimientoDestroy'])->name('seguimiento.destroy');
    Route::get('/export-excel', [AdolescenteController::class, 'exportExcel'])->name('export-excel');
});

// Adulto Mayor
use App\Http\Controllers\AdultoMayorController;
Route::prefix('adulto-mayor')->name('adulto-mayor.')->group(function () {
    Route::get('/check-dni', [AdultoMayorController::class, 'checkDni'])->name('check-dni');
});
Route::resource('adulto-mayor', AdultoMayorController::class)
    ->parameters(['adulto-mayor' => 'adultoMayor']);

// Médicos
use App\Http\Controllers\MedicoController;
Route::prefix('medicos')->name('medicos.')->group(function () {
    Route::get('/buscar-codigo', [MedicoController::class, 'buscarPorCodigo'])->name('buscar-codigo');
    Route::get('/obtener-todos', [MedicoController::class, 'obtenerTodos'])->name('obtener-todos');
    Route::get('/siguiente-codigo', [MedicoController::class, 'obtenerSiguienteCodigo'])->name('siguiente-codigo');
    Route::get('/planilla', [MedicoController::class, 'planilla'])->name('planilla');
    Route::post('/{medico}/toggle-director', [MedicoController::class, 'toggleDirector'])->name('toggle-director');
});
Route::resource('medicos', MedicoController::class);

// Diagnósticos
use App\Http\Controllers\DiagnosticoController;
Route::prefix('diagnosticos')->name('diagnosticos.')->group(function () {
    Route::get('/', [DiagnosticoController::class, 'index'])->name('index');
    Route::post('/normalizar', [DiagnosticoController::class, 'normalizar'])->name('normalizar');
    Route::get('/buscar', [DiagnosticoController::class, 'buscar'])->name('buscar');
    Route::get('/salud-mental', [DiagnosticoController::class, 'obtenerSaludMental'])->name('salud-mental');
    Route::get('/condicionamientos', [DiagnosticoController::class, 'condicionamientos'])->name('condicionamientos');
    Route::post('/condicionamientos/batch', [DiagnosticoController::class, 'actualizarCondicionamientosBatch'])->name('condicionamientos.batch');
    Route::put('/{diagnostico}/condicionamiento', [DiagnosticoController::class, 'actualizarCondicionamiento'])->name('condicionamiento.update');
    Route::get('/validaciones-json', [DiagnosticoController::class, 'obtenerValidacionesJson'])->name('validaciones-json');
    Route::get('/create', [DiagnosticoController::class, 'create'])->name('create');
    Route::post('/', [DiagnosticoController::class, 'store'])->name('store');
    Route::get('/siguiente-codigo', [DiagnosticoController::class, 'obtenerSiguienteCodigo'])->name('siguiente-codigo');
    Route::get('/{diagnostico}', [DiagnosticoController::class, 'show'])->name('show');
    Route::get('/{diagnostico}/edit', [DiagnosticoController::class, 'edit'])->name('edit');
    Route::put('/{diagnostico}', [DiagnosticoController::class, 'update'])->name('update');
    Route::delete('/{diagnostico}', [DiagnosticoController::class, 'destroy'])->name('destroy');
});

// Colonias y Referencias
use App\Http\Controllers\ColoniaController;
use App\Http\Controllers\ReferenciaController;
Route::resource('colonias', ColoniaController::class);
Route::resource('referencias', ReferenciaController::class);

// Documentación
use App\Http\Controllers\DocumentacionController;
Route::prefix('documentacion')->name('documentacion.')->group(function () {
    Route::get('/', [DocumentacionController::class, 'index'])->name('index');
    Route::post('/upload', [DocumentacionController::class, 'store'])->name('upload');
    Route::post('/carpetas', [DocumentacionController::class, 'storeFolder'])->name('storeFolder');
    Route::put('/carpetas/{id}', [DocumentacionController::class, 'updateFolder'])->name('updateFolder');
    Route::delete('/carpetas/{id}', [DocumentacionController::class, 'destroyFolder'])->name('destroyFolder');
    Route::get('/ver/{id}', [DocumentacionController::class, 'view'])->name('view');
    Route::get('/descargar/{id}', [DocumentacionController::class, 'download'])->name('download');
    Route::post('/reemplazar/{id}', [DocumentacionController::class, 'replaceFile'])->name('replaceFile');
    Route::delete('/{id}', [DocumentacionController::class, 'destroy'])->name('destroy');
});



