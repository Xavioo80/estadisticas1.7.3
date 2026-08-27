<x-app-layout>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-plus-circle me-2"></i>Registrar Nuevo Seguimiento / Consulta
                    </h5>
                    <p class="mb-0 small opacity-75">Paciente: {{ $adolescente->nombre_completo }} (EXP: {{ $adolescente->no_expediente }})</p>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger py-2 px-3 small">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('adolescentes.seguimiento.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="no_expediente" value="{{ $adolescente->no_expediente }}">
                        <input type="hidden" name="nombre_completo" value="{{ $adolescente->nombre_completo }}">
                        <input type="hidden" name="sexo" value="{{ $adolescente->sexo }}">
                        <input type="hidden" name="fecha_nacimiento" value="{{ $adolescente->fecha_nacimiento }}">
                        <input type="hidden" name="numero_identidad" value="{{ $adolescente->numero_identidad }}">
                        <input type="hidden" name="nombre_tutor" value="{{ $adolescente->nombre_tutor }}">
                        <input type="hidden" name="direccion_completa" value="{{ $adolescente->direccion_completa }}">
                        <input type="hidden" name="numero_telefono" value="{{ $adolescente->numero_telefono }}">
                        <input type="hidden" name="estado_civil" value="{{ $adolescente->estado_civil }}">
                        <input type="hidden" name="escolaridad" value="{{ $adolescente->escolaridad }}">
                        <input type="hidden" name="ocupacion" value="{{ $adolescente->ocupacion }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Fecha de Consulta</label>
                                <input type="date" name="fecha_consulta" class="form-control" value="{{ old('fecha_consulta', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Edad (al momento de la consulta)</label>
                                <input type="number" name="edad" class="form-control" value="{{ old('edad', $adolescente->edad) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">Diagnóstico / Motivo de Consulta / Seguimiento</label>
                                <textarea name="diagnostico_seguimiento" class="form-control" rows="4" placeholder="Describa el motivo o diagnóstico de esta atención..." required>{{ old('diagnostico_seguimiento') }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                            <a href="{{ route('adolescentes.historial', $adolescente->no_expediente) }}" class="btn btn-light px-4">Cancelar</a>
                            <button type="submit" class="btn btn-success px-4 shadow-sm fw-bold">
                                <i class="fas fa-save me-2"></i>Guardar Seguimiento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
