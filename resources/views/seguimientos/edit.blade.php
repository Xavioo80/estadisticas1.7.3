<x-app-layout>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-info text-dark py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-edit me-2"></i>Editar Seguimiento / Consulta
                    </h5>
                    @if($adolescente)
                        <p class="mb-0 small opacity-75">Paciente: {{ $adolescente->nombre_completo }} (EXP: {{ $adolescente->no_expediente }})</p>
                    @endif
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

                    <form action="{{ route('adolescentes.seguimiento.update', $seguimiento->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Fecha de Consulta</label>
                                <input type="date" name="fecha_consulta" class="form-control" value="{{ old('fecha_consulta', $seguimiento->fecha_consulta) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Edad (al momento de la consulta)</label>
                                <input type="number" name="edad" class="form-control" value="{{ old('edad', $seguimiento->edad) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">Diagnóstico / Motivo de Consulta / Seguimiento</label>
                                <textarea name="diagnostico_seguimiento" class="form-control" rows="4" required>{{ old('diagnostico_seguimiento', $seguimiento->diagnostico_seguimiento) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                            <a href="{{ route('adolescentes.historial', $seguimiento->no_expediente) }}" class="btn btn-light px-4">Cancelar</a>
                            <button type="submit" class="btn btn-info px-4 shadow-sm fw-bold">
                                <i class="fas fa-save me-2"></i>Actualizar Seguimiento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
