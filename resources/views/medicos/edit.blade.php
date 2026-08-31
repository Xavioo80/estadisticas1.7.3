@extends('layouts.app')

@section('title', 'Editar Médico - Estadísticas 1.7')

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div>
            <h2 style="margin: 0; font-size: 1.05rem; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; color: var(--text-primary);">
                <i class="bi bi-pencil-square text-primary" style="font-size: 1.25rem;"></i> Editar Médico: <span class="text-primary">{{ $medico->NOM_MED }}</span>
            </h2>
            <p style="margin: 2px 0 0 0; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Cód. Facultativo: <span class="font-monospace font-weight-bold text-primary">{{ $medico->COD_MED }}</span></p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('medicos.index') }}" class="btn btn-subtle btn-sm" style="font-weight: 600;">
                <i class="bi bi-arrow-left mr-1"></i> Volver al Directorio
            </a>
        </div>
    </div>

    <!-- Formulario -->
    <div class="card sing-card">
        <div class="card-body p-4">
            <form action="{{ route('medicos.update', $medico) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Fila 1: Código y Nombre -->
                <div class="row mb-3">
                    <div class="col-md-3 mb-3">
                        <label for="COD_MED" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Código Médico <span class="text-danger">*</span></label>
                        <input type="text" name="COD_MED" id="COD_MED" class="form-control form-control-sm font-monospace font-weight-bold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('COD_MED', $medico->COD_MED) }}" required maxlength="10">
                    </div>
                    <div class="col-md-9 mb-3">
                        <label for="NOM_MED" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" name="NOM_MED" id="NOM_MED" class="form-control form-control-sm text-uppercase font-weight-semibold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('NOM_MED', $medico->NOM_MED) }}" required maxlength="100">
                    </div>
                </div>

                <!-- Fila 2: Datos Laborales -->
                <div class="row mb-3">
                    <div class="col-md-3 mb-3">
                        <label for="ESPECIALIDAD" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Especialidad <span class="text-danger">*</span></label>
                        <select name="ESPECIALIDAD" id="ESPECIALIDAD" class="form-control form-control-sm text-uppercase font-weight-semibold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;" required>
                            @foreach(['MEDICO GENERAL','GINECOLOGIA','LICENCIADAS EN ENFERMERIA','ENFERMERAS AUXILIARES','PEDIATRA','CONSEJERIA','PSICOLOGIA','PSIQUIATRA','TRABAJADOR SOCIAL','ABOGADO','OTROS'] as $esp)
                                <option value="{{$esp}}" {{ $medico->ESPECIALIDAD == $esp ? 'selected' : '' }}>{{$esp}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="NOMINA" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Nómina <span class="text-danger">*</span></label>
                        <select name="NOMINA" id="NOMINA" class="form-control form-control-sm text-uppercase font-weight-semibold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;" required>
                            @foreach(['MEDICO ASISTENCIAL','ESPECIALISTA','LICENCIADA EN ENFERMERIA','ENFERMERA AUXILIAR','TRABAJADOR SOCIAL','ABOGADO','ONG','OTROS'] as $nom)
                                <option value="{{$nom}}" {{ $medico->NOMINA == $nom ? 'selected' : '' }}>{{$nom}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="JORNADA" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Jornada <span class="text-danger">*</span></label>
                        <select name="JORNADA" id="JORNADA" class="form-control form-control-sm text-uppercase font-weight-semibold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;" required>
                            @foreach(['MATUTINA','VESPERTINA','NOCTURNA','JORNADA COMPLETA','FIN DE SEMANA'] as $jor)
                                <option value="{{$jor}}" {{ $medico->JORNADA == $jor ? 'selected' : '' }}>{{$jor}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="MODALIDAD" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Modalidad <span class="text-danger">*</span></label>
                        <select name="MODALIDAD" id="MODALIDAD" class="form-control form-control-sm text-uppercase font-weight-semibold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;" required>
                            @foreach(['PERMANENTE','CONTRATO','SERVICIO SOCIAL','TEMPORAL','INTERINATO','ONG'] as $mod)
                                <option value="{{$mod}}" {{ strtoupper($medico->MODALIDAD ?? '') == $mod ? 'selected' : '' }}>{{$mod}}</option>
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
                            value="{{ old('FECHA_INGRESO', $medico->FECHA_INGRESO ? $medico->FECHA_INGRESO->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="HORAS_CONTRATADAS" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Horas Contratadas</label>
                        <input type="number" name="HORAS_CONTRATADAS" id="HORAS_CONTRATADAS" class="form-control form-control-sm"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            min="0" step="0.5" value="{{ old('HORAS_CONTRATADAS', $medico->HORAS_CONTRATADAS ?? 6) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="TELEFONO" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Teléfono</label>
                        <input type="text" name="TELEFONO" id="TELEFONO" class="form-control form-control-sm font-monospace"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('TELEFONO', $medico->TELEFONO) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="CORREO" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Correo Electrónico</label>
                        <input type="email" name="CORREO" id="CORREO" class="form-control form-control-sm"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('CORREO', $medico->CORREO) }}">
                    </div>
                </div>

                <!-- Observaciones, Estado y Director -->
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label for="observaciones" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" rows="2" class="form-control form-control-sm"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">{{ old('observaciones', $medico->observaciones) }}</textarea>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="estado" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Estado</label>
                        <select name="estado" id="estado" class="form-control form-control-sm text-uppercase font-weight-bold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;">
                            <option value="activo" {{ $medico->estado == 'activo' ? 'selected' : '' }}>ACTIVO</option>
                            <option value="inactivo" {{ $medico->estado == 'inactivo' ? 'selected' : '' }}>INACTIVO</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 d-flex align-items-center">
                        <div class="p-3 rounded w-100" style="background-color: var(--bg-subtle); border: 1px solid var(--border-color);">
                            <div class="form-check">
                                <input type="checkbox" id="es_director" name="es_director" value="1" class="form-check-input" {{ $medico->es_director ? 'checked' : '' }}>
                                <label class="form-check-label font-weight-bold" for="es_director" style="color: var(--text-primary); font-size: 0.82rem;">
                                    Director / Firma Principal
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
                        <i class="bi bi-save mr-1"></i> Actualizar Médico
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
