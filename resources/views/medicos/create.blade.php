@extends('layouts.app')

@section('title', 'Registrar Nuevo Médico - Estadísticas 1.7')

@php
    $ultimoCodigo = (int) (DB::select('SELECT MAX(CAST(COD_MED AS UNSIGNED)) as max_code FROM medicos')[0]->max_code ?? 0);
    $siguienteCodigo = $ultimoCodigo + 1;
@endphp

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div>
            <h2 style="margin: 0; font-size: 1.05rem; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; color: var(--text-primary);">
                <i class="bi bi-person-plus-fill text-primary" style="font-size: 1.25rem;"></i> Registrar Nuevo Personal Médico
            </h2>
            <p style="margin: 2px 0 0 0; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Alta de facultativo o especialista para asignación de turnos y consultas</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('medicos.index') }}" class="btn btn-subtle btn-sm" style="font-weight: 600;">
                <i class="bi bi-arrow-left mr-1"></i> Volver al Directorio
            </a>
        </div>
    </div>

    <!-- Alert de Sugerencia -->
    <div class="alert alert-info d-flex align-items-center justify-content-between p-3 mb-3 rounded" style="background-color: rgba(77, 124, 254, 0.1); border: 1px solid rgba(77, 124, 254, 0.25); color: var(--text-primary);">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill text-primary" style="font-size: 1.1rem;"></i>
            <span>Último código registrado: <strong class="text-primary font-monospace">{{ $ultimoCodigo }}</strong> &nbsp;|&nbsp; Siguiente código sugerido: <strong class="text-success font-monospace">{{ $siguienteCodigo }}</strong></span>
        </div>
    </div>

    <!-- Card de Formulario -->
    <div class="card sing-card">
        <div class="card-body p-4">
            <form action="{{ route('medicos.store') }}" method="POST">
                @csrf

                <!-- Fila 1: Código y Nombre -->
                <div class="row mb-3">
                    <div class="col-md-3 mb-3">
                        <label for="COD_MED" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Código Médico <span class="text-danger">*</span></label>
                        <input type="text" name="COD_MED" id="COD_MED" class="form-control form-control-sm font-monospace font-weight-bold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('COD_MED', $siguienteCodigo) }}" required maxlength="10">
                    </div>
                    <div class="col-md-9 mb-3">
                        <label for="NOM_MED" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" name="NOM_MED" id="NOM_MED" class="form-control form-control-sm text-uppercase font-weight-semibold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('NOM_MED') }}" placeholder="Ej: DRA. MARIA LOPEZ" required maxlength="100">
                    </div>
                </div>

                <!-- Fila 2: Datos Laborales -->
                <div class="row mb-3">
                    <div class="col-md-3 mb-3">
                        <label for="ESPECIALIDAD" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Especialidad <span class="text-danger">*</span></label>
                        <select name="ESPECIALIDAD" id="ESPECIALIDAD" class="form-control form-control-sm text-uppercase font-weight-semibold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;" required>
                            @foreach(['MEDICO GENERAL','GINECOLOGIA','LICENCIADAS EN ENFERMERIA','ENFERMERAS AUXILIARES','PEDIATRA','CONSEJERIA','PSICOLOGIA','PSIQUIATRA','TRABAJADOR SOCIAL','ABOGADO','OTROS'] as $esp)
                                <option value="{{$esp}}">{{$esp}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="NOMINA" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Nómina <span class="text-danger">*</span></label>
                        <select name="NOMINA" id="NOMINA" class="form-control form-control-sm text-uppercase font-weight-semibold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;" required>
                            @foreach(['MEDICO ASISTENCIAL','ESPECIALISTA','LICENCIADA EN ENFERMERIA','ENFERMERA AUXILIAR','TRABAJADOR SOCIAL','ABOGADO','ONG','OTROS'] as $nom)
                                <option value="{{$nom}}">{{$nom}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="JORNADA" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Jornada <span class="text-danger">*</span></label>
                        <select name="JORNADA" id="JORNADA" class="form-control form-control-sm text-uppercase font-weight-semibold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;" required>
                            @foreach(['MATUTINA','VESPERTINA','NOCTURNA','JORNADA COMPLETA','FIN DE SEMANA'] as $jor)
                                <option value="{{$jor}}">{{$jor}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="MODALIDAD" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Modalidad <span class="text-danger">*</span></label>
                        <select name="MODALIDAD" id="MODALIDAD" class="form-control form-control-sm text-uppercase font-weight-semibold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;" required>
                            @foreach(['PERMANENTE','CONTRATO','SERVICIO SOCIAL','TEMPORAL','INTERINATO','ONG'] as $mod)
                                <option value="{{$mod}}">{{$mod}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Fila 3: Horas y Contacto -->
                <div class="row mb-3">
                    <div class="col-md-3 mb-3">
                        <label for="FECHA_INGRESO" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Fecha Ingreso</label>
                        <input type="date" name="FECHA_INGRESO" id="FECHA_INGRESO" class="form-control form-control-sm"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('FECHA_INGRESO', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="HORAS_CONTRATADAS" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Horas Contratadas</label>
                        <input type="number" name="HORAS_CONTRATADAS" id="HORAS_CONTRATADAS" class="form-control form-control-sm"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            min="0" step="0.5" value="{{ old('HORAS_CONTRATADAS', 6) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="TELEFONO" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Teléfono</label>
                        <input type="text" name="TELEFONO" id="TELEFONO" class="form-control form-control-sm font-monospace"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('TELEFONO') }}" placeholder="9999-9999">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="CORREO" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Correo Electrónico</label>
                        <input type="email" name="CORREO" id="CORREO" class="form-control form-control-sm"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('CORREO') }}" placeholder="doctor@salud.gob.hn">
                    </div>
                </div>

                <!-- Observaciones y Director -->
                <div class="row mb-3">
                    <div class="col-md-8 mb-3">
                        <label for="observaciones" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" rows="2" class="form-control form-control-sm"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);"></textarea>
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-center">
                        <div class="p-3 rounded w-100" style="background-color: var(--bg-subtle); border: 1px solid var(--border-color);">
                            <div class="form-check">
                                <input type="checkbox" id="es_director" name="es_director" value="1" class="form-check-input">
                                <label class="form-check-label font-weight-bold" for="es_director" style="color: var(--text-primary); font-size: 0.82rem;">
                                    Asignar como Director / Firma Principal
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 pt-4 border-top" style="border-color: var(--border-color) !important;">
                    <a href="{{ route('medicos.index') }}" class="btn btn-secondary btn-sm" style="font-weight: 600;">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 600;">
                        <i class="bi bi-save mr-1"></i> Guardar Médico
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
