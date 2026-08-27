@foreach($registros as $index => $reg)
<tr>
    <td class="text-center">{{ $registros->firstItem() + $index }}</td>
    <td contenteditable="true" class="editable" data-id="{{ $reg->id }}" data-field="expediente">{{ $reg->expediente }}</td>
    <td contenteditable="true" class="editable" data-id="{{ $reg->id }}" data-field="nombre_completo">{{ $reg->nombre_completo }}</td>
    <td contenteditable="true" class="editable" data-id="{{ $reg->id }}" data-field="dni">{{ $reg->dni }}</td>
    <td contenteditable="true" class="editable text-center" data-id="{{ $reg->id }}" data-field="edad">{{ $reg->edad }}</td>
    <td contenteditable="true" class="editable" data-id="{{ $reg->id }}" data-field="direccion">{{ $reg->direccion }}</td>
    <td contenteditable="true" class="editable" data-id="{{ $reg->id }}" data-field="telefono">{{ $reg->telefono }}</td>
    <td class="text-center">
        <div class="flex items-center justify-center gap-1">
            <button class="btn btn-sm btn-primary py-0 px-1.5 btn-editar" data-id="{{ $reg->id }}" title="Editar" style="font-size: 11px; height: 20px; line-height: 1;">
                <i class="fas fa-edit"></i>
            </button>
            <form action="{{ route('adulto-mayor.destroy', $reg) }}" method="POST" class="d-inline form-eliminar m-0">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger py-0 px-1.5" title="Eliminar" style="font-size: 11px; height: 20px; line-height: 1;">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@endforeach
