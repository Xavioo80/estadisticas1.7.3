<x-app-layout>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-edit me-2"></i>Editar Registro de Adolescente
                    </h5>
                    <p class="mb-0 small text-light opacity-75">Expediente: {{ $adolescente->no_expediente }}</p>
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

                    <form action="{{ route('adolescentes.update', $adolescente->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 text-primary fw-bold">Información Personal</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Número de Expediente</label>
                                <input type="text" name="no_expediente" class="form-control" value="{{ old('no_expediente', $adolescente->no_expediente) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Nombre Completo</label>
                                <input type="text" name="nombre_completo" class="form-control" value="{{ old('nombre_completo', $adolescente->nombre_completo) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Sexo</label>
                                <select name="sexo" class="form-select" required>
                                    <option value="M" {{ old('sexo', $adolescente->sexo) == 'M' ? 'selected' : '' }}>Masculino (M)</option>
                                    <option value="F" {{ old('sexo', $adolescente->sexo) == 'F' ? 'selected' : '' }}>Femenino (F)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento', $adolescente->fecha_nacimiento) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Fecha de Ingreso</label>
                                <input type="date" name="fecha_ingreso" class="form-control" value="{{ old('fecha_ingreso', $adolescente->fecha_ingreso) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Edad</label>
                                <input type="number" name="edad" class="form-control" value="{{ old('edad', $adolescente->edad) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Número de Identidad</label>
                                <input type="text" name="numero_identidad" class="form-control" value="{{ old('numero_identidad', $adolescente->numero_identidad) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Nombre del Tutor</label>
                                <input type="text" name="nombre_tutor" class="form-control" value="{{ old('nombre_tutor', $adolescente->nombre_tutor) }}">
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="border-bottom pb-2 text-primary fw-bold">Ubicación y Perfil</h6>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small">Dirección Completa</label>
                                <textarea name="direccion_completa" class="form-control" rows="2">{{ old('direccion_completa', $adolescente->direccion_completa) }}</textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Teléfono</label>
                                <input type="text" name="numero_telefono" class="form-control" value="{{ old('numero_telefono', $adolescente->numero_telefono) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Estado Civil</label>
                                <select name="estado_civil" class="form-select">
                                    <option value="">Seleccione...</option>
                                    <option value="Soltero" {{ old('estado_civil', $adolescente->estado_civil) == 'Soltero' ? 'selected' : '' }}>Soltero</option>
                                    <option value="Unión Libre" {{ old('estado_civil', $adolescente->estado_civil) == 'Unión Libre' ? 'selected' : '' }}>Unión Libre</option>
                                    <option value="Casado" {{ old('estado_civil', $adolescente->estado_civil) == 'Casado' ? 'selected' : '' }}>Casado</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Escolaridad</label>
                                <input type="text" name="escolaridad" class="form-control" value="{{ old('escolaridad', $adolescente->escolaridad) }}" placeholder="Ej. Primaria Completa">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Ocupación</label>
                                <input type="text" name="ocupacion" class="form-control" value="{{ old('ocupacion', $adolescente->ocupacion) }}">
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                            <a href="{{ route('adolescentes.index') }}" class="btn btn-light px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }
    label {
        color: #495057;
    }
</style>
</x-app-layout>
