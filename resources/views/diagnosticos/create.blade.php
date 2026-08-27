@extends('layouts.app')

@section('title', 'Crear Diagnóstico - Estadísticas 1.7')

@php
    $ultimoCodigo = (int) (DB::select('SELECT MAX(CAST(codigo AS UNSIGNED)) as max_code FROM diagnosticos')[0]->max_code ?? 0);
    $siguienteCodigo = $ultimoCodigo + 1;
@endphp

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div class="d-flex align-items-center gap-3">
            <div class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: var(--radius-md); background: rgba(77, 124, 254, 0.12); color: var(--color-primary);">
                <i class="bi bi-plus-circle-fill" style="font-size: 1.25rem;"></i>
            </div>
            <div>
                <h2 class="mb-1">Crear Nuevo Diagnóstico CIE-10</h2>
                <p>Registrar un nuevo código y patología en el catálogo general</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('diagnosticos.index') }}" class="btn btn-subtle btn-sm" style="font-weight: 600;">
                <i class="bi bi-arrow-left mr-1"></i> Volver al Catálogo
            </a>
        </div>
    </div>

    <!-- Card de Sugerencia de Código -->
    <div class="alert alert-info d-flex align-items-center justify-content-between p-3 mb-3 rounded" style="background-color: rgba(77, 124, 254, 0.1); border: 1px solid rgba(77, 124, 254, 0.25); color: var(--text-primary);">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill text-primary" style="font-size: 1.1rem;"></i>
            <span>Último código registrado: <strong class="text-primary font-monospace">{{ $ultimoCodigo }}</strong> &nbsp;|&nbsp; Siguiente sugerido: <strong class="text-success font-monospace">{{ $siguienteCodigo }}</strong></span>
        </div>
    </div>

    <!-- Formulario -->
    <div class="card sing-card">
        <div class="card-body p-4">
            <form action="{{ route('diagnosticos.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label for="codigo" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Código <span class="text-danger">*</span></label>
                        <input type="text" name="codigo" id="codigo" class="form-control form-control-sm font-monospace font-weight-bold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('codigo', $siguienteCodigo) }}" maxlength="10" required>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="auxiliar" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Auxiliar</label>
                        <input type="text" name="auxiliar" id="auxiliar" class="form-control form-control-sm font-monospace"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('auxiliar') }}" placeholder="Opcional">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="patologia" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Patología / Descripción <span class="text-danger">*</span></label>
                        <input type="text" name="patologia" id="patologia" class="form-control form-control-sm text-uppercase"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('patologia') }}" placeholder="Ej: DIABETES MELLITUS TIPO 2" required>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="secundario" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Secundario</label>
                        <input type="text" name="secundario" id="secundario" class="form-control form-control-sm"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('secundario') }}" placeholder="Opcional">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="categoria" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Categoría <span class="text-danger">*</span></label>
                        <select name="categoria" id="categoria" class="form-control form-control-sm text-uppercase font-weight-semibold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;" required>
                            <option value="">Seleccione...</option>
                            <option value="AT2-R">AT2-R</option>
                            <option value="MORBILIDAD">MORBILIDAD</option>
                            <option value="SM03">SALUD MENTAL SM03</option>
                            <option value="SM07">SALUD MENTAL SM07</option>
                            <option value="SM2">SM2</option>
                            <option value="ITS">ITS</option>
                            <option value="IRAS">IRAS</option>
                            <option value="ARBOVIROSIS">ARBOVIROSIS</option>
                            <option value="OTRAS PATOLOGIAS">OTRAS PATOLOGIAS</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 pt-4 border-top" style="border-color: var(--border-color) !important;">
                    <a href="{{ route('diagnosticos.index') }}" class="btn btn-secondary btn-sm" style="font-weight: 600;">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 600;">
                        <i class="bi bi-save mr-1"></i> Guardar Diagnóstico
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection