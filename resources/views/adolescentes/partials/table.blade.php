<div class="ata-card" id="ata-card-container">
    <div class="ata-table-wrapper">
        <table class="table-ata">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 100px;">EXPEDIENTE</th>
                    <th>NOMBRE COMPLETO</th>
                    <th style="width: 50px;">SEXO</th>
                    <th style="width: 100px;">F. NAC.</th>
                    <th style="width: 100px;">F. INGRESO</th>
                    <th style="width: 50px;">EDAD</th>
                    <th style="width: 120px;">IDENTIDAD</th>
                    <th>TUTOR</th>
                    <th>DIRECCIÓN</th>
                    <th style="width: 100px;">TELÉFONO</th>
                    <th style="width: 100px;">ESTADO CIVIL</th>
                    <th>ESCOLARIDAD</th>
                    <th>OCUPACIÓN</th>
                    <th style="width: 120px;">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $index => $ado)
                    <tr>
                        <td class="text-center text-muted">{{ $registros->firstItem() + $index }}</td>
                        <td class="text-primary font-weight-bold editable" data-id="{{ $ado->id }}" data-field="no_expediente" contenteditable="true">{{ $ado->no_expediente }}</td>
                        <td class="editable" data-id="{{ $ado->id }}" data-field="nombre_completo" contenteditable="true">{{ $ado->nombre_completo }}</td>
                        <td class="text-center p-0">
                            <select class="ata-select select-editable" data-id="{{ $ado->id }}" data-field="sexo">
                                <option value="M" {{ $ado->sexo == 'M' ? 'selected' : '' }}>M</option>
                                <option value="F" {{ $ado->sexo == 'F' ? 'selected' : '' }}>F</option>
                            </select>
                        </td>
                        <td class="text-center p-0">
                            <input type="date" class="ata-input date-editable" data-id="{{ $ado->id }}" data-field="fecha_nacimiento" value="{{ $ado->fecha_nacimiento }}">
                        </td>
                        <td class="text-center p-0 text-success font-weight-bold">
                            <input type="date" class="ata-input date-editable text-success font-weight-bold" data-id="{{ $ado->id }}" data-field="fecha_ingreso" value="{{ $ado->fecha_ingreso }}">
                        </td>
                        <td class="text-center editable" data-id="{{ $ado->id }}" data-field="edad" contenteditable="true">{{ $ado->edad }}</td>
                        <td class="editable" data-id="{{ $ado->id }}" data-field="numero_identidad" contenteditable="true">{{ $ado->numero_identidad }}</td>
                        <td class="editable" data-id="{{ $ado->id }}" data-field="nombre_tutor" contenteditable="true">{{ $ado->nombre_tutor }}</td>
                        <td class="editable" data-id="{{ $ado->id }}" data-field="direccion_completa" contenteditable="true">{{ $ado->direccion_completa }}</td>
                        <td class="editable" data-id="{{ $ado->id }}" data-field="numero_telefono" contenteditable="true">{{ $ado->numero_telefono }}</td>
                        <td class="p-0">
                            <select class="ata-select select-editable text-center" data-id="{{ $ado->id }}" data-field="estado_civil">
                                <option value="">-</option>
                                <option value="Soltero" {{ $ado->estado_civil == 'Soltero' ? 'selected' : '' }}>SOLTERO</option>
                                <option value="Unión Libre" {{ $ado->estado_civil == 'Unión Libre' ? 'selected' : '' }}>U. LIBRE</option>
                                <option value="Casado" {{ $ado->estado_civil == 'Casado' ? 'selected' : '' }}>CASADO</option>
                            </select>
                        </td>
                        <td class="editable" data-id="{{ $ado->id }}" data-field="escolaridad" contenteditable="true">{{ $ado->escolaridad }}</td>
                        <td class="editable" data-id="{{ $ado->id }}" data-field="ocupacion" contenteditable="true">{{ $ado->ocupacion }}</td>

                        <td class="text-center">
                            <div class="d-flex justify-content-center align-items-center h-100 gap-1 px-1">
                                <a href="{{ route('adolescentes.historial', $ado->no_expediente) }}"
                                    class="btn btn-primary btn-sm p-0 d-flex align-items-center justify-content-center"
                                    style="width: 55px; height: 18px; font-size: 8.5px; font-weight: bold; border-radius: 2px;"
                                    title="Ver Historial">
                                    HISTORIAL
                                </a>

                                <button type="button" 
                                    class="btn btn-info btn-sm p-0 d-flex align-items-center justify-content-center btn-editar"
                                    data-id="{{ $ado->id }}"
                                    style="width: 20px; height: 18px; border-radius: 2px; border: none;"
                                    title="Editar">
                                    <i class="fas fa-edit text-white" style="font-size: 9px;"></i>
                                </button>

                                <form action="{{ route('adolescentes.destroy', $ado->id) }}" method="POST" onsubmit="return confirm('¿Eliminar?');" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm p-0 d-flex align-items-center justify-content-center"
                                        style="width: 20px; height: 18px; border-radius: 2px;"
                                        title="Eliminar">
                                        <i class="fas fa-trash-alt" style="font-size: 9px;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="text-center py-4 text-muted">Sin registros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="ata-footer">
        <div class="d-flex align-items-center">
            <span>REGISTROS: <span class="badge-ata text-primary">{{ $registros->total() }}</span></span>
            <span class="mx-3">|</span>
            <span>PÁGINA: <span class="badge-ata">{{ $registros->currentPage() }}</span> DE {{ $registros->lastPage() }}</span>
        </div>
        <div class="pagination-ata shadow-none border-0">
            {{ $registros->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
