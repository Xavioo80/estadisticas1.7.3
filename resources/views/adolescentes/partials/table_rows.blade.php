@foreach($registros as $index => $ado)
    <tr style="border-bottom: 1px solid var(--border-color);">
        <td class="text-center font-monospace" style="color: var(--text-muted); border-right: 1px solid var(--border-color);">{{ $registros->firstItem() + $index }}</td>
        <td class="font-weight-bold font-monospace editable" data-id="{{ $ado->id }}" data-field="no_expediente" contenteditable="true" style="color: var(--color-primary); border-right: 1px solid var(--border-color);">{{ $ado->no_expediente }}</td>
        <td class="editable font-weight-semibold text-uppercase" data-id="{{ $ado->id }}" data-field="nombre_completo" contenteditable="true" style="color: var(--text-primary); border-right: 1px solid var(--border-color);">{{ $ado->nombre_completo }}</td>
        <td class="text-center p-0" style="border-right: 1px solid var(--border-color);">
            <select class="ata-select select-editable" data-id="{{ $ado->id }}" data-field="sexo">
                <option value="M" {{ $ado->sexo == 'M' ? 'selected' : '' }}>M</option>
                <option value="F" {{ $ado->sexo == 'F' ? 'selected' : '' }}>F</option>
            </select>
        </td>
        <td class="text-center p-0" style="border-right: 1px solid var(--border-color);">
            <input type="date" class="ata-input date-editable" data-id="{{ $ado->id }}" data-field="fecha_nacimiento" value="{{ $ado->fecha_nacimiento ? $ado->fecha_nacimiento->format('Y-m-d') : '' }}">
        </td>
        <td class="text-center p-0 font-weight-bold" style="border-right: 1px solid var(--border-color);">
            <input type="date" class="ata-input date-editable text-success font-weight-bold" data-id="{{ $ado->id }}" data-field="fecha_ingreso" value="{{ $ado->fecha_ingreso ? $ado->fecha_ingreso->format('Y-m-d') : '' }}">
        </td>
        <td class="text-center editable font-weight-bold" data-id="{{ $ado->id }}" data-field="edad" contenteditable="true" style="color: var(--text-primary); border-right: 1px solid var(--border-color);">{{ $ado->edad }}</td>
        <td class="text-center editable font-monospace" data-id="{{ $ado->id }}" data-field="numero_identidad" contenteditable="true" style="color: var(--text-secondary); border-right: 1px solid var(--border-color);">{{ $ado->numero_identidad }}</td>
        <td class="p-0" style="border-right: 1px solid var(--border-color);">
            <select class="ata-select select-editable text-center" data-id="{{ $ado->id }}" data-field="colonia">
                <option value="">-</option>
                @foreach($colonias as $col)
                    <option value="{{ $col->COLONIA }}" {{ $ado->colonia == $col->COLONIA ? 'selected' : '' }}>{{ $col->COLONIA }}</option>
                @endforeach
            </select>
        </td>
        <td class="text-center editable font-monospace" data-id="{{ $ado->id }}" data-field="numero_telefono" contenteditable="true" style="color: var(--text-secondary); border-right: 1px solid var(--border-color);">{{ $ado->numero_telefono }}</td>
        <td class="p-0" style="border-right: 1px solid var(--border-color);">
            <select class="ata-select select-editable text-center" data-id="{{ $ado->id }}" data-field="estado_civil">
                <option value="">-</option>
                <option value="Soltero" {{ $ado->estado_civil == 'Soltero' ? 'selected' : '' }}>SOLTERO</option>
                <option value="Unión Libre" {{ $ado->estado_civil == 'Unión Libre' ? 'selected' : '' }}>U. LIBRE</option>
                <option value="Casado" {{ $ado->estado_civil == 'Casado' ? 'selected' : '' }}>CASADO</option>
            </select>
        </td>
        <td class="p-0" style="border-right: 1px solid var(--border-color);">
            <select class="ata-select select-editable text-center" data-id="{{ $ado->id }}" data-field="escolaridad">
                <option value="">-</option>
                <option value="PRIMARIA" {{ $ado->escolaridad == 'PRIMARIA' ? 'selected' : '' }}>PRIMARIA</option>
                <option value="SECUNDARIA" {{ $ado->escolaridad == 'SECUNDARIA' ? 'selected' : '' }}>SECUNDARIA</option>
                <option value="UNIVERSITARIO" {{ $ado->escolaridad == 'UNIVERSITARIO' ? 'selected' : '' }}>UNIVERSITARIO</option>
                <option value="NINGUNA" {{ $ado->escolaridad == 'NINGUNA' ? 'selected' : '' }}>NINGUNA</option>
            </select>
        </td>
        <td class="text-center editable" data-id="{{ $ado->id }}" data-field="anios_cursados" contenteditable="true" style="color: var(--text-primary); border-right: 1px solid var(--border-color);">{{ $ado->anios_cursados }}</td>
        <td class="p-0" style="border-right: 1px solid var(--border-color);">
            <select class="ata-select select-editable text-center" data-id="{{ $ado->id }}" data-field="ocupacion">
                <option value="">-</option>
                <option value="TRABAJA" {{ $ado->ocupacion == 'TRABAJA' ? 'selected' : '' }}>TRABAJA</option>
                <option value="ESTUDIA" {{ $ado->ocupacion == 'ESTUDIA' ? 'selected' : '' }}>ESTUDIA</option>
                <option value="TRABAJA Y ESTUDIA" {{ $ado->ocupacion == 'TRABAJA Y ESTUDIA' ? 'selected' : '' }}>T/E</option>
                <option value="NINGUNA" {{ $ado->ocupacion == 'NINGUNA' ? 'selected' : '' }}>NINGUNA</option>
            </select>
        </td>
        <td class="text-center" style="border-right: 1px solid var(--border-color);">
            @php
                $hoy = \Carbon\Carbon::now();
                $cumple = $ado->fecha_nacimiento ? \Carbon\Carbon::parse($ado->fecha_nacimiento) : null;
                $edadActual = $cumple ? $cumple->diffInYears($hoy) : 0;
                $enRango = ($edadActual >= 10 && $edadActual <= 19);
            @endphp
            <span class="badge rounded-circle" 
                  style="background-color: {{ $enRango ? '#22c55e' : '#ef4444' }}; width: 10px; height: 10px; display: inline-block; box-shadow: 0 0 5px {{ $enRango ? '#22c55e88' : '#ef444488' }};"
                  title="Edad Actual: {{ $edadActual }} años {{ $enRango ? '(En Rango 10-19)' : '(Fuera de Rango)' }}">
            </span>
        </td>

        <td class="text-center py-1 px-1">
            <div class="d-flex justify-content-center align-items-center gap-1">
                <a href="{{ route('adolescentes.historial', $ado->no_expediente) }}"
                    class="btn btn-subtle-primary btn-sm px-1 py-0 font-weight-bold"
                    style="font-size: 0.72rem; height: 24px; line-height: 22px;"
                    title="Ver Historial de Citas">
                    HISTORIAL
                </a>

                <button type="button" 
                    class="btn btn-icon btn-sm btn-subtle-warning btn-editar"
                    data-id="{{ $ado->id }}"
                    style="width: 24px; height: 24px; border-radius: var(--radius-sm);"
                    title="Editar">
                    <i class="bi bi-pencil" style="font-size: 0.75rem;"></i>
                </button>

                <form action="{{ route('adolescentes.destroy', $ado->id) }}" method="POST" class="m-0 form-eliminar d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-icon btn-sm btn-subtle-danger"
                        style="width: 24px; height: 24px; border-radius: var(--radius-sm);"
                        title="Eliminar">
                        <i class="bi bi-trash" style="font-size: 0.75rem;"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
@endforeach
