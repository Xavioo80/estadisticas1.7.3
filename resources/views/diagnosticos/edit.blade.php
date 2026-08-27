@extends('layouts.app')

@section('title', 'Editar Diagnóstico CIE-10 - Estadísticas 1.7')

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div class="d-flex align-items-center gap-3">
            <div class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: var(--radius-md); background: rgba(77, 124, 254, 0.12); color: var(--color-primary);">
                <i class="bi bi-pencil-square" style="font-size: 1.25rem;"></i>
            </div>
            <div>
                <h2 class="mb-1">Editar Diagnóstico: <span class="text-primary font-monospace">{{ $diagnostico->codigo }}</span></h2>
                <p>Modificar la definición y categorización de este diagnóstico CIE-10</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('diagnosticos.index') }}" class="btn btn-subtle btn-sm" style="font-weight: 600;">
                <i class="bi bi-arrow-left mr-1"></i> Volver al Catálogo
            </a>
        </div>
    </div>

    <!-- Formulario -->
    <div class="card sing-card">
        <div class="card-body p-4">
            <form action="{{ route('diagnosticos.update', $diagnostico) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label for="codigo" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Código <span class="text-danger">*</span></label>
                        <input type="text" name="codigo" id="codigo" class="form-control form-control-sm font-monospace font-weight-bold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('codigo', $diagnostico->codigo) }}" maxlength="10" required>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="auxiliar" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Auxiliar</label>
                        <input type="text" name="auxiliar" id="auxiliar" class="form-control form-control-sm font-monospace"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('auxiliar', $diagnostico->auxiliar) }}" placeholder="Opcional">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="patologia" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Patología / Descripción <span class="text-danger">*</span></label>
                        <input type="text" name="patologia" id="patologia" class="form-control form-control-sm text-uppercase"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('patologia', $diagnostico->patologia) }}" required>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="secundario" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Secundario</label>
                        <input type="text" name="secundario" id="secundario" class="form-control form-control-sm"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;"
                            value="{{ old('secundario', $diagnostico->secundario) }}" placeholder="Opcional">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="categoria" class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.85rem;">Categoría <span class="text-danger">*</span></label>
                        <select name="categoria" id="categoria" class="form-control form-control-sm text-uppercase font-weight-semibold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 36px;" required>
                            <option value="" {{ $diagnostico->categoria == '' ? 'selected' : '' }}>Seleccione...</option>
                            <option value="AT2-R" {{ $diagnostico->categoria == 'AT2-R' ? 'selected' : '' }}>AT2-R</option>
                            <option value="MORBILIDAD" {{ $diagnostico->categoria == 'MORBILIDAD' ? 'selected' : '' }}>MORBILIDAD</option>
                            <option value="SM03" {{ $diagnostico->categoria == 'SM03' ? 'selected' : '' }}>SALUD MENTAL SM03</option>
                            <option value="SM07" {{ $diagnostico->categoria == 'SM07' ? 'selected' : '' }}>SALUD MENTAL SM07</option>
                            <option value="SM2" {{ $diagnostico->categoria == 'SM2' ? 'selected' : '' }}>SM2</option>
                            <option value="ITS" {{ $diagnostico->categoria == 'ITS' ? 'selected' : '' }}>ITS</option>
                            <option value="IRAS" {{ $diagnostico->categoria == 'IRAS' ? 'selected' : '' }}>IRAS</option>
                            <option value="ARBOVIROSIS" {{ $diagnostico->categoria == 'ARBOVIROSIS' ? 'selected' : '' }}>ARBOVIROSIS</option>
                            <option value="OTRAS PATOLOGIAS" {{ $diagnostico->categoria == 'OTRAS PATOLOGIAS' ? 'selected' : '' }}>OTRAS PATOLOGIAS</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 pt-4 border-top" style="border-color: var(--border-color) !important;">
                    <a href="{{ route('diagnosticos.index') }}" class="btn btn-secondary btn-sm" style="font-weight: 600;">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 600;">
                        <i class="bi bi-save mr-1"></i> Actualizar Diagnóstico
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
